<?php
/**
 * classes/Form/Field/Select.php
 *
 */
/**
 * display the select field
 *
 */
class TCC_Form_Field_Select extends TCC_Form_Field_Field {

	/**
	 * array containing the options for the select element
	 */
	protected $choices =  array();
	/**
	 * field type
	 */
	protected $type    = 'select';

	/**
	 * initialize the class
	 *
	 * @param array $args
	 */
	public function __construct( $args ) {
		# sanitize requires the properties 'choices' and 'default'
		$this->sanitize = array( $this, 'sanitize' );
		parent::__construct( $args );
	}

	/**
	 * display enclosing div and select element
	 *
	 * @param string $css
	 */
	public function select_div( $css = 'undef-input-group') { ?>
		<div class="<?php e_esc_attr( $css ); ?>"><?php
			$this->label();
			$this->select(); ?>
		</div><?php
	}

	/**
	 * display select element as table row
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
	 * core function - displays select element with options
	 */
	public function select() {
		if ( $this->choices ) {
			$select = array(
				'id'    => $this->field_id,
				'name'  => $this->field_name,
				'class' => $this->field_css,
				'onchange' => $this->onchange,
			);
			if ( ! empty( $this->description ) ) {
				$select['aria-labelledby'] = $this->field_id . '_label';
			}
			if ( strpos( '[]', $this->field_name ) ) {
				$select['multiple'] = 'multiple';
			}
			$this->tag( 'select', $select );
				if ( is_callable( $this->choices ) ) {
					call_user_func( $this->choices );
				} else if ( is_array( $this->choices ) ) {
					$assoc = is_assoc( $this->choices );
					foreach( $this->choices as $key => $text ) {
						$attrs = array(
							'value' => ( $assoc ) ? $key : $text,
						);
						$attr = $this->selected( $attrs, $attrs['value'], $this->field_value );
						$this->element( 'option', $attr, ' ' . $text . ' ' );
					}
				} ?>
			</select><?php
		}
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
