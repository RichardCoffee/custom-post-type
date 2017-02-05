<?php

/*
 *  https://secure.php.net/manual/en/language.oop5.magic.php
 *  http://www.garfieldtech.com/blog/magical-php-call
 *  https://lornajane.net/posts/2012/9-magic-methods-in-php
 */

trait TCC_Trait_Magic {

#	protected static $magic__call = array();

	public function __call( $string, $arguments ) {
		$return = false;
#		if ( in_array( $string, self::$magic__call ) ) {
#			$return = call_user_func_array( $string, $args ); }
		if ( property_exists( $this, $string ) ) {
			$return = $this->$string; }
		if ( ! $return ) {
			echo "unknown method " . $method; }
		return $return;
	}

	#	http://php.net/manual/en/language.oop5.overloading.php#object.unset
	public function __get($name) {
		if (property_exists($this,$name)) {
			return $this->$name; } #  Allow read access to private/protected variables
		return null;
	}

	#	http://php.net/manual/en/language.oop5.overloading.php#object.unset
	public function __isset($name) {
		return isset($this->$name); #  Allow read access to private/protected variables
	} //*/
/*
	public static function register__call($method) {
		self::$magic__call[] = $method;
	} //*/

}