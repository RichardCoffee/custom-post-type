<?php

trait TCC_Trait_Singleton {

	private static $instance;

	public static function instance() {
		if ( ! (self::$instance instanceof self) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function get_instance( $args = array() ) {
		if ( ! (self::$instance instanceof self) ) {
			self::$instance = new self( $args );
		}
		return self::$instance;
	}

	public function __clone() {
		$message = __( 'This class can not be cloned' , 'tcc-fluid') . ' ' . debug_calling_function();
		$version = ( isset( $this->version ) ) ? $this->version : '0.0.0';
		_doing_it_wrong( __FUNCTION__, $message, $version );
	}


}
