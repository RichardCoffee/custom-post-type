<?php

abstract class TCC_Plugin_Basic {

	use TCC_Trait_Magic;

  protected $admin   = null;
  public    $dbvers  = '0';
  public    $paths;  #  TCC_Plugin_Paths object
  public    $plugin  = '';
  protected $tab     = 'about';
  public    $version = '1.0.0';

  protected function __construct($args=array()) {
    foreach($args as $key=>$value) {
      if (property_exists($this,$key))
        $this->$key = $value;
    }
    $this->paths = TCC_Plugin_Paths::get_instance($args);
  }

  public function add_actions() { }

  public function add_filters() {
    add_filter('plugin_action_links',array($this,'settings_link'),10,2);
  } //*/


  /**  General functions  **/

  abstract public function enqueue_scripts();


  /**  Template functions **/

  public function get_stylesheet( $file = 'tcc-plugin.css' ) {
    if ( file_exists( get_stylesheet_directory() . '/' . $file ) )
      $stylesheet = get_stylesheet_directory_uri() . '/' . $file;
    elseif ( file_exists( get_template_directory() . '/' . $file ) )
      $stylesheet = get_template_directory_uri() . '/' . $file;
    else
      $stylesheet = plugins_url( $file, dirname( __FILE__ ) );
    return $stylesheet;
  }


  // http://code.tutsplus.com/tutorials/integrating-with-wordpress-ui-the-basics--wp-26713
  /*
   *  Removes 'Edit' option from plugin page
   *  Adds 'Settings' option to plugin page
   */
  public function settings_link($links,$file) {
    if (strpos($file,$this->plugin)>-1) {
      unset($links['edit']);
      if (is_plugin_active($file)) { // FIXME:  will this ever get run if the plugin is not active?  if not, why do we need this check?
        $link = array('settings' => sprintf('<a href="%s"> %s </a>',admin_url("admin.php?page=fluidity_options&tab={$this->tab}"),__('Settings','tcc-plugin')));
        $links = array_merge($link,$links);
      }
    }
    return $links;
  }


  /** Update functions **/

  public function check_update() {
    $addr = 'tcc_option_'.$this->tab;
    $data = get_option($addr);
    if (!isset($data['dbvers'])) return;
    if (intval($data['dbvers'],10)>=intval($this->dbvers)) return;
    $this->perform_update($addr);
  }

/*  public static function redirect_about() {
    if ( !current_user_can('manage_options')) return;
    if ( !get_transient('show_tcc_about_page')) return;
    delete_transient('show_tcc_about_page');
    wp_safe_redirect(admin_url('admin.php?page=fluidity_options&tab=theme'));
    exit;
  } //*/

  private function perform_update($addr) {
    $option = get_option($addr);
    $dbvers = intval($option['dbvers'],10);
    $target = intval($this->dbvers,10);
    while($dbvers<$target) {
      $dbvers++;
      $update_func = "update_$dbvers";
      if (method_exists(get_called_class(),$update_func)) $this->$update_func();
    }
    $option = get_option($addr); // reload in case an update changes an array value
    $option['dbvers']  = $dbvers;
    $option['version'] = $this->version;
    update_option($addr,$option);
  } //*/

/*  protected static function update_options_array($option,$items=array()) {
    $section  = "tcc_options_$option";
    $defaults = TCC_Theme_Options_Values::options_defaults($option);
    $current  = get_option($section);
    if (empty($items)) {
      $current = array_merge($defaults,$current); // FIXME: assumes array is one-dimensional
    } else {
      foreach($items as $item) {
        $current[$item] = $defaults[$item];
      }
    }
    update_option($section,$current);
  } //*/


}
