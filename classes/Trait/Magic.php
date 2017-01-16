<?php

trait TCC_Trait_Magic {

	public function __call($name,$arguments) {
		if (property_exists($this,$name)) {
			return $this->name; }
		return null;
	}

	#  http://php.net/manual/en/language.oop5.overloading.php#object.unset
	public function __get($name) {
		if (property_exists($this,$name)) {
			return $this->$name; } #  Allow read access to private/protected variables
		return null;
	}

	#  http://php.net/manual/en/language.oop5.overloading.php#object.unset
	public function __isset($name) {
		return isset($this->$name); #  Allow read access to private/protected variables
	} //*/

}
