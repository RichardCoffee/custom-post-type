<?php

/*
 *  abstract class for WordPress custom post types
 *
 *  GitHub:  https://github.com/RichardCoffee/custom-post-type
 *
 *  Copyright 2009-2016, Richard Coffee
 *
 */

abstract class RC_Custom_Post_Type {

  protected $type     = 'post';  #  'custom_post_type_name'
  protected $label    = 'Post';  #  _x('Custom Post Type','singular form','textdomain')
  protected $plural   = 'Posts'; #  _x('Custom Post Types','plural form','textdomain')
  protected $descrip  = '';      #  __('Custom Post Type Title','textdomain')

  ######  I have marked properties with '**' that I believe people may want to change more often.

  protected $comments    =  false;      # ** boolean:  allow comments
  protected $main_blog   =  true;       # ** set to false to not include the cpt in WP post queries
  protected $user_col    =  false;      # ** set to true to add a count column for this CPT to the admin users screen

  protected $debug       =  false;      #    used in conjunction with $this->logging
  protected $logging     = 'log_entry'; #    assign your own logging function here

  protected $caps        = 'post';      #    default is to not create custom capabilities
  protected $role_caps   = 'normal';    #    value of 'admin' will cause only the administrator caps to be updated - FIXME: allow array of roles

  protected $columns     =  null;       #    array('remove'=>array()','add'=>array())

  protected $has_archive =  false;      #    boolean or string - can be set to the archive template path
  protected $templates   =  false;      #    example: array( 'single' => WP_PLUGIN_DIR.'/plugin_dir/templates/single-{cpt-slug}.php' )

  protected $menu_icon   = 'dashicons-admin-post'; # ** admin dashboard icon
  protected $menu_position = 6;         # ** position on admin dashboard

  protected $rewrite     = array();     #    defaults to: array('slug'=>$this->type));
  protected $supports    = array('title','editor','author','thumbnail','revisions','comments');

  protected $taxonomies  = array('post_tag','category'); # ** passed to register_post_type() FIXME: possible auto call of $this->taxonomy_registration()
  protected $js_path     = false;       #    Set this in child if needed
  protected $slug_edit   = true;        # ** whether to allow editing of taxonomy slugs in admin screen
  protected $tax_list    = array();
  protected $tax_keep    = array();     #    example: array( 'taxonomy-slug' => array('Term One Name','Term Two Name','term-three-slug') )
  protected $tax_omit    = array();     #    taxonomy terms to omit from normal queries - FIXME: not yet implemented

  #  Experimental
  protected $cap_suffix  =  array();    #    can be used to assign custom suffix for capabilities.  buggy - don't use this, or send me a fix
  protected $sidebar     =  false;      #    set to true to register sidebar with default of array('id'=>$this->type,'name'=>$this->label).

  #  Important: Do not set these in the child class
  protected static $types = array('posts'=>null);
  //  FIXME:  this next line needs to be handled differently
  private $cpt_nodelete = false;       #    if true then implement no deletion policy on builtin taxonomies assigned to this cpt
  private $nodelete     = array();     #    used in $this->taxonomy_registration($args)

  public function __construct($data) {
    if ((isset($data['type']) && !post_type_exists($data['type'])) || !post_type_exists($this->type)) {
      if (isset($data['nodelete'])) { $this->cpt_nodelete = true; }
      unset($data['cpt_nodelete'],$data['nodelete']);
      foreach($data as $prop=>$value) {
        $this->{$prop} = $value;
      }
#      foreach((array)$this as $key=>$value) {
#        if (isset($data[$key])) $this->{$key} = $data[$key];
#      }
      $this->type = (empty($this->type)) ? sanitize_title($this->label) : sanitize_title($this->type);
      add_action('init', array( $this, 'create_post_type'));
      add_action('add_meta_boxes_'.$this->type, array($this,'check_meta_boxes'));
      add_action('contextual_help', array($this,'contextual_help'), 10, 3 );
      add_filter('post_updated_messages', array($this,'post_type_messages'));
      if ($this->columns) {      #  Add/Remove cpt screen columns
        $this->setup_columns(); }
      if ($this->comments) {     #  Allow comments for cpt
        add_filter('comments_open', array($this,'comments_limit'),10,2);
        add_filter('pings_open',    array($this,'comments_limit'),10,2);
      }
      if ($this->cpt_nodelete) { #  Add nodelete code for builtin taxonomies
        $this->add_builtins(); }
      if ($this->main_blog) {    #  Force cpt in main wp query
        add_filter('pre_get_posts', array($this,'pre_get_posts'),5); }   #  run early - priority 5
      if ($this->tax_omit) {     #  Stop posts with tax term from showing in any query
        add_filter('pre_get_posts', array($this,'omit_get_posts'),6); }  #  run early - priority 6
      if ($this->sidebar) {
        add_action('widgets_init',array($this,'register_sidebar'),20); } # run late - priority 20
      if ( ! $this->slug_edit) { #  Deny admin ability to edit taxonomy term slugs
        add_action('admin_enqueue_scripts',array($this,'stop_slug_edit')); }
      if ($this->templates) {    #  Handle templates
        add_filter('template_include', array($this,'assign_template')); }
      if ($this->user_col) {     #  Add count column to Users screen
        add_action('manage_users_columns',array($this,'manage_users_columns'));
        add_action('manage_users_custom_column',array($this,'manage_users_custom_column'),10,3);
      }
      if (!isset(static::$types[$this->type])) {
        static::$types[$this->type] = $this;
      }
    }
  }

  public function __destruct() {  // FIXME:  php internals - will this get called?
    unset(static::$types[$this->type]);
  }

  #  http://php.net/manual/en/language.oop5.overloading.php#object.unset
  public function __get($name) {
    if (property_exists($this,$name)) {
      return $this->$name; } #  Allow read access to private/protected variables
    return null;
  }

  #  http://php.net/manual/en/language.oop5.overloading.php#object.unset
  public function __isset($name) {
    return isset($this->$name); #  Allow read access to private/protected variables
  } //*/


  /**  Text functions  **/

  public function contextual_help( $contextual_help, $screen_id, $screen ) {
    #$this->logging("contextual help:  $screen_id", $contextual_help);
    if ( $screen->id == $this->type ) {
      if (isset($this->contextual_help)) { $contextual_help = $this->contextual_help; }
    } elseif ( $screen->id == "edit-{$this->type}") {
      if (isset($this->contextual_edit)) { $contextual_help = $this->contextual_edit; }
    }
    return $contextual_help;
  }

  protected function translate_post_count($count) {
    return _nx('%1$s %2$s by this author','%1$s %2$s by this author',$count,'first placeholder is numeric, second should be a noun','tcc-custom-post');
  }

  protected function translated_text() {
    static $text;
    if (empty($text)) {
      $text =  array('404'     => _x('No %s found',          'placeholder is plural form',  'tcc-custom-post'),
                     'add'     => _x('Add New %s',           'placeheader is singular form','tcc-custom-post'),
                     'add_rem' => _x('Add or remove $s',     'placeholder is plural form',  'tcc-custom-post'),
                     'all'     => _x('All %s',               'placeholder is plural form',  'tcc-custom-post'),
                     'archive' => _x('%s Archive',           'placeholder is singular form','tcc-custom-post'),
                     'commas'  => _x('Separate %s with commas','placeholder is plural form','tcc-custom-post'),
                     'edit_p'  => _x('Edit %s',              'placeholder is plural form',  'tcc-custom-post'),
                     'edit_s'  => _x('Edit %s',              'placeholder is singular form','tcc-custom-post'),
                     'feature' => _x('%s Image',             'placeholder is singular form','tcc-custom-post'),
                     'feat_rem'=> _x('Remove %s image',      'placeholder is singular form','tcc-custom-post'),
                     'feat_set'=> _x('Set %s image',         'placeholder is singular form','tcc-custom-post'),
                     'feat_use'=> _x('Use as %s image',      'placeholder is singular form','tcc-custom-post'),
                     'filter'  => _x('Filter %s list',       'placeholder is plural form',  'tcc-custom-post'),
                     'insert'  => _x('Insert into %s',       'placeholder is singular form','tcc-custom-post'),
                     'list'    => _x('%s list',              'placeholder is singular form','tcc-custom-post'),
                     'navig'   => _x('%s list navigation',   'placeholder is plural form',  'tcc-custom-post'),
                     'new'     => _x('New %s',               'placeholder is singular form','tcc-custom-post'),
                     'none'    => _x('No %s',                'placeholder is plural form',  'tcc-custom-post'),
                     'parent'  => _x('Parent %s',            'placeholder is singular form','tcc-custom-post'),
                     'popular' => _x('Popular %s',           'placeholder is plural form',  'tcc-custom-post'),
                     'search'  => _x('Search %s',            'placeholder is plural form',  'tcc-custom-post'),
                     'trash'   => _x('No %s found in trash', 'placeholder is plural form',  'tcc-custom-post'),
                     'update'  => _x('Update %s',            'placeholder is singular form','tcc-custom-post'),
                     'upload'  => _x('Uploaded to this %s',  'placeholder is singular form','tcc-custom-post'),
                     'used'    => _x('Choose from the most used %s','placeholder is plural form','tcc-custom-post'),
                     'view_p'  => _x('View %s',              'placeholder is plural form',  'tcc-custom-post'),
                     'view_s'  => _x('View %s',              'placeholder is singular form','tcc-custom-post'),
                     'messages'=> array(
                         'custom_u' => __('Custom field updated.', 'tcc-custom-post'),
                         'custom_d' => __('Custom field deleted.', 'tcc-custom-post'),
                         'draft'    => _x('%s draft updated.','placeholder is singular form', 'tcc-custom-post'),
                         'preview'  => _x('Preview %s',       'placeholder is singular form', 'tcc-custom-post'),
                         'publish'  => _x('%s published.',    'placeholder is singular form', 'tcc-custom-post'),
                         'revision' => _x('%1$s restored to revision from %2$s', '1: label in singular form, 2: date and time of the revision','tcc-custom-post'),
                         'saved'    => _x('%s saved.',        'placeholder is singular form', 'tcc-custom-post'),
                         'schedule' => _x('%1$s publication scheduled for %2$s', '1: label in singular form, 2: formatted date string','tcc-custom-post'),
                         'submit'   => _x('%s submitted.',    'placeholder is singular form', 'tcc-custom-post'),
                         'update'   => _x('%s updated.',      'placeholder is singular form', 'tcc-custom-post')));
    }
    return $text;
  }


  /* Create Post Type functions */

  public function create_post_type() {
    if (empty($this->rewrite) || empty($this->rewrite['slug'])) { $this->rewrite['slug'] = $this->type; }
    $caps = array( sanitize_title($this->label), sanitize_title($this->plural) ); # Note: method add_caps
    $args = array(
        'label'             => $this->plural,
        'labels'            => $this->post_type_labels(),
        'description'       => $this->descrip,
        'public'            => (isset($this->public)) ? $this->public : true,
        'show_in_admin_bar' => (isset($this->show_in_admin_bar)) ? $this->show_in_admin_bar : false,
        'menu_position'     => $this->menu_position,
        'menu_icon'         => $this->menu_icon,
        'capability_type'   => (isset($this->capability_type)) ? $this->capability_type : (empty($this->caps)) ? $caps : $this->caps,
        'map_meta_cap'      => (isset($this->map_meta_cap))    ? $this->map_meta_cap : true,
        'hierarchical'      => (isset($this->hierarchical))    ? $this->hierarchical : false,
        'query_var'         => (isset($this->query_var))       ? $this->query_var    : false,
        'supports'          => $this->supports,
        'taxonomies'        => $this->taxonomies,
        'has_archive'       => (isset($this->has_archive)) ? $this->has_archive : $this->type,
        'rewrite'           => $this->rewrite);
    $args = apply_filters('tcc_register_post_'.$this->type,$args);
    register_post_type($this->type,$args);
    do_action('tcc_custom_post_'.$this->type);
    if ($args['map_meta_cap'])  add_action('admin_init', array($this,'add_caps'));
    #$this->logging('cpt object',$this);
    #$this->logging('post type settings',$GLOBALS['wp_post_types'][$this->type]);
    foreach($this->supports as $support) {
      #$this->logging("supports $support: ".((post_type_supports($this->type,$support)) ? 'true' : 'false'));
    }
  }

  protected function post_type_labels() {
    $phrases = $this->translated_text();
    $arr = array (
      'name'          => $this->plural,
      'singular_name' => $this->label,
      'add_new'       => sprintf($phrases['add'],    $this->label),
      'add_new_item'  => sprintf($phrases['add'],    $this->label),
      'edit_item'     => sprintf($phrases['edit_s'], $this->label),
      'new_item'      => sprintf($phrases['new'],    $this->label),
      'view_item'     => sprintf($phrases['view_s'], $this->label),
      'search_items'  => sprintf($phrases['search'], $this->plural),
      'not_found'     => sprintf($phrases['404'],    $this->plural),
      'not_found_in_trash'    => sprintf($phrases['trash'],  $this->plural),
      'parent_item_colon'     => sprintf($phrases['parent'], $this->label).':',
      'all_items'             => sprintf($phrases['all'],    $this->plural),
      'archives'              => sprintf($phrases['all'],    $this->plural),
      'insert_into_item'      => sprintf($phrases['insert'], $this->label),
      'uploaded_to_this_item' => sprintf($phrases['upload'], $this->label),
      'featured_image'        => sprintf($phrases['feature'],$this->label),
      'set_featured_image'    => sprintf($phrases['feat_set'],strtolower($this->label)),
      'remove_featured_image' => sprintf($phrases['feat_rem'],strtolower($this->label)),
      'use_featured_image'    => sprintf($phrases['feat_use'],strtolower($this->label)),
      'menu_name'             => $this->plural,
      'filter_items_list'     => sprintf($phrases['filter'], $this->plural),
      'items_list_navigation' => sprintf($phrases['navig'],  $this->plural),
      'items_list'    => sprintf($phrases['list'],   $this->label),
      'edit'          => sprintf($phrases['edit_p'], $this->plural),
      'view'          => sprintf($phrases['view_p'], $this->plural),
      'items_archive' => sprintf($phrases['archive'],$this->label));
    return apply_filters('tcc_post_labels_'.$this->type,$arr);
  }

  # http://codex.wordpress.org/Function_Reference/register_post_type
  # http://thomasmaxson.com/update-messages-for-custom-post-types/
  public function post_type_messages($messages) {
    $phrases = $this->translated_text();
    $strings = $phrases['messages'];
    $view_link = $preview_link = $formed_date = '';
    if ($post=get_post()) { #  get_post() call should always succeed when editing a post
      $view_text      = sprintf( $phrases['view_s'], $this->label);
      $preview_text   = sprintf( $strings['preview'],$this->label);
      $link_tag_html  = '  <a href="%s" target="'.sanitize_title($post->post_title).'">';
      $view_link      = sprintf( $link_tag_html, esc_url( get_permalink($post->ID))) .$view_text.'</a>';
      $preview_link   = sprintf( $link_tag_html, esc_url( add_query_arg('preview', 'true', get_permalink($post->ID)))) .$preview_text.'</a>';
      $formed_date    = date_i18n( get_option('date_format'), strtotime($post->post_date));
    }
    $messages[$this->type] = array( 0 => '', #  Unused. Messages start at index 1.
      1  => sprintf( $strings['update'],  $this->label) .$view_link,
      2  => $strings['custom_u'],
      3  => $strings['custom_d'],
      4  => sprintf( $strings['update'],  $this->label),
      5  => isset($_GET['revision']) ? sprintf( $strings['revision'], $this->label, wp_post_revision_title((int)$_GET['revision'],false)) : false,
      6  => sprintf( $strings['publish'], $this->label) .$view_link,
      7  => sprintf( $strings['saved'],   $this->label),
      8  => sprintf( $strings['submit'],  $this->label) .$preview_link,
      9  => sprintf( $strings['schedule'],$this->label,  $formed_date) .$preview_link,
      10 => sprintf( $strings['draft'],   $this->label) .$preview_link);
    return apply_filters('tcc_post_type_messages',$messages);
  }

  public function register_sidebar() {
   #$this->logging('register sidebar');
    $sidebar = array('id' => $this->type, 'name' => $this->label);
    if (is_array($this->sidebar)) $sidebar = array_merge($sidebar,$this->sidebar);
    register_sidebar($sidebar);
  }


  /*  Capabilities  */

  #  This only gets run if map_meta_caps is true
  #  http://stackoverflow.com/questions/18324883/wordpress-custom-post-type-capabilities-admin-cant-edit-post-type
  public function add_caps() {
    $roles = array('contributor','author','editor','administrator');
    if ($this->role_caps==='admin') $roles = array('administrator'); // FIXME: provide for custom roles
    foreach($roles as $role) {
      $this->process_caps($role); }
  }

  private function process_caps($name) {
    $role = get_role($name);
    #$this->logging('user role',$role);
    $sing = sanitize_title($this->label);
    $plur = sanitize_title($this->plural);
    $caps = array("delete_$sing","edit_$sing","read_$sing","delete_$plur","edit_$plur");
    $auth = array("delete_published_$plur","edit_published_$plur","publish_$plur");
    $edit = array("delete_others_$plur","delete_private_$plur","edit_others_$plur","edit_private_$plur","read_private_$plur");
    if (in_array($role,array('author','editor','administrator'))) {
      $caps = array_merge($caps,$auth); }
    if (in_array($role,array('editor','administrator'))) {
      $caps = array_merge($caps,$edit); }
    foreach($caps as $cap) {
      $role->add_cap($cap); }
  }


  /* Taxonomy functions */

  protected function taxonomy_labels($single,$plural) {
    $phrases = $this->translated_text();
    $arr = array('name'              => $plural,
                 'singular_name'     => $single,
                 'search_items'      => sprintf($phrases['search'], $plural),
                 'popular_items'     => sprintf($phrases['popular'],$plural),
                 'all_items'         => sprintf($phrases['all'],    $plural),
                 'parent_item'       => sprintf($phrases['parent'], $single),
                 'parent_item_colon' => sprintf($phrases['parent'], $single).':',
                 'edit_item'         => sprintf($phrases['edit_s'], $single),
                 'view_item'         => sprintf($phrases['view_s'], $single),
                 'update_item'       => sprintf($phrases['update'], $single),
                 'add_new_item'      => sprintf($phrases['add'],    $single),
                 'new_item_name'     => sprintf($phrases['new'],    $single),
                 'separate_items_with_commas' => sprintf($phrases['commas'],  $plural),
                 'add_or_remove_items'        => sprintf($phrases['add_rem'], $plural),
                 'choose_from_most_used'      => sprintf($phrases['used'],    $plural),
                 'not_found'                  => sprintf($phrases['404'],     $plural),
                 'menu_name'                  => $plural,
                 'no_terms'                   => sprintf($phrases['none'],    $plural),
                 'items_list_navigation'      => sprintf($phrases['navig'],   $plural),
                 'items_list'                 => sprintf($phrases['list'],    $plural));
    return apply_filters('tcc_taxonomy_labels',$arr);  #  Alternate, more specific, filter: 'tcc_taxonomy_labels_{tax slug}'
  }

  protected function taxonomy_registration($args) {
    $defs = array('admin'=>false,'submenu'=>false,'nodelete'=>false,'func'=>null);
    $args = wp_parse_args($args,$defs);
    extract($args);  #  see README.md for extracted variables list
    if (empty($tax))     return;
    if (empty($taxargs)) $taxargs = array();

    if (empty($single) && empty($taxargs['labels']['singular_name'])) return;  #  Notice the silent return
    $single = (isset($taxargs['labels']['singular_name'])) ? $taxargs['labels']['singular_name'] : $single;
    if (empty($plural) && empty($taxargs['labels']['name']) && empty($taxargs['label'])) return;  #  Here too
    $plural = (isset($taxargs['labels']['name'])) ? $taxargs['labels']['name'] : (isset($taxargs['label'])) ? $taxargs['label'] : $plural;
    $labels = $this->taxonomy_labels($single,$plural);
    $labels = apply_filters('tcc_taxonomy_labels_'.$tax,$labels);  #  Alternate, more general, filter: 'tcc_taxonomy_labels'

    $taxargs['labels']  = (isset($taxargs['labels'])) ? array_merge($labels,$taxargs['labels']) : $labels;
    $taxargs['show_admin_column'] = (isset($taxargs['show_admin_column'])) ? $taxargs['show_admin_column'] : $admin;
    $taxargs['rewrite'] = (isset($taxargs['rewrite'])) ? $taxargs['rewrite'] : (isset($rewrite)) ? array('slug'=>$rewrite) : array('slug'=>$tax);
    $taxargs = apply_filters('tcc_register_taxonomy_'.$tax,$taxargs,$args);

    register_taxonomy($tax,$this->type,$taxargs);
    if (taxonomy_exists($tax)) {
      if (!in_array($tax,$this->tax_list)) { $this->tax_list[] = $tax; }
      register_taxonomy_for_object_type($tax,$this->type);
      $current = get_terms($tax,'hide_empty=0');
      if (empty($current)) {
        $defs = array();
        if (empty($terms)) {
          $func = (is_null($func)) ? "default_$tax" : $func;
          if ($func) {
            if (is_array($func) && method_exists($func[0],$func[1])) { non_function(); } // FIXME?
            elseif (method_exists($this,$func)) { $defs = $this->$func(); }
            elseif (function_exists($func))     { $defs = $func(); }
          }
        } else {
          $defs = $terms;
        }
        if ($defs) {
          foreach($defs as $key=>$term) { // FIXME:  provide for description
            if (is_numeric($key)) {
              wp_insert_term($term,$tax);
            } else {
              wp_insert_term($term,$tax,array('slug'=>$key));
            }
          }
        }
      }
      if (($submenu) && (method_exists($this,$submenu))) {
        add_filter('wp_get_nav_menu_items',array($this,$submenu)); }
      if ($nodelete) {
        $this->nodelete[] = $tax;
        if (!has_action('admin_enqueue_scripts', array($this,'stop_term_deletion'))) {
          add_action('admin_enqueue_scripts', array($this,'stop_term_deletion')); }
      }
      if (!empty($omit)) {
        $this->omit[$tax] = (empty($this->omit[$tax])) ? $omit : array_merge($this->omit[$tax],$omit);
        if (!has_filter('pre_get_posts', array($this,'omit_get_posts'))) { add_filter('pre_get_posts', array($this,'omit_get_posts'),6); }
      }
    }
  }

  private function add_builtins() {
    $this->logging('function: add_builtins');
    $check = array('post_tag','category');
    foreach($check as $tax) {
      $this->nodelete[] = $tax;
    }
    if (!has_action('admin_enqueue_scripts', array($this,'stop_term_deletion'))) {
      add_action('admin_enqueue_scripts', array($this,'stop_term_deletion')); }
  }

  public function stop_slug_edit() {
    $screen = get_current_screen();
    if ($screen->base=='edit-tags') {
      $noedit = ($this->js_path) ? plugin_dir_url($this->js_path.'/dummy.js').'slug_noedit.js' : plugins_url('../js/slug_noedit.js',__FILE__);
      wp_register_script('slug_noedit',$noedit,array('jquery'),false,true);
      wp_enqueue_script('slug_noedit');
    }
  }


  /*  Term functions  */

  public function stop_term_deletion() {
    $screen = get_current_screen();
    if (($screen->base=='edit-tags') && (in_array($screen->taxonomy,$this->nodelete))) {
      $keep_list = array();
      if (!empty($this->tax_keep[$screen->taxonomy])) {
        foreach($this->tax_keep[$screen->taxonomy] as $term) {
          $keep_list[] = $this->get_term_id($term,$screen->taxonomy);
        }
      }
      $term_list = get_terms($screen->taxonomy,'hide_empty=1');
      if ($term_list) {
        foreach($term_list as $term) {
          $keep_list[] = $term->term_id; }
      }
      if ($keep_list) {
        $keep_list = array_unique($keep_list);
        $this->logging($keep_list);
        $nodelete  = ($this->js_path) ? plugin_dir_url($this->js_path.'/dummy.js').'tax_nodelete.js' : plugins_url('../js/tax_nodelete.js',__FILE__);
        wp_register_script('tax_nodelete',$nodelete,array('jquery'),false,true);
        wp_localize_script('tax_nodelete','term_list',$keep_list);
        wp_enqueue_script('tax_nodelete');
      }
    }
  }

  private function get_term_id($term,$tax) {
    if ($term===sanitize_title($term)) {
      return get_term_by('slug',$term,$tax)->term_id;
    } else {
      return get_term_by('name',$term,$tax)->term_id;
    }
  }

/*  public function taxonomy_menu_dropdown($taxonomy,$args='hide_empty=1') {
    $output = array();
    $taxon  = get_taxonomies("name=$taxonomy",'objects');
#    $terms  = get_terms($taxonomy,$args);
#    $site   = get_bloginfo('url');
#    foreach($terms as $term){
#      $tax    = $term->taxonomy; // FIXME: get taxonomy rewrite slug
#      $slug   = $term->slug;
#      $link   = "$site/$tax/$slug";
#      $output[$link] = $term->name;
#    }
    return $output;
  } //*/


  /*  Post Admin Column functions/filters  */

  /**  CPT screen  **/

  private function setup_columns() {
    if (isset($this->columns['remove'])) {
      add_filter("manage_edit-{$this->type}_columns",array($this,'remove_custom_post_columns')); }
    if (isset($data['columns']['add'])) {
      add_filter("manage_edit-{$this->type}_columns",array($this,'add_custom_post_columns'));
      add_filter("manage_edit-{$this->type}_sortable_columns",array($this,'add_custom_post_columns'));
      if (isset($data['columns']['content'])) {
        if (is_callable(array($this,$this->columns['content']))) {
          add_action('manage_posts_custom_column',array($this,$this->columns['content']),10,2);
        } else {
          $this->logging('columns[content] not callable: '.$this->columns['content']); }
      }
    }
  }

  public function add_custom_post_columns($columns) {
    foreach($this->columns['add'] as $key=>$col) {
      if (!isset($columns[$key])) $columns[$key] = $col; }
    return $columns;
  } //*/

  public function remove_custom_post_columns($columns) {
    foreach($this->columns['remove'] as $no_col) {
      if (isset($columns[$no_col])) { unset($columns[$no_col]); } }
    return $columns;
  } //*/

  /**  Users screen  **/
  # http://wordpress.stackexchange.com/questions/3233/showing-users-post-counts-by-custom-post-type-in-the-admins-user-list
  # https://gist.github.com/mikeschinkel/643240
  # http://www.wpcustoms.net/snippets/post-count-users-custom-post-type/

  public function manage_users_columns($column_headers) {
    $index = "{$this->type} num";  # get WP to add the num css class
    $column_headers[$index] = $this->plural;
    return $column_headers;
  }

  public function manage_users_custom_column($column,$column_name,$user_id) {
    $index = "{$this->type} num";
    if ($column_name==$index) {
      $counts = $this->get_author_post_type_counts();
      if (isset($counts[$user_id])) {
        $link = admin_url() . "edit.php?post_type={$this->type}&author=".$user_id;
        $column = "<a href={$link}>";
        $column.= "<span aria-hidden='true'>{$counts[$user_id]}</span>";
        $column.= "<span class='screen-reader-text'>";
        $string = $this->translate_post_count($counts[$user_id]);
        $place  = ($counts[$user_id]==1) ? $this->label : $this->plural;
        $column.= sprintf($string,$counts[$user_id],$place);
        $column.= "</span></a>";
      } else {
        $column = "[none]";
      }
    }
    return $column;
  }

  private function get_author_post_type_counts() {
    static $counts;
    if (!isset($counts)) {
      global $wpdb;
      $sql = "SELECT post_author, COUNT(*) AS post_count FROM {$wpdb->posts}";
      $sql.= " WHERE post_type='{$this->type}' AND post_status IN ('publish','pending', 'draft')";
      $sql.= " GROUP BY post_author";
      $authors = $wpdb->get_results($sql);
      foreach($authors as $author) {
        $counts[$author->post_author] = $author->post_count;
      }
    }
    return $counts;
  } //*/


  /*  Template filters  */


  #  http://codex.wordpress.org/Function_Reference/locate_template
  #  https://wordpress.org/support/topic/stylesheetpath-in-plugin
  public function assign_template($template) {
    $post_id = get_the_ID();
    if ($post_id) {
      $mytype = get_post_type($post_id);
      if ($mytype && ($this->type==$mytype)) {
        if (is_single()) {
          $template = $this->locate_template($template,'single');
        } else if (is_search() || is_post_type_archive($this->type)) {
          $template = $this->locate_template($template,'archive');
        }
        $template = apply_filters('tcc_assign_template_'.$this->type,$template);
      }
    }
    return $template;
  } //*/

  private function locate_template($template,$slug) {
    if (isset($this->templates[$slug])) {
      $template = $this->templates[$slug];
    } elseif (($slug==='archive') && isset($this->has_archive) && is_string($this->has_archive)) {
      $template = $this->has_archive;
    } elseif (isset($this->templates['folders'])) {
      foreach((array)$this->templates['folders'] as $folder) {
        $test = $folder."/$slug-{$this->type}.php";
        if (file_exists($test)) {
          $template = $test;
          break;
        }
      }
    } else {
      $maybe = locate_template(array("$slug-{$this->type}.php"));
      if ($maybe) { $template = $maybe; }
    }
    return $template;
  }


  /**  Alternate Template filters  **/

  private function assign_template_filters() {
    if (!empty($this->templates['single'])) {
      add_filter('single_template', array($this,'single_template'));
    }
    if (!empty($this->templates['archive'])) {
      add_filter('archive_template', array($this,'archive_template'));
    } /*  FIXME:  Test this construct
    foreach($this->templates as $key=>$template) {
      if ($key==='folders') {
        // FIXME:  this needs to be handled
        continue;
      }
      add_filter("{$key}_template", function($mytemplate) use ($key) { // FIXME:  does it need to use $this?
        global $post;
        if ($post->post_type===$this->type) {
          $mytemplate = $this->templates[$key];
        }
        return $mytemplate;
      });
    } //*/
  }

  public function archive_template($archive_template) {
    global $post;
    if ($post->post_type===$this->type) {
      $archive_template = $this->templates['archive'];
     }
     return $archive_template;
  }

  public function single_template($single_template) {
    global $post;
    if ($post->post_type===$this->type) {
      $single_template = $this->templates['single'];
     }
     return $single_template;
  }


  /*  Comments  */

  public function comments_limit($open,$post_id) {
    $mytype = get_post_type($post_id);
    if ($this->type==$mytype) {
      if (is_singular($mytype)) {
        if ((isset($this->comments)) && ($this->comments)) {
          if (is_bool($this->comments)) {
            $open = $this->comments;
          } else { // FIXME:  support numeric values
#            $postime = get_the_time('U', $post_id);
             $this->logging("WARNING: Numeric values for {$this->type}->comments is not yet supported.");
          }
        }
      }
    }
    return $open;
  } //*/


  /*  Query modifications  */

  // https://wordpress.org/support/topic/custom-post-type-posts-not-displayed
  public function pre_get_posts($query) {
    if (!is_admin() && $query->is_main_query() && !($query->is_page())) {
      $check = $query->get('post_type');
      #$this->logging('main query post type',$check);
      if (empty($check)) {  #  || (is_post_type_archive($this->type))) {
        $query->set('post_type',array('post',$this->type));
      } elseif (!((array)$check==$check)) {
        if ($check!==$this->type) $query->set('post_type',array($check,$this->type));
      } elseif (!in_array($this->type,$check)) {
        $check[] = $this->type;
        $query->set('post_type',$check);
      }
    }
    return $query;
  }

  public function omit_get_posts($query) {
    if ($this->tax_omit) {
      if (!is_admin()) {  #  && $query->is_main_query()) {
        if ((!$query->is_page()) || (is_feed())) {
          $check = $query->get('post_type');
          if (in_array($this->type,(array)$check)) {
            foreach($this->tax_omit as $tax) {
              $terms = array();
              foreach($tax as $term) {
                $terms[] = $this->get_term_id($term,$tax);
              }
              $omit = '-'.implode(',-',$terms);
              if ($tax=='category') {
                $query->set('cat',$omit);
              } elseif ($tax=='post_tag') {
                $query->set('tag',$omit);
              } else {
                $query->set('tax_query', array( array( 'taxonomy'=>$tax, 'field'=>'id', 'terms'=>$terms, 'operator'=>'NOT IN' ) ) );
              }
            }
          }
        }
      }
    }
  }


  /*  Meta box  */

  public function check_meta_boxes() {
    if (!$this->caps==='post') {
      $cap = "edit_others_".sanitize_title($this->plural);
      if (!current_user_can($cap)) {
        remove_meta_box('authordiv',$this->type,'normal');
      }
    }
  }


  /*  Debugging  */

  public function logging() {
    if ($this->debug && isset($this->logging)) {
      $log = $this->logging;
      if (is_array($log) && method_exists($log)) {  #  Method in a different class
        extract($log,EXTR_PREFIX_INVALID,'logger');
        foreach (func_get_args() as $message) { $logger_0->$logger_1($message); }
      } elseif (function_exists($log)) {            #  Function
        foreach (func_get_args() as $message) { $log($message); }
      } elseif (method_exists($this,$log)) {        #  Method in this class
        foreach (func_get_args() as $message) { $this->$log($message); }
      }
    }
  }

}

?>
