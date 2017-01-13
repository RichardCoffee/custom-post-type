<?php

trait TCC_Trait_Single {

	private static $instance;

	public static function get_instance() {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self;
		}
		return self::$instance;
	}


}
