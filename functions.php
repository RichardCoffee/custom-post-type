<?php

function tcc_plugin_class_loader( $class ) {
	if ( substr( $class, 0, 4 ) === 'TCC_' ) {
		$load = str_replace( '_', '/', substr( $class, ( strpos( $class, '_' ) + 1 ) ) );
		$file = TCC_PLUGIN_DIR . "/classes/{$load}.php";
		if ( is_readable( $file ) ) include $file;
	}
}
spl_autoload_register( 'tcc_plugin_class_loader' );

/**
 *  array_column() introduced in PHP 7.0.0
 *
 * @since 20200315
 * @param array  $input   Array to get the column from.
 * @param string $column  Key to retrieve.
 * @return array          Contains the column requested or an empty array.
 */
if ( ! function_exists( 'array_column' ) ) {
	function array_column( array $input, $column ) {
		$result = array();
		foreach( $input as $item ) {
			if ( array_key_exists( $column, $item ) ) {
				$result[] = $item[ $column ];
			}
		}
		return $result;
	}
}

/**
 *  array_key_first() introduced in PHP 7.3.0
 *
 * @since 20200315
 * @param array $arr  Input array.
 * @return string     First key of the array.
 */
if ( ! function_exists( 'array_key_first' ) ) {
	function array_key_first( array $arr ) {
		foreach( $arr as $key => $item ) return $key;
		return null;
	}
}

/**
 *  array_key_last() introduced in PHP 7.3.0
 *
 * @since 20200315
 * @param array $arr  Input array.
 * @return string     Last key of the array.
 */
if ( ! function_exists( 'array_key_last' ) ) {
	function array_key_last( array $arr ) {
		return array_key_first( array_reverse( $arr, true ) );
	}
}

/**
 *  Returns the key after the needle, or false otherwise.
 *
 * @since 20200315
 * @param string $needle  Key in the search array.
 * @param array  $search  Array to get the next key from.
 * @param bool   $strict  Whether to make a strict type comparison.
 * @return string|bool    The key following the needle, or boolean false.
 */
if ( ! function_exists( 'array_key_next' ) ) {
	function array_key_next( $needle, $search, $strict = false ) {
		if ( empty( $needle ) ) return false;
		if ( empty( $search ) ) return false;
		if ( ! is_array( $search ) ) return false;
		$keys = array_keys( $search );
		$spot = array_search( $needle, $keys, $strict );
		if ( $spot === false ) return false;
		$spot = ( $spot + 1 === count( $keys ) ) ? 0 : $spot + 1;
		return $keys[ $spot ];
	}
}

/**
 * Remove a key/value pair from an associative array, using the key.
 *
 * @since 20200315
 * @param string $needle    Key to be deleted.
 * @param array  $haystack  Associative array
 * @return array
 */
if ( ! function_exists( 'array_remove_value' ) ) {
	function array_remove_value( $needle, $haystack ) {
		if ( $needle && is_string( $needle ) && $haystack && is_array( $haystack ) ) {
			if( ( $key = array_search( $needle, $haystack ) ) !== false ) {
				unset( $haystack[ $key ] );
			}
		}
		return $haystack;
	}
}

/**
 *  Replace a key in an array
 *
 * @since 20200315
 * @param array  $arr  Array that needs a key replaced
 * @param string $old  Key to be replaced.
 * @param string $new  New key value.
 * @return array       Array with replaced key.
 */
if ( ! function_exists( 'array_key_replace' ) ) {
	function array_key_replace( array $arr, $old, $new ) {
		if ( ! array_key_exists( $old, $arr ) ) return $array;
		$keys = array_keys( $arr );
		$pos  = array_search( $old, $keys );
		if ( $pos === false ) return $arr;
		$keys[ $pos ] = $new;
		return array_combine( $keys, $arr );
	}
}
