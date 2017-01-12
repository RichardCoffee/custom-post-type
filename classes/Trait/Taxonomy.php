<?php

trait TCC_Trait_Taxonomy {

	protected function get_taxonomy_label($tax,$label) {
		if (taxonomy_exists($tax)) {
			$labels = get_taxonomy_labels($tax);
			if (!empty($labels->$label)) {
				return $labels->$label;
			}
		}
		return '';
	}

}
