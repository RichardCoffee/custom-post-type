<?php

class TCC_Plugin_Paths {

	private $dir;
	private $templates = '/template-parts/';
	private $url;
	private $version;

	use TCC_Trait_Magic;
	use TCC_Trait_Singleton;

	public function __construct($args) {
		foreach ($args as $key=>$arg) {
			if (property_exists($this,$key)) {
				$this->$key = $arg; }
		}
	}

	public static function url($file='') {
		return self::$instance->url . $file;
	}

	public static function version() {
		return self::$instance->version;
	}


	/**  Template functions  **/

	public function add_plugin_template($slug,$text) {
		require_once($path.'classes/pagetemplater.php');
		$pager = PageTemplater::get_instance();
		$pager->add_project_template( $slug, $text, $this->dir );
	}

	public function get_plugin_template($slug,$args=array()) {
		$found  = '';
		$return = false;
		if ($args) {
			$args = wp_parse_args($args);
			extract($args,EXTR_IF_EXISTS);
		}
		if (file_exists(get_stylesheet_directory().$this->templates."/$slug.php")) {
			$found = get_stylesheet_directory().$this->templates."/$slug.php";
		} else if (file_exists(get_stylesheet_directory()."/$slug.php")) {
			$found = get_stylesheet_directory()."/$slug.php";
		} else if (file_exists(get_template_directory().$this->templates."/$slug.php")) {
			$found = get_template_directory().$this->templates."/$slug.php";
		} else if (file_exists(get_template_directory()."/$slug.php")) {
			$found = get_template_directory()."/$slug.php";
		} else if (file_exists($this->dir.$this->templates."/$slug.php")) {
			$found = $this->dir.$this->templates."/$slug.php";
		} else {
			$string = _x('WARNING: No template found for %s','placeholder is a file name','tcc-plugin');
			log_entry(sprintf($string,$slug));
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

if (!class_exists('Paths')) { class_alias('TCC_Plugin_Paths','Paths'); }
