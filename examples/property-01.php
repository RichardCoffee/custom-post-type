<?php

require_once('custom-post.php');

class Real_Estate_Property extends RC_Custom_Post_Type {

  private $error = array();
  private $layout;
  private static $instance = null;

  protected $type = 'property';
 #protected $debug         = true;
  protected $logging       = 'tcc_log_entry';
 #protected $main_blog     = true;
  protected $menu_icon     = 'dashicons-admin-home';
  protected $menu_position = 6;
  protected $slug_edit     = false;
  protected $taxonomies    = array();
  protected $user_col      = true;

  public function __construct() {
    $data = array('label'      => _x('Property','single plot of land','tcc-real_estate'),
                  'plural'     => _x('Properties','multiple plots of land','tcc-real-estate'),
                  'descrip'    => __('Real Estate Property','tcc-real-estate'),
                  'tax_keep'   => array('prop_city'=>$this->default_prop_city(),'prop_state'=>$this->default_prop_state(),'prop_area'=>$this->default_prop_area()),
                  'sidebar'    => array('name'=>__('Property Sidebar','tcc-real-estate'),'id'=>'property'),
                  'templates'  => array('single'=> plugin_dir_path(__FILE__)."../template-parts/single-property.php"));
    parent::__construct($data);
    $this->caps = '';  #  allow this class to determine capability_types for custom roles
    add_action('admin_enqueue_scripts',       array($this,'admin_enqueue_scripts'));
    add_action('tcc_custom_post_'.$this->type,array($this,'create_taxonomies'));
    add_action('add_meta_boxes_'.$this->type, array($this,'add_meta_boxes'));
    add_action('save_post_'.$this->type,      array($this,'save_meta_boxes'));
    add_filter('wp_dropdown_users',           array($this,'agent_dropdown_listing'));
    self::$instance = $this;
  }

  public static function get_instance() {
    if (self::$instance==null) {
      self::$instance = new Real_Estate_Property;
    }
    return self::$instance;
  }

  public function admin_enqueue_scripts() {
    $screen = get_current_screen();
    if ($screen && ($screen->post_type==$this->type)) {
      wp_register_style('listing',plugin_dir_url(__FILE__)."../css/admin-listing.css");
      wp_enqueue_style('tcc-columns');
      wp_enqueue_style('listing');
      wp_enqueue_style('tcc-fawe');
      wp_register_script('listing',plugin_dir_url(__FILE__).'../js/admin.js', array('jquery','tcc-library'), null, true);
      wp_enqueue_media();
      wp_enqueue_script('listing');
    }
  }

  /**  Property Meta Boxes  **/

  public function add_meta_boxes() {
    add_meta_box('prop_financ_meta', __('Property Financials','tcc-real-estate'),array($this,'meta_box_finances'), 'property','normal','high');
    add_meta_box('prop_images_meta', __('Property Images',    'tcc-real-estate'),array($this,'meta_box_images'),   'property','normal','high');
    add_meta_box('prop_address_meta',__('Property Location',  'tcc-real-estate'),array($this,'meta_box_address'),  'property','normal','high');
    add_meta_box('prop_details_meta',__('Property Details',   'tcc-real-estate'),array($this,'meta_box_details'),  'property','normal','high');
    add_meta_box('prop_type_meta',   __('Property Type',      'tcc-real-estate'),array($this,'meta_box_types'),    'property','side',  'high');
    add_meta_box('prop_amens_meta',  __('Amenities',          'tcc-real-estate'),array($this,'meta_box_amenities'),'property','side',  'low');
    add_meta_box('prop_close_meta',  __("What's Close",       'tcc-real-estate'),array($this,'meta_box_closest'),  'property','side',  'low');
    remove_meta_box('tagsdiv-prop_action','property','side');
    remove_meta_box('tagsdiv-prop_categ', 'property','side');
    remove_meta_box('tagsdiv-prop_status','property','side');
    remove_meta_box('tagsdiv-prop_list',  'property','side');
    remove_meta_box('tagsdiv-prop_city',  'property','side');
    remove_meta_box('tagsdiv-prop_state', 'property','side');
    remove_meta_box('tagsdiv-prop_area',  'property','side');
    remove_meta_box('tagsdiv-prop_close', 'property','side');
    remove_meta_box('mymetabox_revslider_0','property','normal');
  }

  private function property_meta_box($post,$var,$click=false) {
    $prop_value = get_post_meta($post->ID,"prop_$var",true);
    $prop_array = real_estate_taxonomy("tax=prop_$var&search=0&orderby=name&order=ASC");
    $html = "<select id='prop_$var' name='prop_$var'";
    $html.= ($click) ? " onchange='$click'" : "";
    $html.= ">";
    foreach($prop_array as $key=>$option) {
      $select = ($key===$prop_value) ? 'selected' : '';
      $html  .= "<option value='$key' $select> $option </option>";
    }
    $html .= "</select>";
    echo $html;
  }

  public function meta_box_finances($post) {
    $layout = $this->layout['left']; ?>
    <table class='form_table'>
      <tbody>
        <tr><?php
          $this->show_prop_item($post,'prop_price',$layout['descrip']['prop_price']); ?>
          <td class='spacer'></td><?php
          $this->show_prop_item($post,'prop_term', $layout['descrip']['prop_term']); ?>
        </tr>
        <tr><?php
          $this->show_prop_item($post,'prop_owner',$layout['financial']['prop_owner']); ?>
          <td class='spacer'></td><?php
          $this->show_prop_item($post,'prop_finan',$layout['financial']['prop_finan']); ?>
        </tr>
        <tr><?php
          $this->show_prop_item($post,'prop_charg',$layout['financial']['prop_charg']); ?>
          <td class='spacer'></td><?php
          $this->show_prop_item($post,'prop_taxes',$layout['financial']['prop_taxes']); ?>
        </tr>
      </tbody>
    </table><?php
  }

  public function meta_box_images($post) { ?>
    <div id="listing-images" class="section group"><?php
    $images = get_listing_images($post->ID,true);
    foreach($images as $ID=>$src) { ?>
      <div class="col span_1_of_6 meta-image">
        <img class="attachment-post-thumbnail" src="<?php echo $src; ?>" data-id="<?php echo $ID ?>">
        <span class="delete-image"><i class="fa fa-minus-square fa-2x"></i></span>
      </div><?php
    } ?>
    </div>
    <button id='addListingImage'>Assign/Upload Image</button><?php
  }

  public function meta_box_address($post) {
    $locate  = $this->layout['left']['locate'];
    $fields  = array('city','state','area');
    $add_new = tcc_option('address','estate'); ?>
    <div class='section group'>
      <div class='col span_1_of_2'>
        <label class='screen-reader-text' for='prop_add1'><?php echo $locate['prop_add1']['label']; ?></label>
        <input id='prop_add1' type='text' class='textwide' name='prop_add1'
          value='<?php echo get_post_meta($post->ID,"prop_add1",true); ?>'
          placeholder='<?php echo $locate['prop_add1']['label']; ?>' />
      </div>
    </div><?php
    $add_text = array('area'  => __('Add New Area', 'tcc-real-estate'),
                      'city'  => __('Add New City', 'tcc-real-estate'),
                      'state' => __('Add New State','tcc-real-estate'));
    foreach($fields as $field) {
      if (($field=='area') && (tcc_option('area','estate')!=='yes')) continue; ?>
      <div class='section group'>
        <div class='col span_1_of_4'>
          <label class='screen-reader-text' for='prop_<?php echo $field; ?>'><?php echo $locate["prop_$field"]['label']; ?></label><?php
          $this->property_meta_box($post,$field,'showEditField(this);'); ?>
        </div><?php
        if ($add_new=='yes') { ?>
          <div class='col span_1_of_4'>
            <div id='meta_add_<?php echo $field; ?>' class='hidden'>
              <label class='screen-reader-text' for='add_<?php echo $field; ?>'><?php echo $add_text[$field]; ?></label>
              <input id='add_<?php echo $field; ?>' type='text' class='textwide' name='add_<?php echo $field; ?>'
                autocomplete='off' placeholder='<?php echo $add_text[$field]; ?>' />
            </div>
          </div><?php
        } ?>
      </div><?php
    } ?>
    <div class='section group'>
      <div class='col span_1_of_4'>
        <label class='screen-reader-text' for='prop_zip'><?php echo $locate['prop_zip']['label']; ?></label>
        <input id='prop_zip' type='text' class='textwide' name='prop_zip'
          value='<?php echo get_post_meta($post->ID,"prop_zip",true); ?>'
          placeholder='<?php echo $locate['prop_zip']['label']; ?>' />
      </div>
      <div class='col span_1_of_4'>
        <label class='screen-reader-text' for='prop_county'><?php echo $locate['prop_county']['label']; ?></label>
        <input id='prop_county' type='text' class='textwide' name='prop_county'
          value='<?php echo get_post_meta($post->ID,"prop_county",true); ?>'
          placeholder='<?php echo $locate['prop_county']['label']; ?>' />
      </div>
    </div><?php
  }

  public function meta_box_details($post) {
    $layout = $this->layout['left']['details'];
    $side   = true; ?>
    <table class='form_table'>
      <tbody><?php
        foreach($layout as $key=>$detail) {
          if (!is_array($detail)) continue;
          if ($side) {
            echo "<tr>";
            $this->show_prop_item($post,$key,$layout[$key]);
            echo "<td class='spacer'></td>";
          } else {
            $this->show_prop_item($post,$key, $layout[$key]);
            echo "</tr>";
          }
          $side = !$side;
        } ?>
      </tbody>
    </table><?php
  }

  public function meta_box_types($post) {
    wp_nonce_field(basename(__FILE__),'property_nonce');
    $this->show_prop_type_field($post,__('Listing Type',   'tcc-real-estate'),'action');
    $this->show_prop_type_field($post,__('Property Type',  'tcc-real-estate'),'categ');
    $this->show_prop_type_field($post,__('Property Status','tcc-real-estate'),'status');
  }

  public function meta_box_amenities($post) {
    $this->meta_box_checkboxes($post,'prop_list');
  }

  public function meta_box_closest($post) {
    $this->meta_box_checkboxes($post,'prop_close');
  }

  private function meta_box_checkboxes($post,$tax) {
    $data = $this->layout['right'][$tax];
    #log_entry('amenities',$amens);
    $terms = reformat_taxonomy_terms($post->ID,$tax);
    #log_entry('post amenities',$terms);
    foreach($data as $key=>$item) {
      if ($key=='title') continue;
      $mine  = (in_array($key,(array)$terms['slugs'])) ? " checked" : "";
      echo "<p><label><input type='checkbox' name='{$tax}[]' value='$key'$mine/> {$item['label']} </label></p>";
    }
  }

  private function show_prop_item($post,$name,$layout) {
    $value = get_post_meta($post->ID,$name,true); ?>
    <th><?php
      echo "<label for='$name'>{$layout['label']} </label>"; ?>
    </th>
    <td><?php
      #echo "<input type='{$layout['type']}' id='$name' name='$name' placeholder='{$layout['label']}' value='$value'>";
      echo "<input type='text' id='$name' name='$name' placeholder='{$layout['label']}' value='$value'>"; ?>
    </td><?php
  }

  private function show_prop_type_field($post,$title,$var) { ?>
    <div class='section group'>
      <div class='col span_1_of_2'><?php echo $title; ?></div>
      <div class='col span_1_of_2'><?php
        $this->property_meta_box($post,$var); ?>
      </div>
    </div><?php
  }

  // source unknown
  public function agent_dropdown_listing($output) {
    // return if this isn't the theme author override dropdown
    if (!preg_match('/post_author_override/', $output)) return $output;
    // return if we've already replaced the list (end recursion)
    if (preg_match ('/post_author_override_replaced/', $output)) return $output;
    global $post;
    // replacement call to wp_dropdown_users
    $agents = get_users(array('role'=>'agent'));
    $output = wp_dropdown_users(array('echo' => 0,
                      'name' => 'post_author_override_replaced',
                      'selected' => empty($post->ID) ? get_current_user_id() : $post->post_author,
                      'include_selected' => true));
    // put the original name back
    $output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);
    return $output;
  }

  private function load_layout() {
    $plugin = Real_Estate_Plugin::get_instance();
    require_once($plugin->path.'/includes/real_estate_setup.php');
    $this->layout = real_estate_layout();
  }

  public function save_meta_boxes($postID) {
    remove_action('save_post_'.$this->type, array($this,'save_meta_boxes')); # prevent recursion
    if (!isset($_POST['property_nonce']))       return;
    if (!current_user_can('edit_post',$postID)) return;
    if (wp_is_post_autosave($postID))           return;
    if (wp_is_post_revision($postID))           return;
    if (!wp_verify_nonce($_POST['property_nonce'],basename(__FILE__))) return;
    $verified = true;
    $in_admin = true;
    $incoming = $_POST;
    $incoming['prop_id'] = $postID;
    $plugin = Real_Estate_Plugin::get_instance();
    require($plugin->path.'/actions/save_agent_listing.php');
    if ($result['result']===0) {
      if (isset($result['index'])) {
        $this->error = $result;
        add_filter('redirect_post_location',array($this,'redirect_post_location'));
      }
      return $postID;
    }
  }


  /**  Post Handling Functions **/

  public function redirect_post_location($location) {
    remove_filter('redirect_post_location',array($this,'redirect_post_location'));
    return add_query_arg('message', $this->error['index'] , $location);
  }

  # References: http://codex.wordpress.org/Function_Reference/register_post_type
  function post_type_messages($messages) {
    $post             = get_post();
    $post_type_object = get_post_type_object($this->type);
    $revision_text = _x('Property Listing restored to revision from %s','placeholder is date and time of the revision','tcc-real-estate');
    $schedule_text = _x('Property publication scheduled for %s','placeholder is a formatted date string','tcc-real-estate');
    $formed_date   = date_i18n(get_option('date_format'),strtotime($post->post_date));
    $messages[$this->type] = array(
      0  => '', // Unused. Messages start at index 1.
      1  => __('Property Listing updated.','tcc-real-estate'),
      2  => __('Custom field updated.',    'tcc-real-estate'),
      3  => __('Custom field deleted.',    'tcc-real-estate'),
      4  => __('Property Listing updated.','tcc-real-estate'),
      5  => isset($_GET['revision']) ? sprintf($revision_text,wp_post_revision_title((int)$_GET['revision'],false)) : false,
      6  => __('Property Listing published.','tcc-real-estate'),
      7  => __('Property Listing saved.',    'tcc-real-estate'),
      8  => __('Property Listing submitted.','tcc-real-estate'),
      9  => sprintf($schedule_text,'<b>'.$formed_date.'</b>'),
      10 => __( 'Property Listing draft updated.','tcc-real-estate' )
    );
    if ( $post_type_object->publicly_queryable ) {
      $permalink = get_permalink( $post->ID );
      $view_link = sprintf( ' <a href="%s">%s</a>', esc_url($permalink), __( 'View Property', 'tcc-real-estate'));
      $messages[ $this->type ][1] .= $view_link;
      $messages[ $this->type ][6] .= $view_link;
      $messages[ $this->type ][9] .= $view_link;
      $preview_permalink = add_query_arg('preview', 'true', $permalink );
      $preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview Property', 'tcc-real-estate'));
      $messages[ $this->type ][8]  .= $preview_link;
      $messages[ $this->type ][10] .= $preview_link;
    }
    if (!function_exists('real_estate_mandatory_list')) {
      $plugin = Real_Estate_Plugin::get_instance();
      include_once($plugin->path.'/includes/real_estate_setup.php');
    }
    // FIXME:  these still need to be displayed...
    $index = 101;
    $mandatory = real_estate_mandatory_list();
    foreach($mandatory as $key=>$value) {
      $messages[$this->type][$index] = "Property Listing required field '$value' is empty.";
      $index++;
    }
    return $messages;
  }


  /**  Property Taxonomies  **/

  public function create_taxonomies() {
    $this->create_list_type_tax();
    $this->create_categ_tax();
    $this->create_prop_status_tax();
    $this->create_cities_tax();
    $this->create_states_tax();
    if (tcc_option('area','estate')=='yes') { $this->create_areas_tax(); }
    $this->create_amenities_tax();
    $this->create_closest_tax();
    if (empty($this->layout)) $this->load_layout();
  }

  private function create_cities_tax() {
    $single = _x('City',  'Taxonomy singular name for an urban population center','tcc-real-estate');
    $plural = _x('Cities','Taxonomy plural name for urban population centers', 'tcc-real-estate');
    $this->taxonomy_registration("tax=prop_city&single=$single&plural=$plural&rewrite=city&nodelete=true");
    $this->tax_keep['prop_city'] = $this->default_prop_city();
  }

  protected function default_prop_city() {
    return array(__('Add New City','tcc-real-estate'));
  }

  private function create_states_tax() {
    $single = _x('State', 'Taxonomy singular name for a geographical state/province','tcc-real-estate');
    $plural = _x('States','Taxonomy plural name for geographical states/provinces', 'tcc-real-estate');
    $this->taxonomy_registration("tax=prop_state&single=$single&plural=$plural&rewrite=state&nodelete=true");
    $this->tax_keep['prop_state'] = $this->default_prop_state();
  }

  protected function default_prop_state() {
    return array(__('Add New State','tcc-real-estate'));
  }

  private function create_areas_tax() {
    $single = _x('Area', 'Taxonomy singular name for land area/neighberhood','tcc-real-estate');
    $plural = _x('Areas','Taxonomy plural name for land areas/neighberhoods', 'tcc-real-estate');
    $this->taxonomy_registration("tax=prop_area&single=$single&plural=$plural&rewrite=area&nodelete=true");
    $this->tax_keep['prop_area'] = $this->default_prop_area();
  }

  protected function default_prop_area() {
    return array(__('Add New Area','tcc-real-estate'));
  }

  private function create_categ_tax() {
    $args = array('tax'      => 'prop_categ',
                  'single'   => _x('Property Type', 'Taxonomy singular name for a plot of land','tcc-real-estate'),
                  'plural'   => _x('Property Types','Taxonomy plural name for multiple plots of land', 'tcc-real-estate'),
                  'rewrite'  => 'type',
                  'nodelete' => true);
    $this->taxonomy_registration($args);
  }

  protected function default_prop_categ() {
    return array(__('Apartment',      'tcc-real-estate'),
                 __('Co-Op',          'tcc-real-estate'),
                 __('Condo',          'tcc-real-estate'),
                 __('Farm',           'tcc-real-estate'),
                 __('Hotel',          'tcc-real-estate'),
                 __('House',          'tcc-real-estate'),
                 __('Industrial',     'tcc-real-estate'),
                 __('Land',           'tcc-real-estate'),
                 __('Motel',          'tcc-real-estate'),
                 __('MultiDwelling Unit','tcc-real-estate'),
                 __('Office',         'tcc-real-estate'),
                 __('Open House',     'tcc-real-estate'),
                 __('Recently Sold',  'tcc-real-estate'),
                 __('Retail',         'tcc-real-estate'),
                 __('Room',           'tcc-real-estate'),
                 __('Special Purpose','tcc-real-estate'));
  }

  private function create_list_type_tax() {
    $args = array('tax'      => 'prop_action',
                  'single'   => _x('Listing Type', 'Taxonomy singular name','tcc-real-estate'),
                  'plural'   => _x('Listing Types','Taxonomy plural name', 'tcc-real-estate'),
                  'rewrite'  => 'properties',
                  'admin'    =>  true,
#                  'submenu'  => 'prop_action_submenu',
                  'nodelete' => true);
    $this->taxonomy_registration($args);
  }

/*  public function prop_action_submenu($nav_items) {
#tcc_log_entry('menu item',$nav_items);
#   $submenu = taxonomy_menu_dropdown('prop_action'); // function in base class
    return $nav_items;
  } //*/

  protected function default_prop_action() {
    return array(__('For Lease',  'tcc-real-estate'),
                 __('For Rent',   'tcc-real-estate'),
                 __('For Sale',   'tcc-real-estate'),
                 __('Commercial', 'tcc-real-estate'),
                 __('Foreclosure','tcc-real-estate'));
  }

  private function create_prop_status_tax() {
    $args = array('tax'      => 'prop_status',
                  'single'   => _x('Property Status',  'Taxonomy singular name','tcc-real-estate'),
                  'plural'   => _x('Property Statuses','Taxonomy plural name', 'tcc-real-estate'),
                  'rewrite'  => 'status',
                  'nodelete' => true);
    $this->taxonomy_registration($args);
  }

  protected function default_prop_status() {
    return array(__('normal',         'tcc-real-estate'),
                 __('For Sale',       'tcc-real-estate'),
                 __('For Rent',       'tcc-real-estate'),
                 __('For Lease',      'tcc-real-estate'),
                 __('Open House',     'tcc-real-estate'),
                 __('In Negotiations','tcc-real-estate'),
                 __('Sold',           'tcc-real-estate'));
  }

  private function create_amenities_tax() {
    $args = array('tax'      => 'prop_list',
                  'single'   => _x('Amenity',  'Taxonomy singular name','tcc-real-estate'),
                  'plural'   => _x('Amenities','Taxonomy plural name', 'tcc-real-estate'),
                  'rewrite'  => 'amenities',
                  'nodelete' => true);
    $this->taxonomy_registration($args);
  }

  protected function default_prop_list() {
    return array(__('Attic',           'tcc-real-estate'),__('Back yard',         'tcc-real-estate'),__('Balcony',         'tcc-real-estate'),
                 __('Basketball court','tcc-real-estate'),__('Conciege',          'tcc-real-estate'),__('Deck',            'tcc-real-estate'),
                 __('Doorman',         'tcc-real-estate'),__('Electric heat',     'tcc-real-estate'),__('Electric stove',  'tcc-real-estate'),
                 __('Fenced yards',    'tcc-real-estate'),__('Fireplace',         'tcc-real-estate'),__('Front yard',      'tcc-real-estate'),
                 __('Garage',          'tcc-real-estate'),__('Gas heat',          'tcc-real-estate'),__('Gas stove',         'tcc-real-estate'),
                 __('Gourmet Kitchen', 'tcc-real-estate'),__('Granite',           'tcc-real-estate'), // FIXME:  Granite what??
                 __('Gym',             'tcc-real-estate'),__('Hardwood floors',   'tcc-real-estate'),__('Lake view',       'tcc-real-estate'),
                 __('Laundry',         'tcc-real-estate'),__('Ocean view',        'tcc-real-estate'),__('Pool',            'tcc-real-estate'),
                 __('Private Entrance','tcc-real-estate'),__('Private space',     'tcc-real-estate'),__('Recreation',      'tcc-real-estate'),
                 __('Roof deck',       'tcc-real-estate'),__('Sprinklers',        'tcc-real-estate'),__('Storage',         'tcc-real-estate'),
                 __('Tennis court',    'tcc-real-estate'),__('Utilities included','tcc-real-estate'),__('Washer and Dryer','tcc-real-estate'),
                 __('Wine cellar',     'tcc-real-estate'));
  }

  private function create_closest_tax() {
    $settings = array('tax'     => 'prop_close',
                      'single'  => _x('Closest','Taxonomy singular name','tcc-real-estate'),
                      'plural'  => _x('Closest','Taxonomy plural name', 'tcc-real-estate'),
                      'admin'   =>  false,
                      'rewrite' => 'closest');
    $this->taxonomy_registration($settings);
  }

  protected function default_prop_close() {
    return array(__('Banks',               'tcc-real-estate'),
                 __('Bars/Pubs/Night Life','tcc-real-estate'),
                 __('Diners/Restaurants',  'tcc-real-estate'),
                 __('Entertainment',       'tcc-real-estate'),
                 __('Gas',                 'tcc-real-estate'),
                 __('Subway',              'tcc-real-estate'),
                 __('Parks',               'tcc-real-estate'),
                 __('Shopping',            'tcc-real-estate'),
                 __('Sports Venues',       'tcc-real-estate'),
                 __('Transportation',      'tcc-real-estate'));
  }

}
