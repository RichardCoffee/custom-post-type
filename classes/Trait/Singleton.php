<?php

trait TCC_Trait_Singleton {

	private static $instance;

	public static function get_instance($args=array()) {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self($args);
		}
		return self::$instance;
	}

	public function __clone() {
		$message = __( 'This class can not be cloned' , 'tcc-fluid') . ' ' . debug_calling_function(1);
		_doing_it_wrong( __FUNCTION__, $message, TCC_PLUGIN_VERSION );
	}


}
