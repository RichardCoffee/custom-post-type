<?php

/*
 *  classes/Enum/Latinate.php
 *
 */

class TCC_Enum_Latinate extends TCC_Enum_Enum {


	use TCC_Trait_Singleton;


	protected function __construct( $args = array() ) {
		$this->set = array( 'Absence',
			'Primary',      'Secondary',      'Tertiary',      'Quaternary',     'Quinary',
			'Senary',       'Septenary',      'Octonary',      'Nonary',         'Denary',
			'Undenary',     'Duodenary',      'Tredenary',     'Quattuordenary', 'Quindenary',
			'Sedenary',     'Septendenary',   'Octodenary',    'Novemdenary',    'Vigenary',
		);
		if ( $args && is_array( $args ) ) $this->set = array_replace( $this->set, $args );
	}

}
