<?php

/*
 *  classes/Enum/English.php
 *
 */

class TCC_Enum_English extends TCC_Enum_Enum {


	use DND_Trait_Singleton;


	protected function __construct( $args = array() ) {
		$this->set = array( 'Zero',
			'One',           'Two',          'Three',        'Four',        'Five',
			'Six',           'Seven',        'Eight',        'Nine',        'Ten',
			'Eleven',        'Twelve',       'Thirteen',     'Fourteen',    'Fifteen',
			'Sixteen',       'Seventeen',    'Eighteen',     'Nineteen',    'Twenty',
			'Twenty-One',    'Twenty-Two',   'Twenty-Three', 'Twenty-Four', 'Twenty-Five',
			'Twenty-Six',    'Twenty-Seven', 'Twenty-Eight', 'Twenty-Nine', 'Thirty',
		);
		if ( $args && is_array( $args ) ) $this->set = array_replace( $this->set, $args );
	}

}
