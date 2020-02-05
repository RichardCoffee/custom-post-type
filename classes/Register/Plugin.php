<?php

class TCC_Register_Plugin extends TCC_Register_Register {

	public    static $option      = 'plugin';
	private   static $versions    =  array();
	protected static $plugin_file = 'tcc-plugin/tcc-plugin.php';

	protected static function activate_tasks() {
		//  Example setup tasks
		self::initialize_options();
		self::remove_update_transients();
	}

	private static function initialize_options() {
	}

	protected static function php_version_required() {
		$php = self::get_required_version( 'PHP' );
		return ( $php ) ? $php : parent::php_version_required();
	}

	protected static function wp_version_required() {
		$wp = self::get_required_version( 'WP' );
		return ( $wp ) ? $wp : parent::wp_version_required();
	}

	private static function get_required_version( $request ) {
		if ( empty( self::$versions ) ) {
			static::$title = __( 'Plugin Name', 'tcc-plugin' );
			$file = WP_PLUGIN_DIR . '/' . self::$plugin_file;
			$need = array(
				'PHP' => 'Required PHP',
				'WP'  => 'Requires at least',
			);
			self::$versions = get_file_data( $file, $need );
		}
		if ( array_key_exists( $request, self::$versions ) ) {
			return self::$versions[ $request ];
		}
		return false;
	}

	private static function remove_update_transients() { }

	#	No theme dependencies
	protected static function theme_dependency() { }

}
