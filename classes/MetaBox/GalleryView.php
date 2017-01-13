<?php

class TCC_MetaBox_GalleryView extends TCC_MetaBox_Gallery {

	public function __construct($args=array()) {
		parent::__construct($args);
		if (!is_admin()) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		}
		add_shortcode( 'galleryview', array( $this, 'show_galleryview' ) );
	}

	public function wp_enqueue_scripts() {
		wp_register_style('tcc-galleryview-css', get_theme_file_uri('galleryview/css/jquery.galleryview-3.0-dev.css'), null, '3.0');
		wp_register_style('tcc-gv-css',          get_theme_file_uri('css/galleryview.css'), null, TCC_THEME_VERSION);
		wp_register_script('tcc-galleryview-js', get_theme_file_uri('galleryview/js/jquery.galleryview-3.0-dev.js'),array('jquery'),'3.0',true);
		wp_register_script('tcc-gv-easing',      get_theme_file_uri('galleryview/js/jquery.easing.1.3.js'),array('jquery','tcc-galleryview-js'),'1.3',true);
		wp_register_script('tcc-gv-timers',      get_theme_file_uri('galleryview/js/jquery.timers-1.2.js'),array('jquery','tcc-galleryview-js'),'1.2',true);
		wp_register_script('tcc-gv-load',        get_theme_file_uri('js/galleryview.js'),array('tcc-gv-easing','tcc-gv-timers'), TCC_THEME_VERSION, true);
		if ( is_singular() ) {
			add_filter( 'pre_get_posts', array( $this, 'load_galleryview' ) );
		}
	}

	public function load_galleryview($query) {
		if (!is_admin() && is_single()) {
			if (  $query->get('post_type') === $this->type ) {
				wp_enqueue_style('tcc-galleryview-css');
				wp_enqueue_style('tcc-gv-css');
				wp_enqueue_script('tcc-gv-load');
			}
		}
		return $query;
	}

	public function show_galleryview($postID=0) {
		$postID = ($postID) ? $postID : get_the_ID();
		if ($postID) {
			$images = get_listing_images($postID,true);
			if ($images || has_post_thumbnail($postID)) { ?>
				<div>
					<ul class='tcc-galleryview'><?php
						if (has_post_thumbnail($postID)) {
							$link = get_post_thumbnail_url($postID,'full');
							echo "<li><img src='$link'/></li>";
						}
						foreach($images as $image) {
							echo "<li><img src='$image'/></li>";
						} ?>
					</ul>
				</div><?php
			}
		}
	}

}
