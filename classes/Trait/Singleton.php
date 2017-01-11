<?php

trait TCC_Trait_Singleton {

	private static $instance;

	public static function get_instance($args=array()) {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self($args);
		}
		return self::$instance;
	}


}
