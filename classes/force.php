<?php

# * @since 2020014
# * @link https://www.php.net/manual/en/language.oop5.visibility.php

class TCC_Force {


	public static function force_set($object, $property, $value) {
		call_user_func(\Closure::bind(
			function () use ($object, $property, $value) {
				$object->{$property} = $value;
			},
			null,
			$object
		));
	}

	public static function force_get($object, $property) {
		return call_user_func(\Closure::bind(
			function () use ($object, $property) {
				return $object->{$property};
			},
			null,
			$object
		));
	}


}
