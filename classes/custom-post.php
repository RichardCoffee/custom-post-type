<?php

/*
 *  abstract class for WordPress custom post types
 *
 *
 *  Copyright 2009-2016, Richard Coffee
 */

abstract class Custom_Post_Type {

  protected $type     = '';  #  'custom_post_type_name'
  protected $label    = '';  #  _x('Custom Post Type','singular form','textdomain')
  protected $plural   = '';  #  _x('Custom Post Types','plural form','textdomain')
  protected $descrip  = '';  #  __('Custom Post Type Title','textdomain')

  protected $caps       = 'post';      #  default is to not create custom capabilities
  protected $columns    = null;        #  array('remove'=>array()','add'=>array())
  protected $comments   = false;       #  boolean:  allow comments
  protected $debug      = false;       #  used in conjunction with $this->logging
  protected $menu_icon  = 'dashicons-admin-post'; #  admin dashboard icon
  protected $logging    = 'log_entry'; #  assign your own logging function here
  protected $main_blog  = true;        #  set to false to not force inclusion in WP post queries
  private   $nodelete   = array();     #  used in $this->taxonomy_registration($args)
  protected $position   = 6;           #  position on admin dashboard
  protected $rewrite    = array();     #  array('slug'=>$this->type));
  protected $role_caps  = 'normal';    #  value of 'admin' will cause only the administrator caps to be updated - FIXME: allow array of roles
  protected $slug_edit  = true;        #  whether to allow editing of taxonomy slugs in admin screen
  protected $supports   = array('title','editor','author','thumbnail','revisions','comments');
  protected $tax_list   = array();
  protected $taxonomies = array();     #  array('post_tag','category');  passed to register_post_type() - FIXME:  possible auto call of $this->taxonomy_registration()
  protected $tax_keep   = array();     #  example: array( 'taxonomy-slug' => array('Term One Name','Term Two Name','term-three-slug') )
  protected $templates  = false;       #  example: array( 'single' => WP_PLUGIN_DIR.'/plugin_dir/templates/single-{cpt-slug}.php' )

  private static $types = array('posts');

  public function __construct($data) {
    if (!post_type_exists($data['type'])) {
      foreach($data as $prop=>$value) {
        $this->{$prop} = $value;
      }
      if (empty($this->type)) { $this->type = sanitize_title($this->label); }  // seriously?
      add_action('init',                 array($this,'create_post_type'));
      add_action('add_meta_boxes_'.$this->type, array($this,'check_meta_boxes'));
      add_filter('post_updated_messages',array($this,'post_type_messages'));
      if ($this->columns) {
        $this->setup_columns(); }
      if ($this->comments) {
        add_filter('comments_open',array($this,'comments_limit'),10,2);
        add_filter('pings_open',   array($this,'comments_limit'),10,2);
      } //*/
      if ($this->main_blog) {
        add_filter('pre_get_posts',        array($this,'pre_get_posts'),5); } #  run early - priority 5
      if ( ! $this->slug_edit) {
        add_action('admin_enqueue_scripts',array($this,'stop_slug_edit')); }
      if ($this->templates) {
        add_filter('template_include',     array($this,'assign_template')); }
    }
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

  protected function translated_text() {
    static $text;
    if (empty($text)) {
      $text =  array('404'     => _x('No %s found','placeholder is plural form',  'tcc-custom-post'),
                     'add'     => _x('Add New %s', 'placeheader is singular form','tcc-custom-post'),
                     'all'     => _x('All %s',     'placeholder is plural form',  'tcc-custom-post'),
                     'archive' => _x('%s Archive', 'placeholder is singular form','tcc-custom-post'),
                     'edit_p'  => _x('Edit %s',    'placeholder is plural form',  'tcc-custom-post'),
                     'edit_s'  => _x('Edit %s',    'placeholder is singular form','tcc-custom-post'),
                     'new'     => _x('New %s',     'placeholder is singular form','tcc-custom-post'),
                     'search'  => _x('Search %s',  'placeholder is plural form',  'tcc-custom-post'),
                     'trash'   => _x('No %s found in trash','placeholder is plural form','tcc-custom-post'),
                     'update'  => _x('Update %s',  'placeholder is singular form','tcc-custom-post'),
                     'view_p'  => _x('View %s',    'placeholder is plural form',  'tcc-custom-post'),
                     'view_s'  => _x('View %s',    'placeholder is singular form','tcc-custom-post'),
                     'messages'=> array('custom_u' => __('Custom field updated.', 'tcc-custom-post'),
                         'custom_d' => __('Custom field deleted.','tcc-custom-post' ),
                         'draft'    => _x('%s draft updated.','placeholder is singular form', 'tcc-custom-post'),
                         'preview'  => _x('Preview $s',       'placeholder is singular form', 'tcc-custom-post'),
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
        'menu_position'     => $this->position,
        'menu_icon'         => $this->menu_icon,
        'capability_type'   => (isset($this->capability_type)) ? $this->capability_type : (empty($this->caps)) ? $caps : $this->caps,
        'map_meta_cap'      => (isset($this->map_meta_cap))    ? $this->map_meta_cap : true,
        'hierarchical'      => (isset($this->hierarchical))    ? $this->hierarchical : false,
        'query_var'         => (isset($this->query_var))       ? $this->query_var    : false,
        'supports'          => $this->supports,
        'taxonomies'        => $this->taxonomies,
        'has_archive'       => $this->type,
        'rewrite'           => $this->rewrite);
    $args = apply_filters('tcc_register_post_'.$this->type,$args);
    register_post_type($this->type,$args);
    do_action('tcc_custom_post_'.$this->type);
    if ($args['map_meta_cap'])  add_action('admin_init', array($this,'add_caps'));
    $this->log_entry('post type settings',$GLOBALS['wp_post_types'][$this->type]);
    foreach($this->supports as $support) {
      $this->log_entry("supports $support: ".((post_type_supports($this->type,$support)) ? 'true' : 'false'));
    }
  }

  protected function post_type_labels() {
    $phrases = $this->translated_text();
    $arr = array (
      'name'          => $this->plural,
      'singular_name' => $this->label,
      'add_new'       => sprintf($phrases['add'],    $this->label),
      'add_new_item'  => sprintf($phrases['add'],    $this->label),
      'edit'          => sprintf($phrases['edit_p'], $this->plural),
      'edit_item'     => sprintf($phrases['edit_s'], $this->label),
      'new_item'      => sprintf($phrases['new'],    $this->label),
      'all_items'     => sprintf($phrases['all'],    $this->plural),
      'view'          => sprintf($phrases['view_p'], $this->plural),
      'view_item'     => sprintf($phrases['view_s'], $this->label),
      'items_archive' => sprintf($phrases['archive'],$this->label),
      'search_items'  => sprintf($phrases['search'], $this->plural),
      'not_found'     => sprintf($phrases['404'],    $this->plural),
      'not_found_in_trash' => sprintf($phrases['trash'],$this->plural));
    $arr = apply_filters('tcc_post_labels_'.$this->type,$arr);
    return $arr;
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
      $link_tag_html  = '  <a href="%s" target="'.sanitize_title($post->title).'">';
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
    return $messages;
  }

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
    $this->log_entry('user role',$role);
    $sing = sanitize_title($this->label); # not sure what these singular caps are supposed to do...
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
    return array('name'              => $plural,
                 'singular_name'     => $single,
                 'search_items'      => sprintf($phrases['search'],$plural),
                 'all_items'         => sprintf($phrases['all'],   $plural),
                 'edit_item'         => sprintf($phrases['edit_s'],$single),
                 'update_item'       => sprintf($phrases['update'],$single),
                 'add_new_item'      => sprintf($phrases['add'],   $single),
                 'new_item_name'     => sprintf($phrases['new'],   $single),
                 'menu_name'         => $plural);
  }

  protected function taxonomy_registration($args) {
    $defs = array('admin'=>false,'submenu'=>false,'nodelete'=>false,'func'=>null);
    $args = wp_parse_args($args,$defs);
    extract($args);  #  see README.md for extracted variables list
    if (empty($tax))     return;
    if (empty($taxargs)) $taxargs = array();
    if (empty($single) && empty($taxargs['labels']['singular_name'])) return;
    $single = (isset($taxargs['labels']['singular_name'])) ? $taxargs['labels']['singular_name'] : $single;
    if (empty($plural) && empty($taxargs['labels']['name']) && empty($taxargs['label'])) return;
    $plural = (isset($taxargs['labels']['name'])) ? $taxargs['labels']['name'] : (isset($taxargs['label'])) ? $taxargs['label'] : $plural;
    $labels = $this->taxonomy_labels($single,$plural);
    $taxargs['labels'] = (isset($taxargs['labels'])) ? array_merge($labels,$taxargs['labels']) : $labels;
    $taxargs['show_admin_column'] = (isset($taxargs['show_admin_column'])) ? $taxargs['show_admin_column'] : $admin;
    $taxargs['rewrite'] = (isset($taxargs['rewrite'])) ? $taxargs['rewrite'] : (isset($rewrite)) ? array('slug'=>$rewrite) : array('slug'=>$tax);
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
          if (!isset($slug)) {
            $test = array_slice($defs,0,1,true);
            $slug = (!isset($test[0]));
          }
          foreach($defs as $key=>$term) { // FIXME:  provide for description
            if ($slug) {
              wp_insert_term($term,$tax,array('slug'=>$key));
            } else {
              wp_insert_term($term,$tax);
            }
          }
        }
      }
      if (($submenu) && (method_exists($this,$submenu))) {
        add_filter('wp_get_nav_menu_items',array($this,$submenu)); }
      if ($nodelete) {
        $this->nodelete[] = $tax;
        add_action('admin_enqueue_scripts',array($this,'stop_term_deletion'));
      }
    }
  }

  public function stop_slug_edit() {
    $screen = get_current_screen();
    if ($screen->base=='edit-tags') {
      wp_register_script('slug_noedit',plugins_url('../js/slug_noedit.js',__FILE__),array('jquery'),false,true);
      wp_enqueue_script('slug_noedit');
    }
  }

  public function stop_term_deletion() {
    $screen = get_current_screen();
    if (($screen->base=='edit-tags') && (in_array($screen->taxonomy,$this->nodelete))) {
      $keep_list = array();
      if (!empty($this->tax_keep[$screen->taxonomy])) {
        foreach($this->tax_keep[$screen->taxonomy] as $term) {
          if ($term===sanitize_title($term)) {
            $keep_list[] = get_term_by('slug',$term,$screen->taxonomy)->term_id;
          } else {
            $keep_list[] = get_term_by('name',$term,$screen->taxonomy)->term_id;
          }
        }
      }
      $term_list = get_terms($screen->taxonomy,'hide_empty=1');
      if ($term_list) {
        foreach($term_list as $term) {
          $keep_list[] = $term->term_id; }
      }
      if ($keep_list) {
        $keep_list = array_unique($keep_list);
        wp_register_script('tax_nodelete',plugins_url('../js/tax_nodelete.js',__FILE__),array('jquery'),false,true);
        wp_localize_script('tax_nodelete','term_list',$keep_list);
        wp_enqueue_script('tax_nodelete');
      }
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
          $this->log_entry('columns[content] not callable: '.$this->columns['content']); }
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


  /*  Template filters  */

  #  http://codex.wordpress.org/Function_Reference/locate_template
  #  https://wordpress.org/support/topic/stylesheetpath-in-plugin
  public function assign_template($template) {
    if ($post_id=get_the_ID()) {
      $mytype = get_post_type($post_id);
      if ($mytype && ($this->type==$mytype)) {
        if (is_single()) {
          $template = $this->locate_template($template,'single');
        } // FIXME: search, archive
        $template = apply_filters('tcc_assign_template_'.$this->type,$template);
      }
    }
    return $template;
  } //*/

  private function locate_template($template,$slug) {
    if (isset($this->templates[$slug])) {
      $template = $this->templates[$slug];
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

  public function comments_limit($open,$post_id) {
    $mytype = get_post_type($post_id);
    if ($this->type==$mytype) {
      if (is_singular($mytype)) {
        if ((isset($this->comments)) && ($this->comments)) {
          if (is_bool($this->comments)) {
            $open = $this->comments;
          } else { // FIXME:  support numeric values
#            $postime = get_the_time('U', $post_id);
             $this->log_entry("WARNING: Numeric values for {$this->type}->comments is not yet supported.");
          }
        }
      }
    }
    return $open;
  } //*/


  // https://wordpress.org/support/topic/custom-post-type-posts-not-displayed
  public function pre_get_posts($query) {
    if (!is_admin()) {
      if ($query->is_main_query()) {
        if ((!$query->is_page()) || (is_feed())) {
          $check = $query->get('post_type');
          if (empty($check)) {
            $query->set('post_type',array('post',$this->type));
          } elseif (!((array)$check==$check)) {
            if ($check!==$this->type) $query->set('post_type',array($check,$this->type));
          } elseif (!in_array($this->type,$check)) {
            $check[] = $this->type;
            $query->set('post_type',$check);
          }
        }
      }
    }
    return $query;
  }

  public function check_meta_boxes() {
    $cap = "edit_others_".sanitize_title($this->plural);
    if (!current_user_can($cap)) {
      remove_meta_box('authordiv',$this->type,'normal');
    }
  }

  private function log_entry() {
    if ($this->debug && isset($this->logging)) {
      $log = $this->logging; // FIXME:  check for array, ie: method of a different class
      if (function_exists($log)) {
        foreach (func_get_args() as $message) { $log($message); }
      } elseif (method_exists($this,$log)) {
        foreach (func_get_args() as $message) { $this->$log($message); }
      }
    }
  }

}

?>
