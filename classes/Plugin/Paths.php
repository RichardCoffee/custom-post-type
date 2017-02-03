<?php

class TCC_Plugin_Paths {

	private $dir;
	private $templates = '/template-parts/';
	private $url;
	private $version;

	use TCC_Trait_Magic;
	use TCC_Trait_Singleton;

	public function __construct( $args ) {
		foreach ( $args as $key => $arg ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $arg; }
		}
	}

	public static function url( $file = '' ) {
		return self::$instance->url . $file;
	}

	public static function version() {
		return self::$instance->version;
	}


	/**  Template functions  **/

	public function add_plugin_template( $slug, $text ) {
		require_once( $this->dir . 'classes/pagetemplater.php' );
		$pager = PageTemplater::get_instance();
		$pager->add_project_template( $slug, $text, $this->dir );
	}

	public function get_plugin_template( $slug, $return = false ) {
		$found = '';
		$stylesheet = get_stylesheet_directory();
		$template   = get_template_directory();
		$search = array( $stylesheet.$this->templates, $stylesheet,
		                 $template . $this->templates, $template,
		                 $this->dir. $this->templates);
		foreach( $search as $path ) {
			if ( file_exists( "$path/$slug.php" ) ) {
				$found = "$path/$slug.php";
				break;
			}
		}
		if ($found) {
			if ($return) {
				return $found;
			} else {
				include($found);
			}
		}
	}
}
