<?php

class TCC_Plugin_Paths {

	public $dir;
	public $url;

	use TCC_Trait_Singleton;

	public function __construct($args) {
		$this->dir = $args['path'];
		$this->url = $args['url'];
	}

	public function __call($name,$arguments) {
		if (property_exists($this,$name)) return $this->name;
		return null;
	}

	public static function url($file='') {
		return $this->url . $file;
	}

}
