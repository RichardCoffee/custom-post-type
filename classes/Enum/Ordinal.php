<?php

/*
 *  classes/Enum/Ordinal.php
 *
 */

class TCC_Enum_Ordinal extends TCC_Enum_Enum {


	use TCC_Trait_Singleton;


	protected function __construct( $args = array() ) {
		$this->set = array( 'Zero',
			'First',         'Second',         'Third',         'Fourth',        'Fifth',
			'Sixth',         'Seventh',        'Eighth',        'Ninth',         'Tenth',
			'Eleventh',      'Twelfth',        'Thirteenth',    'Fourteenth',    'Fifteenth',
			'Sixteenth',     'Seventeenth',    'Eighteenth',    'Nineteenth',    'Twentieth',
			'Twenty-First',  'Twenty-Second',  'Twenty-Third',  'Twenty-Fourth', 'Twenty-Fifth',
			'Twenty-Sixth',  'Twenty-Seventh', 'Twenty-Eighth', 'Twenty-Ninth',  'Thirtieth',
		);
		if ( $args && is_array( $args ) ) $this->set = array_replace( $this->set, $args );
	}

}
