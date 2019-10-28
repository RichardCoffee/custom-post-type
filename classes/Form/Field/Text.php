<?php

defined( 'ABSPATH' ) || exit;

class WMN_Form_Field_Text extends WMN_Form_Field_Field {


	public function text() { ?>
		<div class="undef-input-group"><?php
			$this->label();
#			$this->addon();
			$this->input(); ?>
		</div><?php
	}

	protected function addon() { ?>
		<span class="input-group-addon"></span><?php
	}


}
