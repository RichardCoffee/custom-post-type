<?php
/**
 *  classes/Enum/Enum.php
 *
 * @package Plugin
 * @subpackage Enum
 * @since 20191201
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2017, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Plugin/Plugin.php
 */
defined( 'ABSPATH' ) || exit;
/**
 *  Provides base abstract class for enumeration capabilities.
 *
 * @since 20191201
 */
abstract class TCC_Enum_Enum {


	/**
	 *  Enumeration set
	 *
	 * @since 20191201
	 * @var array
	 */
	protected $set = array();

	/**
	 *  Example constructor method, designed to be used with the trait DND_Trait_Singleton
	 *
	 * @since 20191201
	 * @param array Should contain changes for the enumeration set
	 */
	protected function __construct( $args = array() ) {
		$this->set = array( /* Array values go here */ );
		if ( $args && is_array( $args ) ) $this->set = array_replace( $this->set, $args );
	} //*/

	/**
	 *  Get the indicated enumerated item.
	 *
	 * @since 20191201
	 * @param int The index of the requested item.
	 * @return string|mixed
	 */
	public function get( $position ) {
		if ( array_key_exists( $position, $this->set ) ) {
			return $this->set[ $position ];
		}
		return '-undefined-';
	}

	/**
	 *  Check to see if an item exists in the enumerated set.
	 *
	 * @since 20191201
	 * @param string|mixed The item to search for
	 * @param bool Indicates a strict type comparison is desired
	 * @return bool
	 */
	public function has( $search, $strict = false ) {
		return in_array( $search, $this->set, $strict );
	}

	/**
	 *  Retrieve the numeric position of an item in the enumerated set.
	 *
	 * @since 20191201
	 * @param string|mixed Item to search for
	 * @param bool Indicates a strict type comparison is desired
	 * @return int|bool
	 */
	public function position( $search, $strict = false ) {
		if ( $this->has( $search, $strict ) ) {
			return array_search( $search, $this->set, $strict );
		}
		return false;
	}

	/**
	 *  Compares two set items, and returns a numeric value indicator.
	 *
	 * @since 20191201
	 * @param string|mixed First item to compare
	 * @param string|mixed Second item to compare
	 * @param bool Indicates a strict type comparison is desired
	 * @return int
	 */
	public function compare( $one, $two, $strict = false ) {
		$p1 = $this->position( $one, $strict );
		$p2 = $this->position( $two, $strict );
		if ( $p1 > $p2 ) return 1;
		if ( $p2 > $p1 ) return -1;
		return 0;
	}


}
