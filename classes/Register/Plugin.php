<?php
/**
 *  Handles activation and deactivation tasks for the plugin.
 *
 * @package Plugin
 * @subpackage Register
 * @since 20200204
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2020, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Register/Plugin.php
 */
defined( 'ABSPATH' ) || exit;


class TCC_Register_Plugin extends TCC_Register_Register {


	/**
	 * @since 20200204
	 * @var string  Option slug.
	 */
	public static $option = 'plugin_option_slug';
	/**
	 * @since 20200406
	 * @var string  Plugin class name.
	 */
	protected static $register = 'TCC_Register_Plugin';
	/**
	 * @since 20200406
	 * @var string  Plugin name.
	 */
	protected static $title = 'Plugin Title';
	/**
	 * @since 20200204
	 * @var array  Version information.
	 */
	private static $versions = array();

	/**
	 *  Runs plugin activation tasks.
	 *
	 * @since 20200204
	 */
	protected static function activate_tasks() {
		self::$title = self::get_required_version( 'Name' );
		//  Example setup tasks
		self::initialize_options();
		self::remove_update_transients();
		self::theme_dependency();
	}

	/**
	 *  Some previous plugin activity examples.
	 *
	 * @since 20200204
	 */
	private static function initialize_options() { }
	private static function remove_update_transients() { }
	protected static function theme_dependency() { }

	/**
	 *  Returns the PHP version required by the plugin.
	 *
	 * @since 20200204
	 * @return string  Required PHP version.
	 */
	protected static function php_version_required() {
		$php = self::get_required_version( 'PHP' );
		return ( $php ) ? $php : parent::php_version_required();
	}

	/**
	 *  Returns WP version required.
	 *
	 * @since 20200204
	 * @return string  Required wp version.
	 */
	protected static function wp_version_required() {
		$wp = self::get_required_version( 'WP' );
		return ( $wp ) ? $wp : parent::wp_version_required();
	}

	/**
	 *  Loads the version array, and returns info from it.
	 *
	 * @since 20200204
	 * @param string   Which info is requested.
	 * @return string  The information requested.
	 */
	private static function get_required_version( $request ) {
		if ( empty( self::$versions ) ) {
			$info = TCC_Plugin_Paths::instance();
			static::$title = __( 'Plugin Name', 'tcc-plugin' );
			$need = array(
				'Name' => 'Plugin Name',
				'PHP'  => 'Required PHP',
				'WP'   => 'Requires at least',
			);
			self::$versions = get_file_data( $info->file, $need );
		}
		if ( array_key_exists( $request, self::$versions ) ) {
			return self::$versions[ $request ];
		}
		return false;
	}


}
