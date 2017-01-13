<?php

class TCC_Plugin_Paths {

	public $file;
	public $dir;
	public $url;

	use TCC_Trait_Singleton;

	public function __construct($args) {
log_entry($args);
		$this->dir = $args['path'];
	}

}
