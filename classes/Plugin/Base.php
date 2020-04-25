<?php
/**
 *  Main plugin class file.
 *
 * @package Plugin
 * @subpackage Core
 * @since 20170111
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2017, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Plugin/Base.php
 */
defined( 'ABSPATH' ) || exit;
/**
 * @since 20180404
 */
class TCC_Plugin_Base extends TCC_Plugin_Plugin {


	/**
	 * @since 20200201
	 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Trait/Singleton.php
	 */
	use TCC_Trait_Singleton;


	/**
	 *  Initialize the plugin.
	 *
	 * @since 20180404
	 */
	public function initialize() {
		if ( ( ! TCC_Register_Plugin::php_version_check() ) || ( ! TCC_Register_Plugin::wp_version_check() ) ) {
			return;
		}
		register_deactivation_hook( $this->paths->file, [ 'TCC_Register_Plugin', 'deactivate' ] );
		register_uninstall_hook(    $this->paths->file, [ 'TCC_Register_Plugin', 'uninstall'  ] );
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 *  Establish actions.
	 *
	 * @since 20180404
	 */
	public function add_actions() {
		parent::add_actions();
	}

	/**
	 *  Establish filters.
	 *
	 * @since 20180404
	 */
	public function add_filters() {
		parent::add_filters();
	}


}
