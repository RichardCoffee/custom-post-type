<?php
/*
 *  File:  clasess/MetaBox/Gallery.php
 *
 */

class TCC_MetaBox_Gallery {

	private $button   = 'Assign/Upload Image'
	private $callback =  null;
	private $context  = 'normal';
	private $nonce    = 'gallery_nonce';
	private $priority = 'high';
	private $slug     = 'gallery_meta_box';
	private $title    = 'Image Gallery';
	private $type     = 'custom_post_type';

	public function __construct($args=array()) {
		$this->button = esc_html__('Assign/Upload Image');
		$this->title  = esc_html__('Image Gallery');
		foreach($args as $prop=>$value) {
			$this->{$prop} = $value;
		}
		$this->nonce = "{$this->type}_gallery_nonce";
		add_action( 'add_meta_boxes_'.$this->type, array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts',       array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'save_post_'.$this->type,      array( $this, 'save_meta_boxes' ) );
	}

	public function add_meta_boxes() {
		add_meta_box( $this->name, $this->title, array($this,'gallery_meta_box'), $this->type, $this->context, $this->priority, $this->callback );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( $screen && ( $screen->post_type==$this->type ) ) {
			wp_enqueue_style('tcc-columns');  #  provides 'section group col span_*_of_*' classes
		}
	}

	public function gallery_meta_box($post) { ?>
		<div id="<?php echo $this->slug; ?>-gallery" class="section group listing-images"><?php
			wp_nonce_field(basename(__FILE__),$this->nonce);
			$images = $this->get_gallery_images($post->ID,true);
			foreach($images as $ID=>$src) { ?>
				<div class="col span_1_of_6 meta-image">
					<span class="dashicons dashicons-trash delete-image"></span>
					<img class="attachment-post-thumbnail" src="<?php echo $src; ?>" data-id="<?php echo $ID ?>">
				</div><?php
			} ?>
		</div>
		<button id='add-<?php echo $this->slug; ?>-image' class="add-gallery-image"><?php echo $this->button; ?></button><?php
	}

	#	http://www.wpbeginner.com/wp-themes/how-to-get-all-post-attachments-in-wordpress-except-for-featured-image/
	public function get_gallery_images($postID,$exclude=false) {
		$images = array();
		if ($postID) {
			$data = array('post_type'      => 'attachment',
			              'posts_per_page' => -1,
			              'post_parent'    => $postID);
			if ($exclude) { $data['exclude'] = get_post_thumbnail_id($postID); }
			$attachments = get_posts($data);
			if ($attachments) {
				foreach ($attachments as $attachment) {
					$image_src = wp_get_attachment_image_src($attachment->ID,'full');
					$images[$attachment->ID] = $image_src[0];
				}
			}
		}
		return $images;
	}

	public function save_meta_boxes($postID) {
		remove_action('save_post_'.$this->type, array($this,'save_meta_boxes')); # prevent recursion
		if (!isset( $_POST[$this->nonce] ) )            return;
		if (!current_user_can( 'edit_post', $postID ) ) return;
		if ( wp_is_post_autosave( $postID ) )           return;
		if ( wp_is_post_revision( $postID ) )           return;
		if (!wp_verify_nonce( $_POST[ $this->nonce ], basename(__FILE__) ) ) return;
log_entry($_POST);
/*
		$verified = true;

		$in_admin = true;

		$incoming = $_POST;

		if (!empty($incoming['prop_images'])) {
			foreach($incoming['prop_images'] as $imageID) {
				if (intval($imageID,10)) wp_update_post(array('ID'=>intval($imageID,10),'post_parent'=>$postID));
			}
		} //*/
	}

}
