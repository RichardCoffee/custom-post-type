<?php

defined( 'ABSPATH' ) || exit;

trait TCC_Trait_Taxonomy {

	protected function get_taxonomy_label($tax,$label) {
		if (taxonomy_exists($tax)) {
			$tax_ob = get_taxonomy($tax);
			$labels = $tax_ob->labels;
			if (!empty($labels->$label)) {
				return $labels->$label;
			}
		}
		return '';
	}


}
