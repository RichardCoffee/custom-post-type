<?php

/*
 *  classes/Enum/Enum.php
 *
 */

abstract class TCC_Enum_Enum {


	protected $set = array();

/*
	protected function __construct( $args = array() ) {
		$this->set = array();
		if ( $args && is_array( $args ) ) $this->set = array_replace( $this->set, $args );
	} //*/

	public function get( $position ) {
		if ( array_key_exists( $position, $this->set ) ) {
			return $this->set[ $position ];
		}
		return '-undefined-';
	}

	public function has( $search, $strict = false ) {
		return in_array( $search, $this->set, $strict );
	}

	public function position( $search, $strict = false ) {
		if ( $this->has( $search, $strict ) ) {
			return array_search( $search, $this->set, $strict );
		}
		return -1;
	}

	public function compare( $one, $two, $strict = false ) {
		$p1 = $this->position( $one, $strict );
		$p2 = $this->position( $two, $strict );
		if ( $p1 > $p2 ) return 1;
		if ( $p2 > $p1 ) return -1;
		return 0;
	}


}
