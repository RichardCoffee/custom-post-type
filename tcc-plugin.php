<?php
/**
 *  Plugin Name
 *
 * @package   Custom Post Type Plugin
 * @author    Author Name <author@email>
 * @copyright 2020 Author Name
 * @license   GPLv2  <need uri here>
 * @link      link
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Post Type Plugin
 * Plugin URI:        https://example.com/plugin-name
 * Description:       Description for the plugin.
 * Version:           0.1.0
 * Requires at least: 4.7.0
 * Tested up to:      5.2.0
 * Requires PHP:      5.3.6
 * Author:            Author Name
 * Author URI:        https://example.com
 * GitHub URI:        github uri needed if using plugin-update-checker
 * Text Domain:       plugin-domain
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
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

require_once( 'functions.php' );

$plugin = TCC_Plugin_Base::get_instance( array( 'file' => __FILE__ ) );

register_activation_hook( __FILE__, array( 'TCC_Register_Plugin', 'activate' ) );
