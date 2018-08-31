<?php
/**
 * Plugin Name
 *
 * @package   Custom Post Type Plugin
 * @author    Author Name <author@email>
 * @copyright 2018 Author Name
 * @license   GPLv2  <need uri here>
 * @link      link
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Post Type Plugin
 * Plugin URI:        pluginhome.com
 * Description:       Description for the plugin.
 * Version:           2.3.1
 * Requires at least: 4.7.0
 * Requires WP:       4.7.0
 * Tested up to:      4.7.4
 * Requires PHP:      5.3.6
 * Author:            Author Name
 * Author URI:        author uri
 * GitHub Plugin URI: github uri needed if using plugin-update-checker
 * License:           GPLv2
 * Text Domain:       plugin-domain
 * Domain Path:       /languages
 * Tags:              what, where, when, who, how, why
 */

defined( 'ABSPATH' ) || exit;
/*
# https://github.com/helgatheviking/Nav-Menu-Roles/blob/master/nav-menu-roles.php
if ( ! defined('ABSPATH') || ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
} //*/

define( 'TCC_PLUGIN_DIR' , plugin_dir_path( __FILE__ ) );

function tcc_plugin_class_loader( $class ) {
   if ( substr( $class, 0, 4 ) === 'TCC_' ) {
     $load = str_replace( '_', '/', substr( $class, ( strpos( $class, '_' ) + 1 ) ) );
     $file = TCC_PLUGIN_DIR . "/classes/{$load}.php";
     if ( is_readable( $file ) ) {
       include $file;
     }
   }
}
spl_autoload_register( 'tcc_plugin_class_loader' );

$plugin = TCC_Plugin_NameHere::get_instance( array( 'file' => __FILE__ ) );

register_activation_hook( __FILE__, array( 'TCC_Register_Plugin', 'activate' ) );
