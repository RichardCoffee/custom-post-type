<?php
/**
 * classes/Plugin/Base.php
 *
 */
defined( 'ABSPATH' ) || exit;
/**
 * Main plugin class
 *
 * @since 20180404
 */
class TCC_Plugin_Base extends TCC_Plugin_Plugin {


#	 * @since 20200201
	use TCC_Trait_Singleton;


#	 * @since 20180404
	public function initialize() {
		if ( ( ! TCC_Register_Register::php_version_check() ) || ( ! TCC_Register_Register::wp_version_check() ) ) {
			return;
		}
		register_deactivation_hook( $this->paths->file, [ 'TCC_Register_Register', 'deactivate' ] );
		register_uninstall_hook(    $this->paths->file, [ 'TCC_Register_Register', 'uninstall'  ] );
		$this->add_actions();
		$this->add_filters();
	}

#	 * @since 20180404
	public function add_actions() {
		parent::add_actions();
	}

#	 * @since 20180404
	public function add_filters() {
		parent::add_filters();
	}


}
