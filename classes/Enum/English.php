<?php
/**
 *  Provides an English enumeration set.
 *
 * @package Plugin
 * @subpackage Enum
 * @since 20191202
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2019, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Enum/English.php
 */
defined( 'ABSPATH' ) || exit;
/*
 *  Example usage:
 *
 *  This example replaces 'Zero' with 'None'
 *
 *  $enum = TCC_Enum_English::get_instance( [ 'None' ] );
 *
 *  To replace another value, like 'Three', you would need to do this:
 *
 *  $enum = TCC_Enum_English::get_instance( [ 3 => 'Trace' ] );
 */
class TCC_Enum_English extends TCC_Enum_Enum {


	/**
	 *  Trait to provide singleton methods.
	 */
	use DND_Trait_Singleton;


	/**
	 *  Constructor method
	 *
	 * @since 20191202
	 * @param array $args Substitution values for the set.
	 */
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
