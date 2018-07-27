<?php
/**
 * classes/Plugin/Base.php
 *
 */
/**
 * Main plugin class
 *
 */
class TCC_Plugin_Base extends TCC_Plugin_Plugin {

#	use TCC_Trait_Singleton;

	public function initialize() {
		if ( ( ! TCC_Register_Register::php_version_check() ) || ( ! TCC_Register_Register::wp_version_check() ) ) {
			return;
		}
		register_deactivation_hook( $this->paths->file, [ 'TCC_Register_Register', 'deactivate' ] );
		register_uninstall_hook(    $this->paths->file, [ 'TCC_Register_Register', 'uninstall'  ] );
		$this->add_actions();
		$this->add_filters();
	}

	public function add_actions() {
		parent::add_actions();
	}

	public function add_filters() {
		parent::add_filters();
	}

}
