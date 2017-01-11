<?php
/*
Plugin Name: TCC Plugin for Custom Post Types
Plugin URI: the-creative-collective.com
Description: basic plugin for themes from The Creative Collective. Warning:  not intended for use with other themes
Version: 2.0.0
Author: Richard Coffee, The Creative Collective
Author URI: the-creative-collective.com
Text Domain: tcc-plugin
Domain Path: /locales
License: GPLv2
*/

defined('ABSPATH') || exit;

define('TCC_PLUGIN_FILE', __FILE__ );
define('TCC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
$data = get_file_data( __FILE__, array( 'ver' => 'Version' ) );
define('TCC_PLUGIN_VERSION',$data['ver']);

function tcc_plugin_class_loader( $class ) {
   if (substr($class,0,4)==='TCC_') {
     $load = str_replace( '_', '/', substr( $class, (strpos($class,'_')+1) ) );
     $file = TCC_PLUGIN_DIR."/classes/{$load}.php";
     if ( is_readable( $file ) ) {
       include $file;
     }
   }
}
spl_autoload_register( 'tcc_plugin_class_loader' );

if (!function_exists('tcc_options_check')) {
  function tcc_options_check() { // FIXME: what exactly is being acomplished here?  Is there another way to do this?
    $state = 'Stand Alone';
    if (!function_exists('is_plugin_active')) { include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }
    if (is_plugin_active('tcc-theme-options/tcc-theme-options.php'))         { $state = 'Plugin External'; }
    if (file_exists(get_template_directory().'/classes/admin-form.php'))     { $state = 'Theme Internal'; }
    return $state;
  }
}

function tcc_plugin_state_check() {
  $state  = tcc_options_check();
  $plugin = TCC_Plugin_Basic::getInstance();
#  if ($state==='Plugin External') {
#    add_action('tcc_theme_options_loaded', array($plugin,'initialize'));
#  } else {
    add_action('plugins_loaded', array($plugin,'initialize'), 100);  #  run late - priority 100
#  }
}

tcc_plugin_state_check();

register_activation_hook(TCC_PLUGIN_FILE, array('TCC_Register_Plugin','activate'));
