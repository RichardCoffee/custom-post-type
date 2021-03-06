<?php
/**
 *  Provides an Ordinal enumeration set.
 *
 * @package Plugin
 * @subpackage Enum
 * @since 20191201
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2019, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Enum/Ordinal.php
 */
defined( 'ABSPATH' ) || exit;


class TCC_Enum_Ordinal extends TCC_Enum_Enum {


	/**
	 *  Trait to provide singleton methods.
	 */
	use TCC_Trait_Singleton;


	/**
	 *  Constructor method.
	 *
	 * @since 20191201
	 * @param array $args Substitution values for the set.
	 */
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
