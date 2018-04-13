<?php

# sanitize will require a false timestamp attribute when checking strings
class WMN_Form_Field_Date extends WMN_Form_Field_Field {

	protected $timestamp = true;
	protected $field_css = 'date';

	public function __construct( $args ) {
		$this->sanitize    = array( $this, 'sanitize_timestamp' );
		$this->placeholder = 'dd/mm/yyyy';
		parent::__construct( $args );
		$this->add_form_control_css( 'date' );
		if ( ! $this->timestamp && ( $this->sanitize === array( $this, 'sanitize_timestamp' ) ) ) {
			$this->sanitize = array( $this, 'sanitize_string' );
		}
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
		$check = intval( $this->field_value, 10 );
		if ( $check < 3000 ) { // probably a date string
			if ( $unix = strtotime( $this->field_value ) ) {
				return $unix;
			}
		}
		return $this->field_value;
	}

	# convert to formatted date
	public function form_date( $reset = false ) {
		//  check for unix time before formatting
		$check = intval( $this->field_value, 10 );
		if ( $reset && $check < 3000 ) {
			$check = strtotime( $this->field_value );
		}
		if ( $check > 3000 ) {  // large year value - assumed unix time stamp
			return date( self::$date_format, $check );
		}
		return $this->field_value;
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

	public function sanitize_timestamp( $date ) {
		$date_format = DateTime::createFromFormat( self::$date_format, $date );
		if( ! $date_format ) {
			return false;
		}
		return $date;
	}

	public function sanitize_string( $date ) {
		return date( self::$date_format, strtotime( $date ) );
	}


}
