<?php

defined( 'ABSPATH' ) || exit;

class TCC_Register_Register {

	private static $dep_func  = 'log_entry';  // FIXME:  not a good value to check for theme dependency on.
	private static $our_site  = "<a href='the-creative-collective.com' target='tcc'>%s</a>";
	private static $rc_email  = "<a href='mailto:richard.coffee@gmail.com'>%s</a>";
	private static $jg_email  = "<a href='mailto:cableman371@gmail.com'>%s</a>";
	private static function our_email() { return ( ( mt_rand( 1, 10 ) > 5 ) ? self::$rc_email : self::$jg_email ); }

	public static function activate() {
		$return = false;
		if ( current_user_can( 'activate_plugins' ) ) {
			$return = true;
		}
#		self::theme_dependency();
		return $return;
	}

	protected static function theme_dependency() {
		#	not a good way to do this
		if ( ! function_exists( self::$dep_func ) ) {
			$error_text = self::dependency_string();
			trigger_error( $error_text, E_USER_ERROR );
		}
	}

	public static function check_dependency() {
		if ( current_user_can( 'manage_options' ) ) {
			if ( ! function_exists( self::$dep_func ) ) {
				require_once( ABSPATH . 'wp-admin/include/plugin.php' );
				deactivate_plugins( TCC_BASE ); // FIXME:  get correct plugin name
				$error_text = dependency_string();
				trigger_error( $error_text, E_USER_ERROR );
			} //*/
		}
	}

	private static function dependency_string() {
		$site_name = _x( 'The Creative Collective', 'noun - plugin site name', 'tcc-fluid' );
		$comp_name = _x( 'The Creative Collective', 'noun - plugin company name', 'tcc-fluid');
		$string    = _x( 'This plugin should only be used with %1$s themes by %2$s', 'nouns - 1 is the company, 2 is the website', 'tcc-fluid' );
		$site      = sprintf( self::$our_site, $site_name );
		$company   = sprintf( self::our_email(), $comp_name );
		return sprintf( $string, $site, $company );
	}

	public static function deactivate() {
		if ( current_user_can( 'activate_plugins' ) ) {
			self::delete_options( 'deactive' );
			flush_rewrite_rules();
		}
	}

	public static function uninstall() {
		if ( current_user_can( 'activate_plugins' ) ) {
			self::delete_options( 'uninstall' );
		}
	}

	// FIXME:  this function needs to be rewritten
	protected static function delete_options( $action, $section = 'about' ) {
		$options = get_option( "tcc_options_$section" );
		if ( $options && ( $options[ $action ] !== 'no' ) ) {
#			$menu = TCC_Theme_Options_Values::options_menu_array();
#			foreach( $menu as $key => $data ) {
#				self::delete_option( "tcc_options_$key" );
#			}
		}
	}

	/*  FIXME: wtf? this doesn't go here, put it in TCC_Plugin_Basic
	protected static function create_new_page( $new ) {
		$page = get_page_by_title( $new['post_title'] ); // FIXME: should get page by slug instead
		if ( $page ) {
			$class = get_class( $page );
			foreach( $new as $key => $value ) {
				if ( property_exists( $class, $key ) ) {
					$page->$key = $value;
				}
			}
			wp_update_post( $page );
		} else {
			wp_insert_post( $new );
		}
	} //*/

}  #  End of class TCC_Register_Plugin