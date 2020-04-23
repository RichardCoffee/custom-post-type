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
 * insert a key/value pair into an array after a specific key
 *
 * @param  array  $array      Array to act upon.
 * @param  string $key        Key to search for.
 * @param  string $new_key    Key to insert.
 * @param  mixed  $new_value  Value to insert.
 * @return array              Modified array.
 * @link http://eosrei.net/comment/287
 */
if ( ! function_exists( 'array_insert_after' ) ) {
	function array_insert_after( array $array, $key, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				$new[ $k ] = $value;
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
			}
			return $new;
		}
		return $array;
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

/**
 *  Provides escaping of a translated string with comment and count, use ONLY with suitable MakePOT class - which is not the one WP provides.
 *
 * @since 20170202
 * @link wp_includes/i10n.php#_nx
 */
if ( ! function_exists( 'esc_html_nx' ) ) {
	function esc_html_nx( $single, $plural, $number, $context, $domain = 'default' ) {
		$translations = get_translations_for_domain( $domain );
		$translation  = $translations->translate_plural( $single, $plural, $number, $context );
		return esc_html( apply_filters( 'ngettext_with_context', $translation, $single, $plural, $number, $context, $domain ) );
	}
}
