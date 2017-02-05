<?php

defined( 'ABSPATH' ) || exit;

# [gallery link="file" size="medium" ids="271,272,273,274,275,276,277,278,279,280,281,282,283"]

class TCC_MetaBox_GalleryView extends TCC_MetaBox_Gallery {

	public function __construct($args=array()) {
		parent::__construct($args);
		if (!is_admin()) {
			add_action( 'tcc_after_enqueue', array( $this, 'register_galleryview' ) );
		}
		add_shortcode( 'galleryview', array( $this, 'show_galleryview' ) );
	}

	public function register_galleryview() {
		wp_register_style( 'tcc-gv-css',          get_theme_file_uri('galleryview/css/jquery.galleryview-3.0-dev.css'), null, '3.0');
		wp_register_style( 'tcc-galleryview-css', get_theme_file_uri('css/galleryview.css'), array('tcc-gv-css'), TCC_THEME_VERSION);
		wp_register_script('tcc-gv-js',           get_theme_file_uri('galleryview/js/jquery.galleryview-3.0-dev.js'),array('jquery'),'3.0',true);
		wp_register_script('tcc-gv-easing',       get_theme_file_uri('galleryview/js/jquery.easing.1.3.js'),array('jquery','tcc-gv-js'),'1.3',true);
		wp_register_script('tcc-gv-timers',       get_theme_file_uri('galleryview/js/jquery.timers-1.2.js'),array('jquery','tcc-gv-js'),'1.2',true);
		wp_register_script('tcc-galleryview-js',  get_theme_file_uri('js/galleryview.js'),array('tcc-gv-easing','tcc-gv-timers'), TCC_THEME_VERSION, true);
		$this->enqueue_galleryview();
	}

	public function enqueue_galleryview() {
		global $wp_query;
		if (!is_admin() && $wp_query->is_single) {
			if ( $wp_query->get('post_type')===$this->type ) {
				wp_enqueue_style( 'tcc-galleryview-css' );
				$data = array( 'div_id' => $this->div_id );
				wp_localize_script( 'tcc-galleryview-js', 'tcc_gallery', $data );
				wp_enqueue_script(  'tcc-galleryview-js' );
			}
		}
	}

	public function show_galleryview($postID=0) {
		$postID = ($postID) ? $postID : get_the_ID();
		if ($postID) {
			$images = $this->get_gallery_images($postID,true);
			if ($images || has_post_thumbnail($postID)) { ?>
				<div id="<?php echo $this->div_id; ?>">
					<ul class='tcc-galleryview'><?php
						if (has_post_thumbnail($postID)) {
							$this->show_galleryview_image(get_post_thumbnail_id());
						}
						foreach($images as $ID=>$image) {
							$this->show_galleryview_image($ID);
						} ?>
					</ul>
				</div><?php
			}
		}
	}

	private function show_galleryview_image($imgID) {
		$info  = wp_get_attachment($imgID);
		$attrs = (empty($info['src']))                ? '' : ' src="'.esc_attr($info['src']).'"';
		$attrs.= (empty($info['alt']))                ? '' : ' alt="'.esc_attr($info['alt']).'"';
		$attrs.= (empty($info['title']))              ? '' : ' title="'.esc_attr($info['title']).'"';
		$attrs.= (empty($info['sizes']['thumbnail'])) ? '' : ' data-frame="'.esc_attr($info['sizes']['thumbnail']).'"';
		$attrs.= (empty($info['description']))        ? '' : ' data-description="'.esc_attr($info['description']).'"'; ?>
		<li>
			<img <?php echo $attrs; ?> />
		</li><?php
	}


}