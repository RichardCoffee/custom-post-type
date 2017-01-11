<?php

trait TCC_Trait_Taxonomy {

	protected function get_taxonomy_label($tax,$label) {
		$return = '';
		if (taxonomy_exists($tax) {
			$labels = get_taxonomy($tax)->labels;
			if (!empty($labels->$label)) {
				$return = $labels->$label;
			}
		}
		return $return;
	}

}
