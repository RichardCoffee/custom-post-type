<?php

# class requires that the js function fix_jquery_datepicker() be available
# sanitize will require a false timestamp attribute when checking strings
class TCC_Form_Field_Date extends TCC_Form_Field_Field {

	protected $class     = 'date';
	protected $timestamp =  false;
	protected $type      = 'date';

	public function __construct( $args = array() ) {
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
			'id'    => 'visible_' . $this->id,
			'name'  => 'visible_' . $this->name,
			'size'  => 10,
			'class' => $this->class,
			'value' => $this->form_date(),
			'placeholder'   => $this->placeholder,
			'data-altfield' => $this->name,
			'onchange'      => 'fix_jquery_datepicker(this);'
		);
		$hidden = array(
			'type'  => 'hidden',
			'id'    => $this->id,
			'name'  => $this->name,
			'value' => $this->deform_date()
		);
		$this->element( 'input', $visible );
		$this->element( 'input', $hidden );
	}

	# convert to unix timestamp
	public function deform_date() {
		if ( is_string( $this->value ) ) {
			return strtotime( $this->value );
		}
		return $this->value;
	}

	# convert to formatted date
	public function form_date() {
		if ( is_string( $this->value ) ) {
			return date( self::$date_format, strtotime( $this->value ) );
		} else {
			return date( self::$date_format, $this->value );
		}
	}

	public function bare() {
		$attrs = array(
			'type'  => $this->type,
			'id'    => $this->id,
			'name'  => $this->name,
			'size'  => 10,
			'class' => $this->class,
			'value' => $this->form_date(),
			'placeholder' => $this->placeholder,
		);
		$this->element( 'input', $attrs );
	}

	public function sanitize_timestamp( $date ) {
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
