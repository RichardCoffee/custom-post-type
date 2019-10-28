<?php

defined( 'ABSPATH' ) || exit;

class TCC_Form_Field_CheckBox extends TCC_Form_Field_Field {

	protected $type      = 'checkbox';
	protected $checked   = false;
	protected $bootstrap = false;

	public function checkbox() {
		echo $this->get_checkbox();
	}

	public function get_checkbox() {
		return $this->get_tag( 'label', [ 'id' => $this->field_id . '_label' ] ) . $this->get_input() . '&nbsp;' . $this->description . '</label>';
	}

	protected function get_input_attributes() {
		$attrs = parent::get_input_attributes();
		$this->checked( $attrs, $this->checked, true );
		return $attrs;
	}

}
