<?php

# sanitize requires the attributes 'choices' and 'default'
class WMN_Form_Field_Select extends WMN_Form_Field_Field {

	protected $choices =  array();
	protected $type    = 'select';

	public function __construct( $args ) {
		$this->sanitize = array( $this, 'sanitize' );
		parent::__construct( $args );
	}

	public function select() {
		if ( $this->choices ) {
			$select = array(
				'id'    => $this->field_id,
				'name'  => $this->field_name,
				'class' => $this->field_css
			);
			if ( ! empty( $this->description ) ) {
				$select['aria-labelledby'] = $this->field_id . '_label';
			}
			if ( strpos( '[]', $this->field_name ) ) {
				$select['multiple'] = 'multiple';
			}
			if ( $this->onchange ) {
				$select['onchange'] = $this->onchange;
			} ?>
			<div class="undef-input-group"><?php
				if ( ! empty( $this->description ) ) {
					echo $this->label();
				} ?>
				<select <?php $this->apply_attrs( $select ); ?>><?php
					if ( is_callable( $this->choices ) ) {
						call_user_func( $this->choices );
					} else if ( is_array( $this->choices ) ) {
						$assoc = is_assoc( $this->choices );
						foreach( $this->choices as $key => $text ) {
							$attrs = array(
								'value'    => ( $assoc ) ? $key : $text,
							);
							if ( ( $assoc && ( $key === $this->field_value ) ) || ( $text === $this->field_value ) ) {
								$attrs['selected'] = '';
							}
							$this->apply_attrs_element( 'option', $attrs, ' ' . $text . ' ' );
						}
					} ?>
				</select>
			</div><?php
		}
	}

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
