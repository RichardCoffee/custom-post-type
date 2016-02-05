<?php

abstract class TCC_Custom_Post_Type {

  protected $type     = ''; // 'custom_post_type_name'
  protected $label    = ''; // _x('Custom Post Type','singular form','textdomain')
  protected $plural   = ''; // _x('Custom Post Types','plural form','textdomain')
  protected $descrip  = ''; // __('Custom Post Type Title','textdomain')

  protected $columns    = null; // array('remove'=>array()','add'=>array())
#  protected $comments   = null; // or true
  protected $edit_other = 'edit_others_posts';
  protected $extra      = array();
  protected $icon       = '';
  protected $nodelete   = array();
  protected $position   = 6;
  protected $rewrite    = array(); // array('slug'=>$this->type));
  protected $sidebars   = array();
  protected $slug_edit  = true; // whether to allow editing of taxonomy slugs in admin screen
  protected $supports   = array('title','editor','author','thumbnail','revisions','comments');
  protected $tax_list   = array();
  protected $taxonomies = array('post_tag','category');
  protected $tax_keep   = array();
  protected $template   = false; // array('single'=>WP_PLUGIN_DIR.'/plugin_dir/templates/single-<custom_post_type>.php')
  private static $types = array('posts');

  private static function translated_text() {
    return array('add'    => _x('Add New %s', 'placeheader is singular form','tcc-theme-options'),
                 'all'    => _x('All %s',     'placeholder is plural form',  'tcc-theme-options'),
                 'edit'   => _x('Edit %s',    'placeholder is singular form','tcc-theme-options'),
                 'new'    => _x('New %s',     'placeholder is singular form','tcc-theme-options'),
                 'search' => _x('Search %s',  'placeholder is plural form',  'tcc-theme-options'),
                 'update' => _x('Update %s',  'placeholder is singular form','tcc-theme-options'));
  }

  public function __construct($data) {
    if (!post_type_exists($data['type'])) {
      foreach($data as $prop=>$value) {
        $this->{$prop} = $value;
      }
      add_action('init',                 array($this,'create_post_type'));
      add_action('admin_init',           array($this,'add_caps'));
      add_filter('pre_get_posts',        array($this,'pre_get_posts'),5); // run early
      add_filter('post_updated_messages',array($this,'post_type_messages'));
      if (isset($this->columns)) { $this->setup_columns(); }
/*
      if (isset($this->comments)) {
        add_filter('comments_open',array($this,'comments_limit'),10,2);
        add_filter('pings_open',   array($this,'comments_limit'),10,2);
      } //*/
      add_action('add_meta_boxes_'.$this->type, array($this,'add_meta_boxes'));
      if ($this->sidebars) {
        add_filter('tcc_register_sidebars',array($this,'custom_post_sidebars')); }
      if ($this->template) {
        add_filter('template_include',array($this,'assign_template')); }
      if (!$this->slug_edit) {
        add_action('admin_enqueue_scripts',array($this,'stop_slug_edit'));
      }
    }
  }

  // origin: http://php.net/manual/en/language.oop5.overloading.php#object.unset
  public static function __callStatic($name,$arguments) {
    if (($name=='get_tax_list') && (self::$instance)) {
      return self::$instance->get_tax_list(); }
    return null;
  }

  // origin: http://php.net/manual/en/language.oop5.overloading.php#object.unset
  public function __get($name) {
    if (property_exists($this,$name)) {
      return $this->$name; } // Allow read access to private/protected variables
    return null;
  }

  // origin: http://php.net/manual/en/language.oop5.overloading.php#object.unset
  public function __isset($name) {
    return isset($this->$name); // Allow read access to private/protected variables
  } //*/


  /* Create Post Type functions */

  public function create_post_type() {
    if (empty($this->rewrite) || empty($this->rewrite['slug'])) { $this->rewrite['slug'] = $this->type; }
    $args = array (
        'label'             => $this->plural,
        'labels'            => $this->post_type_labels(),
        'description'       => $this->descrip,
        'public'            => true,
        'show_in_admin_bar' => false,
        'menu_position'     => $this->position,
        'menu_icon'         => $this->icon,
        'capability_type'   => array(sanitize_title($this->label),sanitize_title($this->plural)), # Note: method add_caps
        'map_meta_cap'      => true,
        'hierarchical'      => false,
        'query_var'         => false,
        'supports'          => $this->supports,
        'taxonomies'        => $this->taxonomies,
        'has_archive'       => $this->type,
        'rewrite'           => $this->rewrite);
    $args = apply_filters('tcc_register_post_'.$this->type,$args);
    register_post_type($this->type,$args);
    do_action('tcc_custom_post_'.$this->type);
    #log_entry('post type settings',$GLOBALS['wp_post_types'][$this->type]);
    #log_entry(debug_backtrace());
  }

  private function post_type_labels() {
    $phrases = self::translated_text();
    $arr = array (
      'name'          => $this->plural,
      'singular_name' => $this->label,
      'add_new'       => sprintf($phrases['add'],   $this->label),
      'add_new_item'  => sprintf($phrases['add'],   $this->label),
      'edit'          => sprintf(_x('Edit %s',     'placeholder will be in plural form',  'tcc-theme-options'),$this->plural),
      'edit_item'     => sprintf($phrases['edit'],  $this->label),
      'new_item'      => sprintf($phrases['new'],   $this->label),
      'all_items'     => sprintf($phrases['all'],   $this->plural),
      'view'          => sprintf(_x('View %s',     'placeholder will be in plural form',  'tcc-theme-options'),$this->plural),
      'view_item'     => sprintf(_x('View %s',     'placeholder will be in singular form','tcc-theme-options'),$this->label),
      'items_archive' => sprintf(_x('%s Archive',  'placeholder will be in singular form','tcc-theme-options'),$this->label),
      'search_items'  => sprintf($phrases['search'],$this->plural),
      'not_found'     => sprintf(_x('No %s found', 'placeholder will be in plural form',  'tcc-theme-options'),$this->plural),
      'not_found_in_trash' => sprintf(_x('No %s found in trash','placeholder will be in plural form','tcc-theme-options'),$this->plural));
    return $arr;
  }

  # http://codex.wordpress.org/Function_Reference/register_post_type
  # http://thomasmaxson.com/update-messages-for-custom-post-types/
  public function post_type_messages($messages) {
    $update_text    = _x('%s updated.', 'placeholder is singular form', 'tcc-theme-options');
    $revision_text  = _x('%1$s restored to revision from %2$s', '1: label in singular form, 2: date and time of the revision','tcc-theme-options');
    $published_text = _x('%s published.', 'placeholder is singular form', 'tcc-theme-options');
    $submitted_text = _x('%s submitted.', 'placeholder is singular form', 'tcc-theme-options');
    $schedule_text  = _x('%1$s publication scheduled for %2$s', '1: label in singular form, 2: formatted date string','tcc-theme-options');
    $draft_text     = _x('%s draft updated.');
    $view_link = $preview_link = $formed_date = '';
    if ($post=get_post()) {
      $view_text      = sprintf(_x('View %s',    'placeholder is singular form', 'tcc-theme-options'),$this->label);
      $preview_text   = sprintf(_x('Preview $s', 'placeholder is singular form', 'tcc-theme-options'),$this->label);
      $link_tag_html  = '  <a href="%s" target="'.sanitize_title($post->title).'">';
      $view_link      = sprintf( $link_tag_html, esc_url( get_permalink($post->ID))) .$view_text.'</a>';
      $preview_link   = sprintf( $link_tag_html, esc_url( add_query_arg('preview', 'true', get_permalink($post->ID)))) .$preview_text.'</a>';
      $formed_date    = date_i18n( get_option('date_format'), strtotime($post->post_date));
    }
    $messages[$this->type] = array( 0 => '', // Unused. Messages start at index 1.
      1  => sprintf( $update_text,    $this->label) .$view_link,
      2  => __( 'Custom field updated.', 'tcc-theme-options' ),
      3  => __( 'Custom field deleted.', 'tcc-theme-options' ),
      4  => sprintf( $update_text,    $this->label),
      5  => isset($_GET['revision']) ? sprintf( $revision_text, $this->label, wp_post_revision_title((int)$_GET['revision'],false)) : false,
      6  => sprintf( $published_text, $this->label) .$view_link,
      7  => sprintf( _x('%s saved.','placeholder is singular form','tcc-theme-options'), $this->label),
      8  => sprintf( $submitted_text, $this->label) .$preview_link,
      9  => sprintf( $schedule_text,  $this->label, $formed_date) .$preview_link,
      10 => sprintf( $draft_text,     $this->label) .$preview_link);
    return $messages;
  }

  # http://stackoverflow.com/questions/18324883/wordpress-custom-post-type-capabilities-admin-cant-edit-post-type
  public function add_caps() {
    $role = get_role('administrator');
    $sing = sanitize_title($this->label);
    $plur = sanitize_title($this->plural);
    $caps = array("edit_$sing","read_$sing","delete_$sing","edit_$plur","edit_others_$plur","publish_$plur",
                  "read_private_$plur","delete_$plur","delete_private_$plur","delete_published_$plur",
                  "delete_others_$plur","edit_private_$plur","edit_published_$plur","edit_$plur");
    foreach($caps as $cap) { $role->add_cap($cap); }
    $this->edit_other = "edit_others_$plur";
  }


  /* Taxonomy functions */

  protected function taxonomy_labels($single,$plural) {
    $phrases = self::translated_text();
    return array('name'              => $plural,
                 'singular_name'     => $single,
                 'search_items'      => sprintf($phrases['search'],$plural),
                 'all_items'         => sprintf($phrases['all'],   $plural),
                 'edit_item'         => sprintf($phrases['edit'],  $single),
                 'update_item'       => sprintf($phrases['update'],$single),
                 'add_new_item'      => sprintf($phrases['add'],   $single),
                 'new_item_name'     => sprintf($phrases['new'],   $single),
                 'menu_name'         => $plural);
  }

  protected function register_taxonomy($args) {
    $defs = array('tax'=>'fix-me','single'=>'One Fix Me','plural'=>'Many Fix Mes','admin'=>false,'submenu'=>false,'nodelete'=>false);
    $args = wp_parse_args($args,$defs);
    extract($args);
    if (!isset($rewrite)) { $rewrite = $tax; }
    $taxi = array('hierarchical' => false,
                  'labels'       => $this->taxonomy_labels($single,$plural),
                  'rewrite'      => array('slug'=>$rewrite),
                  'show_admin_column'=>$admin);
    register_taxonomy($tax,$this->type,$taxi);
    if (taxonomy_exists($tax)) {
      if (!in_array($tax,$this->tax_list)) { $this->tax_list[] = $tax; }
      register_taxonomy_for_object_type($tax,$this->type);
      $func = "default_$tax";
      if ($func) {
        $current = get_terms($tax,'hide_empty=0');
        if (empty($current)) {
          $defs = $this->$func();
          foreach($defs as $each) {
            wp_insert_term($each,$tax); }
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
          $keep_list[] = get_term_by('name',$term,$screen->taxonomy)->term_id;
        }
      }
      $term_list = get_terms($screen->taxonomy,'hide_empty=1');
      if ($term_list) {
        foreach($term_list as $term) {
          $keep_list[] = $term->term_id; }
     }
     if ($keep_list) {
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

  public function get_tax_list() {
    return $this->tax_list;
  }


  /* Post Column functions */

  private function setup_columns() {
    if (isset($this->columns['remove'])) {
      add_filter("manage_edit-{$type}_columns",array($this,'remove_custom_post_columns')); }
    if (isset($data['columns']['add'])) {
      add_filter("manage_edit-{$type}_columns",array($this,'add_custom_post_columns'));
      add_filter("manage_edit-{$type}_sortable_columns",array($this,'add_custom_post_columns'));
      if (isset($data['columns']['content'])) {
        if (is_callable(array($this,$this->columns['content']))) {
          add_action('manage_posts_custom_column',array($this,$this->columns['content']),10,2);
        } else { tcc_log_entry('columns[content] not callable',$this); }
      }
    }
  }

  public function add_custom_post_columns($columns) {
    foreach($this->columns['add'] as $key=>$col) {
      if (!isset($columns[$key])) $columns[$key] = $col; }
    return $columns;
  } //*/

  // http://codex.wordpress.org/Function_Reference/locate_template
  // https://wordpress.org/support/topic/stylesheetpath-in-plugin
  public function assign_template($template) {
    $post_id = get_the_ID();
    if ($post_id) {
      $mytype = get_post_type($post_id);
      if ($mytype && ($this->type==$mytype)) {
        if ((is_single()) && (isset($this->template['single']))) {
          $name  = basename($this->template['single']);
          $maybe = locate_template(array($name));
          $template = ($maybe) ? $maybe : $this->template['single'];
        }
      }
    }
    do_action('tcc_assign_template_'.$this->type);
    return $template;
  } //*/

/*
  public function comments_limit($open,$post_id) {
    $mytype = get_post_type($post_id);
    if ($this->type==$mytype) {
      if (is_singular($mytype)) {
        if ((isset($this->comments)) && ($this->comments)) {
          if (is_bool($this->comments)) {
            $open = $this->comments;
          } else {
#            $postime = get_the_time('U', $post_id);
             tcc_log_entry('WARNING: Numeric values for custom_post_type->comments is not yet supported.');
          }
        }
      }
    }
    return $open;
  } //*/

  public function custom_post_sidebars($sidebars) {
    $defaults = array('before_widget' => '<div class="panel panel-primary">', // bootstrap css classes
                      'before_title'  => '<div class="panel-heading"><h3 class="panel-title">',
                      'after_title'   => '</h3></div><div class="panel-body">',
                      'after_widget'  => '</div></div>');
    foreach($this->sidebars as $sidebar) {
      if (empty($sidebar['id']) || empty($sidebar['name'])) continue;
      $add_sidebar = array_merge($defaults,$sidebar);
      $sidebars[]  = $add_sidebar;
    }
    return $sidebars;
  } //*/


  // https://wordpress.org/support/topic/custom-post-type-posts-not-displayed
  public function pre_get_posts($query) {
    if (!is_admin()) {
      if ($query->is_main_query()) {
        if ((!$query->is_page()) || (is_feed())) {
          $check = $query->get('post_type');
          if (empty($check)) {
            $query->set('post_type',array('post',$this->type));
          } else if (!((array)$check==$check)) {
            if ($check!==$this->type) $query->set('post_type',array($check,$this->type));
          } else if (!in_array($this->type,$check)) {
            $check[] = $this->type;
            $query->set('post_type',$check);
          }
        }
      }
    }
    return $query;
  }

  public function remove_custom_post_columns($columns) {
    foreach($this->columns['remove'] as $no_col) {
      if (isset($columns[$no_col])) { unset($columns[$no_col]); } }
    return $columns;
  } //*/

  protected function add_meta_boxes() {
    if (!current_user_can($this->edit_other)) { 
      remove_meta_box('authordiv',$this->type,'normal');
    }
  }

}

?>
