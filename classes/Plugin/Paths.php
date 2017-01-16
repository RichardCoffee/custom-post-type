<?php

class TCC_Plugin_Paths {

	private $dir;
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

}
