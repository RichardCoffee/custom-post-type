<?php
/**
 *  classes/Enum/Latinate.php
 *
 * @package Plugin
 * @subpackage Enum
 * @since 20191201
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2019, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Enum/Enum.php
 */
defined( 'ABSPATH' ) || exit;
/**
 *  Provides a Latinate enumeration set.
 *
 * @since 20191201
 */
class TCC_Enum_Latinate extends TCC_Enum_Enum {


	/**
	 *  Trait to provide singleton methods.
	 */
	use TCC_Trait_Singleton;


	/**
	 *  Constructor method
	 *
	 * @since 20191201
	 * @param array Substitution values for the set
	 * @link https://oeis.org/wiki/Trigesimal_numeral_system
	 * @link https://wikidiff.com/vigenary
	 */
	protected function __construct( $args = array() ) {
		$this->set = array( 'Absence',
			'Primary',      'Secondary',      'Tertiary',      'Quaternary',     'Quinary',
			'Senary',       'Septenary',      'Octonary',      'Nonary',         'Denary',
			'Undenary',     'Duodenary',      'Tredenary',     'Quadrodenary',   'Quindenary',
			'Sedenary',     'Septendenary',   'Octodenary',    'Nonadenary',     'Vigenary', // Icosa - Greek
		);
		if ( $args && is_array( $args ) ) $this->set = array_replace( $this->set, $args );
	}


}
