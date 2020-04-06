<?php

class TCC_Register_Plugin extends TCC_Register_Register {

	public    static $option   = 'plugin_option_slug';
	protected static $register = 'TCC_Register_Plugin';
	protected static $title    = 'Plugin Title';
	private   static $versions =  array();

	protected static function activate_tasks() {
		self::$title = self::get_required_version( 'Name' );
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
			$info = TCC_Plugin_Paths::instance();
			static::$title = __( 'Plugin Name', 'tcc-plugin' );
			$file = trailingslashit( $info->dir ) . $info->file;
			$need = array(
				'Name' => 'Plugin Name',
				'PHP'  => 'Required PHP',
				'WP'   => 'Requires at least',
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
