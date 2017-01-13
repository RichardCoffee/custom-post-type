<?php

class TCC_Plugin_Paths {

	public $dir;
	public $url;
	public $version;

	use TCC_Trait_Singleton;

	public function __construct($args) {
		foreach ($args as $key=>$arg) {
			if (property_exists($this,$key)) {
				$this->$key = $arg; }
		}
	}

	public function __call($name,$arguments) {
		if (property_exists($this,$name)) return $this->name;
		return null;
	}

	public static function url($file='') {
		return self::$instance->url . $file;
	}

	public static function version() {
|  |  return self::$instance->version;
|  }

}
