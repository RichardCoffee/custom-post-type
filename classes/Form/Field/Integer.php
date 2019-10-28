<?php

defined( 'ABSPATH' ) || exit;

class WMN_Form_Field_Integer extends WMN_Form_Field_Field {

	public function sanitize( $input ) {
		return intval( $input, 10 );
	}

}
