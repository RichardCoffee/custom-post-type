<?php

# sanitize will require a false timestamp attribute when checking strings
class WMN_Form_Field_Date extends WMN_Form_Field_Field {

	protected $type      = 'date';
	protected $field_css = 'date';

	public function __construct( $args ) {
		$this->sanitize    = array( $this, 'sanitize_timestamp' );
		$this->placeholder = 'dd/mm/yyyy';
		parent::__construct( $args );
		$this->add_form_control_css( 'date' );
	}

	public function date() { ?>
		<div class="undef-input-group"><?php
			$this->label();
			if ( $this->timestamp ) {
				$this->input();
			} else {
				$this->bare();
			} ?>
		</div><?php
	}

	public function input() {
		$visible = array(
			'type'  => $this->type,
			'id'    => 'visible_' . $this->field_id,
			'name'  => 'visible_' . $this->field_name,
			'size'  => 10,
			'class' => $this->field_css,
			'value' => $this->form_date(),
			'placeholder'   => $this->placeholder,
			'data-altfield' => $this->field_name,
			'onchange'      => 'fix_jquery_datepicker(this);'
		);
		$hidden = array(
			'type'  => 'hidden',
			'id'    => $this->field_id,
			'name'  => $this->field_name,
			'value' => $this->deform_date()
		);
		$this->apply_attrs_element( 'input', $visible );
		$this->apply_attrs_element( 'input', $hidden );
	}

	# convert to unix timestamp
	public function deform_date() {
		if ( is_string( $this->field_value ) ) {
			return strtotime( $this->field_value );
		}
		return $this->field_value;
	}

	# convert to formatted date
	public function form_date() {
		if ( is_string( $this->field_value ) ) {
			return date( self::$date_format, strtotime( $this->field_value ) );
		} else {
			return date( self::$date_format, $this->field_value );
		}
	}

	public function bare() {
		$attrs = array(
			'type'  => $this->type,
			'id'    => $this->field_id,
			'name'  => $this->field_name,
			'size'  => 10,
			'class' => $this->field_css,
			'value' => $this->form_date(),
			'placeholder' => $this->placeholder,
		);
		$this->apply_attrs_element( 'input', $attrs );
	}

	public function sanitize( $date ) {
		if ( is_string( $date ) ) {
			return date( self::$date_format, strtotime( $date ) );
		} else {
			$formatted = date( self::$date_format, $date );
			if( ! $formatted ) {
				return false;
			}
		}
		return $date;
	}


}
