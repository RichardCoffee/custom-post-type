<?php
/*
 *  File:  clasess/MetaBox/Gallery.php
 *
 */

defined( 'ABSPATH' ) || exit;

class TCC_MetaBox_Gallery extends TCC_MetaBox_MetaBox {

	protected $button   = 'Assign/Upload Image';
	protected $confirm  = 'Remove this image?';
	protected $div_css  = 'section group gallery-images';
	protected $div_id   = 'post-gallery';
	protected $div_img  = 'col span_1_of_4 meta-image';
	protected $field    = 'tcc_gallery';
	protected $icon     = 'dashicons dashicons-trash delete-image';
	protected $img_css  = 'attachment-post-thumbnail img-responsive';
	protected $m_button = 'Assign Image';
	protected $m_title  = 'Assign/Upload Image';
	protected $slug     = 'gallery_meta_box';


	public function __construct( $args = array() ) {
		$this->button   = esc_html__( 'Assign/Upload Gallery Image', 'tcc-fluid' );
		$this->confirm  = esc_html__( 'Remove this image?',          'tcc-fluid' );
		$this->m_button = esc_html__( 'Assign Image',                'tcc-fluid' );
		$this->m_title  = esc_html__( 'Assign/Upload Gallery Image', 'tcc-fluid' );
		$this->title    = esc_html__( 'Image Gallery',               'tcc-fluid' );
		parent::__construct( $args );
		$this->div_id   = "{$this->type}-gallery";
		$this->nonce    = "{$this->type}_gallery_nonce";
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( $screen && ( $screen->post_type === $this->type ) ) {
			wp_enqueue_style( 'tcc-gallery-css' );
			wp_enqueue_style( 'tcc-columns' );  #  provides 'section group col span_*_of_*' classes
			$data = array(
				'button'  => $this->m_button,
				'confirm' => $this->confirm,
				'div_img' => $this->div_img,
				'icon'    => $this->icon,
				'div_id'  => $this->div_id,
				'img_css' => $this->img_css,
				'field'   => $this->field . '[]',
				'title'   => $this->m_title,
			);
			wp_localize_script( 'tcc-gallery-js', 'tcc_gallery', $data );
			wp_enqueue_script(  'tcc-gallery-js' );
		}
	}

	public function show_meta_box( $post ) {
		wp_nonce_field( basename( __FILE__ ), $this->nonce );
		$this->gallery_meta_box_pretext(); ?>
		<div id="<?php e_esc_attr( $this->div_id ); ?>" class="<?php e_esc_attr( $this->div_css ); ?>"><?php
			$images = $this->get_gallery_images( $post->ID, true );
			foreach( $images as $imgID => $src ) { ?>
				<div class="<?php e_esc_attr( $this->div_img ); ?>">
					<span class="<?php e_esc_attr( $this->icon ); ?>"></span>
					<img class="<?php e_esc_attr( $this->img_css ); ?>" src="<?php e_esc_attr( $src ); ?>" data-id="<?php e_esc_attr( $imgID ); ?>">
				</div><?php
			} ?>
		</div>
		<button id="add-<?php e_esc_attr( $this->div_id ); ?>" type="button"><?php e_esc_html( $this->button ); ?></button><?php
	}

	protected function gallery_meta_box_pretext() { }

	#	http://www.wpbeginner.com/wp-themes/how-to-get-all-post-attachments-in-wordpress-except-for-featured-image/
	public function get_gallery_images( $postID, $exclude = false ) {
		$images = array();
		if ( $postID ) {
			$data = array(
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'post_parent'    => $postID
			);
			if ( $exclude ) {
				$data['exclude'] = get_post_thumbnail_id( $postID );
			}
			$attachments = get_posts( $data );
			if ( $attachments ) {
				usort( $attachments, function( $a, $b ) {
					return ( intval( $a->ID, 10 ) - intval( $b->ID, 10 ) );
				});
				foreach ( $attachments as $attachment ) {
					$image_src = wp_get_attachment_image_src( $attachment->ID, 'full' );
					$images[ $attachment->ID ] = $image_src[0];
				}
			}
		}
		return $images;
	}

	public function save_meta_box( $postID ) {
		if ( ! $this->pre_save_meta_box( $postID, basename( __FILE__ ) ) ) {
			return;	#	Invalid data
		}
		$incoming = $_POST;
		if ( ! empty( $incoming[ $this->field ] ) ) {
			foreach( $incoming[ $this->field ] as $imageID ) {
				$check = intval( $imageID, 10 );
				if ( $check ) {
					wp_update_post( array(
						'ID'          => $check,
						'post_parent' => $postID
					));
				}
			}
		}
		if ( ! empty( $incoming['delete_image'] ) ) {
			foreach( $incoming['delete_image'] as $deleteID ) {
				$check = intval( $deleteID, 10 );
				if ( $check ) {
					$attach = get_post( $check );
					#	Verify parent post
					if ( $attach->post_parent === $postID ) {
						wp_delete_post( $check );
					}
				}
			}
		}
	}

}
