<?php

defined( 'ABSPATH' ) || exit;


class TCC_MetaBox_GalleryView extends TCC_MetaBox_Gallery {

	public function __construct( $args = array() ) {
		parent::__construct( $args );
		if ( ! is_admin() ) {
			add_action( 'tcc_after_enqueue', array( $this, 'register_galleryview' ) );
		}
		add_shortcode( 'galleryview', array( $this, 'show_galleryview' ) );
		add_action( 'tcc_gallery_meta_box_pre', array( $this, 'meta_box_pretext' ) );
	}

	/**  Setup  **/

	public function register_galleryview() {
		$version = $this->version();
		wp_register_style( 'tcc-gv-css',          get_theme_file_uri( 'vendors/galleryview/css/jquery.galleryview-3.0-dev.css' ), null, '3.0' );
		wp_register_style( 'tcc-galleryview-css', get_theme_file_uri( 'css/galleryview.css' ), array( 'tcc-gv-css' ), $version );
		wp_register_script('tcc-gv-js',           get_theme_file_uri( 'vendors/galleryview/js/jquery.galleryview-3.0-dev.js' ), array( 'jquery' ), '3.0', true );
		wp_register_script('tcc-gv-easing',       get_theme_file_uri( 'vendors/galleryview/js/jquery.easing.1.3.js' ), array( 'jquery','tcc-gv-js' ), '1.3', true );
		wp_register_script('tcc-gv-timers',       get_theme_file_uri( 'vendors/galleryview/js/jquery.timers-1.2.js' ), array( 'jquery','tcc-gv-js' ), '1.2', true );
		wp_register_script('tcc-galleryview-js',  get_theme_file_uri( 'js/galleryview.js' ), array( 'tcc-gv-easing', 'tcc-gv-timers' ), $version, true );
		$this->enqueue_galleryview();
	}

	public function enqueue_galleryview() {
		global $wp_query;
		if ( ( ! is_admin() ) && $wp_query->is_single ) {
			if ( $wp_query->get('post_type') === $this->type ) {
				wp_enqueue_style( 'tcc-galleryview-css' );
				$data = array( 'div_id' => $this->div_id );
				wp_localize_script( 'tcc-galleryview-js', 'tcc_gallery', $data );
				wp_enqueue_script(  'tcc-galleryview-js' );
			}
		}
	}

	/**  Front end display  **/

	public function show_galleryview($postID=0) {
		$postID = ( $postID ) ? $postID : get_the_ID();
		if ( $postID ) {
			$images = $this->get_gallery_images( $postID, true );
			if ( $images || has_post_thumbnail( $postID ) ) { ?>
				<div id="<?php echo $this->div_id; ?>">
					<ul class='tcc-galleryview'><?php
						if ( has_post_thumbnail( $postID ) ) {
							$this->show_galleryview_image( get_post_thumbnail_id() );
						}
						foreach( $images as $ID => $image ) {
							$this->show_galleryview_image( $ID );
						} ?>
					</ul>
				</div><?php
			}
		}
	}

	private function show_galleryview_image( $imgID ) {
		$info  = wp_get_attachment( $imgID );
		$attrs = ( empty( $info['src'] ) )                ? '' :              ' src="' . esc_attr( $info['src'] ) . '"';
		$attrs.= ( empty( $info['alt'] ) )                ? '' :              ' alt="' . esc_attr( $info['alt'] ) . '"';
		$attrs.= ( empty( $info['title'] ) )              ? '' :            ' title="' . esc_attr( $info['title'] ) . '"';
		$attrs.= ( empty( $info['sizes']['thumbnail'] ) ) ? '' :       ' data-frame="' . esc_attr( $info['sizes']['thumbnail'] ) . '"';
		$attrs.= ( empty( $info['description'] ) )        ? '' : ' data-description="' . esc_attr( $info['description'] ) . '"'; ?>
		<li>
			<img <?php echo $attrs; ?> />
		</li><?php
	}

	/**  Admin meta box  **/

	public function meta_box_pretext() {
		$text = esc_html_x( 'Use the %s shortcode to place the gallery in your post.', 'a wordpress shortcode', 'tcc-fluid' ); ?>
		<p>
			<?php echo sprintf( $text, '<span class="red">[galleryview]</span>' ); ?>
		</p><?php
	}


}
