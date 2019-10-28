<?php
/**
 *  Form select field class
 *
 * @package Plugin
 * @subpackage Form
 * @since 20190604
 * @author Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright Copyright (c) 2019, Richard Coffee
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Form/Field/Select.php
 */
defined( 'ABSPATH' ) || exit;
/**
 *  Handles all tasks required for displaying and saving a select field on an admin form.
 */
class TCC_Form_Field_Select extends TCC_Form_Field_Field {

	/**
	 * @since 20190604
	 * @var array Contains options for select field.
	 */
	protected $choices =  array();
	/**
	 * @since 20190604
	 * @var string Contains the form field type.
	 */
	protected $type = 'select';

	/**
	 *  Constructor function
	 *
	 * @since 20190604
	 * @param array $args
	 */
	public function __construct( $args ) {
		# sanitize requires the properties 'choices' and 'default'
		$this->sanitize = array( $this, 'sanitize' );
		parent::__construct( $args );
	}

	/**
	 *  Display select element within enclosing div
	 *
	 * @since 20180701
	 * @param string $css
	 */
	public function select_div( $css = 'undef-input-group') { ?>
		<div class="<?php e_esc_attr( $css ); ?>"><?php
			$this->label();
			$this->select(); ?>
		</div><?php
	}

	/**
	 *  Display select element within table row
	 *
	 * @since 20180701
	 */
	public function select_table_row() { ?>
		<tr>
			<th><?php
				$this->label(); ?>
			</th>
			<td><?php
				$this->select(); ?>
			</td>
		</tr><?php
	}

	/**
	 *  Render a select field.
	 *
	 * @since 20190605
	 * @uses TCC_Trait_Attributes::tag()
	 * @uses TCC_Trait_Attributes::element()
	 * @uses TCC_Trait_Attributes::selected()
	 */
	public function select() {
		if ( $this->choices ) {
			$element = $this->get_select_element_attributes();
			$this->tag( 'select', $element );
				if ( is_callable( $this->choices ) ) {
					call_user_func( $this->choices );
				} else if ( is_array( $this->choices ) ) {
					$assoc = is_assoc( $this->choices );
					foreach( $this->choices as $key => $text ) {
						$attrs = [ 'value' => ( $assoc ) ? $key : $text ];
						$this->selected( $attrs, $attrs['value'], $this->value );
						$this->element( 'option', $attrs, ' ' . $text . ' ' );
					}
				} ?>
			</select><?php
		}
	}

	/**
	 *  Determines attributes for select element.
	 *
	 * @since 20190605
	 */
	protected function get_select_element_attributes() {
		if ( ! $this->class ) $this->class = 'components-select-control__input';
		$attrs = array(
			'id'    => $this->id,
			'class' => $this->class,
			'type'  => $this->type,
			'name'  => $this->name,
			'onchange' => $this->onchange,
		);
		if ( strpos( '[]', $this->name ) ) $attrs['multiple'] = 'multiple';
		if ( $this->description ) $attrs['aria-labelledby'] = $this->id . '_label';
		return $attrs;
	}

	/**
	 * validate and sanitize select value
	 *
	 * @param string $input
	 * @return string
	 */
	public function sanitize( $input ) {
		$input = sanitize_text_field( $input );
		return ( in_array( $input, $this->choices ) || array_key_exists( $input, $this->choices ) ) ? $input : $this->default;
	}


}


/**
 *  check if an array is an assocative array
 *
 * @since 20180410
 * @link https://stackoverflow.com/questions/5996749/determine-whether-an-array-is-associative-hash-or-not
 * @param array $array
 * @return bool
 */
if ( ! function_exists( 'is_assoc' ) ) {
	function is_assoc( array $array ) {
		// Keys of the array
		$keys = array_keys($array);
		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys( $keys ) !== $keys;
	}
}
