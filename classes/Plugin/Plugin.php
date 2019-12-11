<?php
/**
 *   Supplies basic plugin functions
 *
 * @package Plugin
 * @subpackage Plugin_Core
 * @since 20170111
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2017, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Plugin/Plugin.php
 */
defined( 'ABSPATH' ) || exit;
/**
 *  Abstract class that contains helper functions for a plugin.
 *
 * @since 20170214
 */
abstract class TCC_Plugin_Plugin {

#	 * @since 20170111
	protected $admin = null;
#	 * @since 20170111
	public    $dbvers = '0';
#	 * @since 20170325
	protected $github = '';    #  'https://github.com/MyGithubName/my-plugin-name/';
#	 * @since 20170113
	public    $paths = null;   #  TCC_Plugin_Paths object
#	 * @since 20170111
	public    $plugin = 'plugin-slug';
#	 * @since 20170207
	protected $setting = '';    #  settings link
#	 * @since 20170207
	protected $state = '';
#	 * @since 20170111
	protected $tab = 'about';

	use TCC_Trait_Magic;
	use TCC_Trait_ParseArgs;

#	 * @since 20170214
	abstract public function initialize();

#	 * @since 20170111
	protected function __construct( $args = array() ) {
		if ( ! empty( $args['file'] ) ) {
			$data = get_file_data( $args['file'], [ 'ver' => 'Version' ] );
			$defaults = array(
				'dir'     => plugin_dir_path( $args['file'] ),
				'plugin'  => dirname( plugin_basename( $args['file'] ) ),
				'url'     => plugin_dir_url( $args['file'] ),
				'version' => $data['ver'],
			);
			$args = array_merge( $defaults, $args );
			$this->parse_args( $args );
			$this->paths = TCC_Plugin_Paths::get_instance( $args );
			$this->state = $this->state_check();
			$this->schedule_initialize();
			$this->load_textdomain();
			$this->load_update_checker();
		} else {
			static::$abort__construct = true;
		}
	}

#	 * @since 20170111
	public function add_actions() { }

#	 * @since 20170111
	public function add_filters() {
		add_filter( 'plugin_action_links', [ $this, 'settings_link' ], 10, 4 );
		add_filter( 'network_admin_plugin_action_links', [ $this, 'settings_link' ], 10, 4 );
	} //*/


	/**  General functions  **/

#	 * @since 20170207
	public function state_check() {
		$state = 'alone';
		if ( is_readable( get_template_directory() . '/classes/Form/Admin.php' ) ) {
			$state = 'theme';
		} else {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'tcc-theme-options/tcc-theme-options.php' ) ) {
				$state = 'plugin';
			}
		}
		return $state;
	}

#	 * @since 20170227
	protected function schedule_initialize() {
		switch ( $this->state ) {
			case 'plugin':
				add_action( 'tcc_theme_options_loaded', [ $this, 'initialize' ] );
				break;
			case 'alone':
			case 'theme':
			default:
				add_action( 'plugins_loaded', [ $this, 'initialize' ], 100 );
		}
	}

#	 * @since 20170325
#	 * @link https://github.com/schemapress/Schema
	private function load_textdomain() {
		$args = array(
			'text_domain' => 'Text Domain',
			'lang_dir'    => 'Domain Path',
		);
		$data = get_file_data( $this->paths->file, $args );
		if ( $data && ( ! empty( $data['text_domain'] ) ) ) {
			list( $lang_dir, $mofile_local, $mofile_global ) = $this->determine_textdomain_filenames( $data );
			if ( is_readable( $mofile_global ) ) {
				load_textdomain( $data['text_domain'], $mofile_global );
			} else if ( is_readable( $mofile_local ) ) {
				load_textdomain( $data['text_domain'], $mofile_local );
			} else {
				load_plugin_textdomain( $data['text_domain'], false, $lang_dir );
			}
		}
	}

#	 * @since 20170409
	private function determine_textdomain_filenames( $data ) {
		$lang_def = ( empty( $data['lang_dir'] ) ) ? '/languages' : $data['lang_dir'];
		#	$lang_dir
		$files[]  = untrailingslashit( $this->paths->dir ) . $lang_def;
		$locale   = apply_filters( 'plugin_locale',  get_locale(), $data['text_domain'] );
		$mofile   = sprintf( '%1$s-%2$s.mo', $data['text_domain'], $locale );
		#	$mofile_local
		$files[]  = $files[0] . '/' . $mofile;
		#	$mofile_global
		$files[]  = WP_LANG_DIR . '/plugins/' . $data['text_domain'] . '/' . $mofile;
		return $files;
	}

#	 * @since 20170111
	public function get_stylesheet( $file = 'css/tcc-plugin.css', $path = '/' ) {
		return $this->paths->get_plugin_file_path( $file );
	}

	/*
		return $this->paths->get_plugin_file_path( $file );
	}

	/*
	 *  Removes 'Edit' option from plugin page entry
	 *  Adds 'Settings' option to plugin page entry
	 *
	 * @since 20170111
	 *  sources:  http://code.tutsplus.com/tutorials/integrating-with-wordpress-ui-the-basics--wp-26713
	 *            https://hugh.blog/2012/07/27/wordpress-add-plugin-settings-link-to-plugins-page/
	 */
	public function settings_link( $links, $file, $data, $context ) {
		if ( strpos( $file, $this->plugin ) !== false ) {
			unset( $links['edit'] );
			if ( is_plugin_active( $file ) && ! ( $this->tab === 'about' ) ) {
				$url = ( $this->setting ) ? $this->setting : admin_url( 'admin.php?page=fluidity_options&tab=' . $this->tab );
				$links['settings'] = sprintf( '<a href="%s"> %s </a>', esc_url( $url ), esc_html__( 'Settings', 'tcc-plugin' ) );
			}
		}
		return $links;
	}


  /** Update functions **/


#	 * @since 20170325
	private function load_update_checker() {
		if ( $this->github ) {
			$puc_file = $this->paths->dir . $this->paths->vendor . 'plugin-update-checker/plugin-update-checker.php';
			if ( is_readable( $puc_file ) ) {
				require_once( $puc_file );
				$puc = Puc_v4_Factory::buildUpdateChecker( $this->github, $this->paths->file, $this->plugin );
			}
		}
	}

/*
#	 * @since 20170111
  public function check_update() {
    $addr = 'tcc_options_'.$this->tab;
    $data = get_option($addr);
    if ( ! array_key_exists( 'dbvers', $data ) ) return;
    if (intval($data['dbvers'],10)>=intval($this->dbvers)) return;
    $this->perform_update($addr);
  }

#	 * @since 20170111
  private function perform_update($addr) {
    $option = get_option($addr);
    $dbvers = intval($option['dbvers'],10);
    $target = intval($this->dbvers,10);
    while($dbvers<$target) {
      $dbvers++;
      $update_func = "update_$dbvers";
      if ( method_exists( get_called_class(), $update_func ) ) {
        $this->$update_func();
      }
    }
    $option = get_option($addr); // reload in case an update changes an array value
    $option['dbvers']  = $dbvers;
    $option['version'] = $this->paths->version;
    update_option($addr,$option);
  } //*/


}
