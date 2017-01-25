<?php

trait TCC_Trait_Single {

	private static $instance;

	public static function get_instance() {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __clone() {
		$message = __( 'This class can not be cloned' , 'tcc-fluid') . ' ' . debug_calling_function(1);
		_doing_it_wrong( __FUNCTION__, $message, TCC_PLUGIN_VERSION );
	}


}
