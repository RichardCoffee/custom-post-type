<?php

abstract class TCC_MetaBox_MetaBox {

	protected $add_meta  = null;
	/**
	 *  Callback arguments
			'__block_editor_compatible_meta_box' (boolean) true indicates the metabox can be used with the block editor, default: true
			'__back_compat_meta_box'             {boolean) true indicates the metabox should only be displayed when using the old editor, default: false
	 */
	protected $callback  = [ '__block_editor_compatible_meta_box' => true, '__back_compat_meta_box' => false ];  #  callback arguments - not the callback function!
	protected $context   = 'normal';
	protected $nonce     = 'meta_box_nonce';   # change this!
	protected $priority  = 'high';
	protected $save_meta = null;
	protected $slug      = 'metabox_meta_box'; # change this!
	protected $title     = 'MetaBox Title';
	protected $type      = 'post';

	use TCC_Trait_Attributes;
	use TCC_Trait_Magic;
	use TCC_Trait_ParseArgs;

	abstract function admin_enqueue_scripts();
	abstract function save_meta_box( $post );
	abstract function show_meta_box( $post );

	public function __construct( $args = array() ) {
		$this->parse_args( $args );
		$this->add_meta  = ( $this->add_meta )  ? $this->add_meta  : 'add_meta_boxes_' . $this->type;
		$this->save_meta = ( $this->save_meta ) ? $this->save_meta : 'save_post_' . $this->type;
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 11 );  #  run later
		add_action( $this->add_meta,         [ $this, 'add_meta_box' ] );
		add_action( $this->save_meta,        [ $this, 'save_meta_box' ] );
	}

	public function add_meta_box() {
		add_meta_box( $this->slug, $this->title, [ $this, 'show_meta_box' ], $this->type, $this->context, $this->priority, $this->callback );
	}

	protected function pre_save_meta_box( $postID, $file ) {
		remove_action( $this->save_meta, [ $this, 'save_meta_box' ] ); # prevent recursion
		if ( ! array_key_exists( $this->nonce, $_POST ) ) return false;
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->nonce ] ), $file ) ) return false;
		if ( ! current_user_can( 'edit_post', $postID ) ) return false;
		if ( wp_is_post_autosave( $postID ) )             return false;
		if ( wp_is_post_revision( $postID ) )             return false;
		return true;
	}


}
