<?php
/**
 *  Abstract class for working with custom post types.
 *
 * @package Plugin
 * @subpackage Posts
 * @since 20160205
 * @author Richard Coffee
 * @copyright 2009-2018, RTC Enterprises DBA
 * @license GPL-2.0+
 * @link https://github.com/RichardCoffee/custom-post-type
 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Post/Post.php
 */
defined( 'ABSPATH' ) || exit;


abstract class TCC_Post_Post {


	/**  Custom Post Type behaviorial properties  **/

	/**
	 * @since 20160205
	 * @var string  The custom post type slug.
	 */
	protected $type = 'post';
	/**
	 * @since 20160205
	 * @var string  Label to be used for the post type.
	 */
	protected $label = 'Post'; // _x('Custom Post Type', 'singular form', 'textdomain' )
	/**
	 * @since 20160205
	 * @var string  Label for plural references to the post type.
	 */
	protected $plural = 'Posts'; // _x('Custom Post Types', 'plural form', 'textdomain' )
	/**
	 * @since 20160205
	 * @var string  A description for the post type.
	 */
	protected $description = ''; // __('Custom Post Type Title', 'textdomain' )
	/**
	 * @since 20160319
	 * @var bool  Is the post type public?
	 */
	protected $public = true;
	/**
	 * @since 20160319
	 * @var bool
	 */
	protected $show_in_admin_bar = false;
	/**
	 * @since 20160319
	 * @var bool
	 */
	protected $map_meta_cap = true;
	/**
	 * @since 20160319
	 * @var bool  Does the post type have a hierarchy?
	 */
	protected $hierarchical = false;
	/**
	 * @since 20160319
	 * @var bool
	 */
	protected $query_var = false;
	/**
	 * @since 20160322
	 * @var string  Type of archive.
	 */
	protected $has_archive = 'post';


	/**  CPT API settings  **/

	/**
	 * @since 20160217
	 * @var bool  Should the CPT show up in post queries?
	 */
	protected $main_blog = true;
	/**
	 * @since 20170503
	 * @var bool  Add the CPT into the WP REST API.
	 */
	protected $show_in_rest = true;


	/**  CPT user capabilities  **/

	/**
	 * @since 20160319
	 * @var string  User capabilities needed for the post, default is to not create custom capabilities.
	 */
	protected $caps = 'post';
	/**
	 * @since 20160205
	 * @var string  Value of 'admin' will cause only the administrator caps to be updated.
	 * @todo: allow array of roles
	 */
	protected $role = 'normal';


	/**  CPT post list columns  **/

	/**
	 * @since 20160205
	 * @var array  Takes form of array( 'remove' => [], 'add' => [] ) - see docs.
	 */
	protected $columns = null;
	/**
	 * @since 20160329
	 * @var bool  If set to true will add a count column for this CPT to the admin users screen.
	 */
	protected $user_col = false;


	/**  CPT admin menu options  **/

	/**
	 * @since 20160205
	 * @var string  Admin dashboard icon.
	 */
	protected $menu_icon = 'dashicons-admin-post';
	/**
	 * @since 20160205
	 * @var int  Position on admin dashboard.
	 */
	protected $menu_position = 6;


	/**  CPT general options  **/

	/**
	 * @since 20160205
	 * @var bool  Allow comments for CPT.
	 */
	protected $comments = false;
	/**
	 * @since 20161228
	 * @var bool  Signifies support for post formats - only useful if the theme supports it.
	 */
	protected $formats = false;
	/**
	 * @since 20160205
	 * @var array  Defaults to: array( 'slug' => $this->type ) )
	 * @todo: Add method to create taxonomy rewrite rules.
	 */
	protected $rewrite = array();
	/**
	 * @since 20160205
	 * @var bool  Whether to allow editing of taxonomy slugs in admin screen.
	 */
	protected $slug_edit = true;
	/**
	 * @since 20160205
	 * @var array  What post edit metaboxes the CPT supports/uses.
	 */
	protected $supports = array( 'title', 'editor', 'author', 'revisions' );
	/**
	 * @since 20160205
	 * @var array  List of terms to prevent deletion of, example: array( 'taxonomy-slug' => array( 'Term One Name', 'Term Two Name', 'term-three-slug' ) )
	 */
	protected $tax_keep = array();
	/**
	 * @since 20160205
	 * @var array
	 */
	protected $tax_list = array();
	/**
	 * @since 20160205
	 * @var array  Taxonomies for the CPT, passed to register_post_type().
	 * @todo: possible auto call of $this->taxonomy_registration()
	 */
	protected $taxonomies = array( 'post_tag', 'category' );
	/**
	 * @since 20160205
	 * @var bool  Paths to CPT page templates, example: array( 'single' => WP_PLUGIN_DIR . '/plugin_dir/templates/single-{cpt-slug}.php' ).
	 */
	protected $templates = false;
	/**
	 * @since 20161228
	 * @var bool  Indicates support for featured image
	 */
	protected $thumbnail = true;
	/**
	 * @since 20170305
	 * @var array  Contains translation strings for labels and messages, use filter in child to modify.
	 */
	protected $trans_text = array();


	/**  Experimental  **/

	/**
	 * @since 20160328
	 * @var array  Can be used to assign custom suffix for capabilities.  buggy - don't use this, any fix appreciated.
	 */
	protected $cap_suffix = array();
	/**
	 * @since 20160327
	 * @var array  Taxonomy terms to omit from normal queries.
	 * @fixme: not yet fully implemented
	 */
	protected $tax_omit = array();


	/**  Important: Do not set these in the child class  **/

	/**
	 * @since 20160205
	 * @static array  Maintains list of CPTs.
	 */
	protected static $types = array( 'posts' => null );
	/**
	 * @since 20160326
	 * @var bool  If true then a no deletion policy will be implemented on builtin taxonomies assigned to this CPT.
	 * @fixme:  this needs to be handled differently
	 */
	private $cpt_nodelete = false;
	/**
	 * @since 20160205
	 * @var array  Used in $this->taxonomy_registration( $args ), automatically sets cpt_nodelete to true.
	 */
	private $nodelete = array();

	/**
	 * @since 20191028
	 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Trait/Attributes.php
	 */
	use TCC_Trait_Attributes;
	/**
	 * @since 20170304
	 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Trait/Logging.php
	 */
	use TCC_Trait_Logging;
	/**
	 * @since 20170305
	 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Trait/Magic.php
	 */
	use TCC_Trait_Magic;
	/**
	 * @since 20180701
	 * @link https://github.com/RichardCoffee/custom-post-type/blob/master/classes/Trait/ParseArgs.php
	 */
	use TCC_Trait_ParseArgs;


	/**
	 *  Constructer method, will accept argument array.
	 *
	 * @since 20160205
	 * @param array  Associative array for property values.
	 */
	protected function __construct( array $data ) {
		if ( ( array_key_exists( 'type', $data ) && ( ! post_type_exists( $data['type'] ) ) ) || ( $this->type && ( ! post_type_exists( $this->type ) ) ) ) {

			//  Experimental - this will be replaced at some point.
			if ( array_key_exists( 'nodelete', $data ) ) {
				$this->cpt_nodelete = true;  //  FIXME
			}
			unset( $data['cpt_nodelete'], $data['nodelete'] );  //  FIXME

			// Import data arguments.
			$this->parse_args( $data );

			// Add actions and filters
			$this->add_actions();
			$this->add_filters();

			// Force value for cpt type.
			$this->type = ( empty( $this->type ) ) ? sanitize_title( $this->label ) : sanitize_title( $this->type );
			$this->has_archive = ( in_array( $this->has_archive, [ 'post' ] ) ) ? $this->type : $this->has_archive;

			// What will the cpt support?
			if ( $this->comments )  {
				$this->supports[] = 'comments';
			}
			if ( $this->thumbnail ) {
				$this->supports[] = 'thumbnail';
			}
			if ( $this->formats && current_theme_supports( 'post-formats' ) ) {
				$this->supports[] = 'post-formats';
			}
			// Add nodelete code for builtin taxonomies.
			if ( $this->cpt_nodelete ) {
				$this->add_builtins();
			}
			// Add/Remove cpt screen columns.
			if ( $this->columns ) {
				$this->setup_columns();
			}
			if ( ! array_key_exists( $this->type, static::$types ) ) {
				static::$types[ $this->type ] = $this;
			}
		}
	}

	/**
	 *  Remove CPT type from static array.
	 *
	 * @since 20160327
	 */
	public function __destruct() {
		unset( static::$types[ $this->type ] );
	}


	/**  Actions and filters  **/

	/**
	 *  Setup actions.
	 *
	 * @since 20200422
	 */
	protected function add_actions() {
		add_action( 'init',                         [ $this, 'create_post_type' ] );
		add_action( "add_meta_boxes_{$this->type}", [ $this, 'check_meta_boxes' ] );
		// Deny the user the ability to edit taxonomy term slugs.
		if ( ! $this->slug_edit ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'stop_slug_edit' ] );
		}
		// Add count column to users screen.
		if ( $this->user_col ) {
			add_action( 'manage_users_columns',       [ $this, 'manage_users_columns' ] );
			add_action( 'manage_users_custom_column', [ $this, 'manage_users_custom_column' ], 10, 3 );
		}
	}

	/**
	 *  Setup filters.
	 *
	 * @since 20200422
	 */
	protected function add_filters() {
		add_filter( 'comments_open',         [ $this, 'comments_limit' ], 10, 2 );
#		add_filter( 'map_meta_cap',          [ $this, 'map_meta_cap' ],   10, 4 );
		add_filter( 'pings_open',            [ $this, 'comments_limit' ], 10, 2 );
		add_filter( 'post_updated_messages', [ $this, 'post_type_messages' ] );
		add_filter( 'wpseo_metabox_prio',    [ $this, 'wpseo_metabox_prio' ] );
		// Add custom role
		if ( ! in_array( $this->role, [ 'normal' ] ) ) {
			add_filter( "{$this->type}_add_roles",    [ $this, 'add_user_role' ] );
			add_filter( "{$this->type}_author_roles", [ $this, 'add_user_role' ] );
		}
		// Force cpt in main wp query.
		if ( $this->main_blog ) {
			add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ), 5 );
		}
		// Stop posts with tax term from showing in any query.
		if ( $this->tax_omit ) {
			add_filter( 'pre_get_posts', array( $this, 'omit_get_posts' ), 6 );
		}
		// Add CPT templates
		if ( $this->templates ) {
			add_filter( 'template_include', array( $this, 'assign_template' ) );
		}
		// Sortable columns.
		if ( is_admin() ) {
			add_filter( 'pre_get_posts', array( $this, 'sort_get_posts' ) );
		}
	}

	/**
	 *  Filter for wpseo plugin that changes the priority on the plugin's metabox on the edit post page.
	 *
	 * @since 20170130
	 * @param  string
	 * @return string
	 */
	public function wpseo_metabox_prio( $arg ) {
		return 'low';
	}


	/**  Text functions  **/

	/**
	 *  Create a translated text array for use in CPT and Taxonomy labels.
	 *
	 * @since 20160205
	 * @return array  Translated text.
	 */
	protected function translated_text() {
		static $text;
		if ( empty( $text ) ) {
			$text =  array(
				'404'       => _x( 'No %s found',          'placeholder is plural form',   'tcc-plugin' ),
				'add'       => _x( 'Add New %s',           'placeheader is singular form', 'tcc-plugin' ),
				'add_rem'   => _x( 'Add or remove %s',     'placeholder is plural form',   'tcc-plugin' ),
				'all'       => _x( 'All %s',               'placeholder is plural form',   'tcc-plugin' ),
				'archive'   => _x( '%s Archive',           'placeholder is singular form', 'tcc-plugin' ),
				'attributes'=> _x( '%s Attributes',        'placeholder is singular form', 'tcc-plugin' ),
				'commas'    => _x( 'Separate %s with commas', 'placeholder is plural form','tcc-plugin' ),
				'edit_p'    => _x( 'Edit %s',              'placeholder is plural form',   'tcc-plugin' ),
				'edit_s'    => _x( 'Edit %s',              'placeholder is singular form', 'tcc-plugin' ),
				'feature'   => _x( '%s Image',             'placeholder is singular form', 'tcc-plugin' ),
				'feat_rem'  => _x( 'Remove %s image',      'placeholder is singular form', 'tcc-plugin' ),
				'feat_set'  => _x( 'Set %s image',         'placeholder is singular form', 'tcc-plugin' ),
				'feat_use'  => _x( 'Use as %s image',      'placeholder is singular form', 'tcc-plugin' ),
				'filter'    => _x( 'Filter %s list',       'placeholder is plural form',   'tcc-plugin' ),
				'insert'    => _x( 'Insert into %s',       'placeholder is singular form', 'tcc-plugin' ),
				'list'      => _x( '%s list',              'placeholder is singular form', 'tcc-plugin' ),
				'list_nav'  => _x( '%s list navigation',   'placeholder is plural form',   'tcc-plugin' ),
				'new'       => _x( 'New %s',               'placeholder is singular form', 'tcc-plugin' ),
				'none'      => _x( 'No %s',                'placeholder is plural form',   'tcc-plugin' ),
				'parent'    => _x( 'Parent %s',            'placeholder is singular form', 'tcc-plugin' ),
				'popular'   => _x( 'Popular %s',           'placeholder is plural form',   'tcc-plugin' ),
				'search'    => _x( 'Search %s',            'placeholder is plural form',   'tcc-plugin' ),
				'trash'     => _x( 'No %s found in trash', 'placeholder is plural form',   'tcc-plugin' ),
				'update'    => _x( 'Update %s',            'placeholder is singular form', 'tcc-plugin' ),
				'upload'    => _x( 'Uploaded to this %s',  'placeholder is singular form', 'tcc-plugin' ),
				'used'      => _x( 'Choose from the most used %s', 'placeholder is plural form', 'tcc-plugin' ),
				'view_p'    => _x( 'View %s',              'placeholder is plural form',   'tcc-plugin' ),
				'view_s'    => _x( 'View %s',              'placeholder is singular form', 'tcc-plugin' ),
				'messages'  => array(
					'custom_u' => __( 'Custom field updated.', 'tcc-plugin'),
					'custom_d' => __( 'Custom field deleted.', 'tcc-plugin'),
					'draft'    => _x( '%s draft updated.', 'placeholder is singular form', 'tcc-plugin' ),
					'preview'  => _x( 'Preview %s',        'placeholder is singular form', 'tcc-plugin' ),
					'publish'  => _x( '%s published.',     'placeholder is singular form', 'tcc-plugin' ),
					'revision' => _x( '%1$s restored to revision from %2$s', '1: label in singular form, 2: date and time of the revision', 'tcc-plugin' ),
					'saved'    => _x( '%s saved.',         'placeholder is singular form', 'tcc-plugin' ),
					'schedule' => _x( '%1$s publication scheduled for %2$s', '1: label in singular form, 2: formatted date string', 'tcc-plugin' ),
					'submit'   => _x( '%s submitted.',     'placeholder is singular form', 'tcc-plugin' ),
					'update'   => _x( '%s updated.',       'placeholder is singular form', 'tcc-plugin' )
				)
			);
			$text = apply_filters( "fluid_translated_text_{$this->type}", $text );
		}
		return $text;
	}

	/* Create Post Type functions */

	/**
	 *  Create the post type.
	 *
	 * @since 20160205
	 */
	public function create_post_type() {
		// Check rewrite slug.
		if ( empty( $this->rewrite ) || empty( $this->rewrite['slug'] ) ) {
			$this->rewrite['slug'] = $this->type;
		}
		// Build argument array.
		$args = array(
			'label'             => $this->plural,
			'labels'            => $this->post_type_labels(),
			'description'       => $this->description,
			'public'            => $this->public,
			'show_in_admin_bar' => $this->show_in_admin_bar,
			'menu_position'     => $this->menu_position,
			'menu_icon'         => $this->menu_icon,
			'map_meta_cap'      => $this->map_meta_cap,
			'hierarchical'      => $this->hierarchical,
			'query_var'         => $this->query_var,
			'show_in_rest'      => $this->show_in_rest,
			'supports'          => $this->supports,
			'taxonomies'        => $this->taxonomies,
			'has_archive'       => $this->has_archive,
			'rewrite'           => $this->rewrite,
		);
		//  Handle capability changes here.
		if ( ! in_array( $this->caps, [ 'post' ] ) ) {
#			$args['capability_type'] = $this->type;
#			$args['capabilities']    = $this->map_capabilities();
		}
		// Make last minute changes here
		$args = apply_filters( "fluid_register_post_{$this->type}", $args );
		// Register the CPT.
		register_post_type( $this->type, $args );
		// Register taxonomies using this action
		do_action( "fluid_custom_post_{$this->type}" );
		// Get the CPT object
		$cpt = get_post_type_object( $this->type );
		if ( $cpt->map_meta_cap ) {
			add_action( 'admin_init', [ $this, 'add_caps' ] );
		}
}

	/**
	 *  Create labels for CPT.
	 *
	 * @since 20160205
	 * @return array
	 */
	protected function post_type_labels() {
		$phrases = $this->translated_text();
		$labels  = array (
			'name'          => $this->plural,
			'singular_name' => $this->label,
			'add_new'       => sprintf( $phrases['add'],    $this->label ),
			'add_new_item'  => sprintf( $phrases['add'],    $this->label ),
			'edit_item'     => sprintf( $phrases['edit_s'], $this->label ),
			'new_item'      => sprintf( $phrases['new'],    $this->label ),
			'view_item'     => sprintf( $phrases['view_s'], $this->label ),
			'search_items'  => sprintf( $phrases['search'], $this->plural ),
			'not_found'     => sprintf( $phrases['404'],    $this->plural ),
			'not_found_in_trash'    => sprintf( $phrases['trash'],   $this->plural ),
			'parent_item_colon'     => sprintf( $phrases['parent'],  $this->label ) . ':',
			'all_items'             => sprintf( $phrases['all'],     $this->plural ),
			'archives'              => sprintf( $phrases['all'],     $this->plural ),
			'insert_into_item'      => sprintf( $phrases['insert'],  $this->label ),
			'uploaded_to_this_item' => sprintf( $phrases['upload'],  $this->label ),
			'featured_image'        => sprintf( $phrases['feature'], $this->label ),
			'set_featured_image'    => sprintf( $phrases['feat_set'], strtolower( $this->label ) ),
			'remove_featured_image' => sprintf( $phrases['feat_rem'], strtolower( $this->label ) ),
			'use_featured_image'    => sprintf( $phrases['feat_use'], strtolower( $this->label ) ),
			'menu_name'             => $this->plural,
			'filter_items_list'     => sprintf( $phrases['filter'],   $this->plural ),
			'items_list_navigation' => sprintf( $phrases['list_nav'], $this->plural ),
			'items_list'    => sprintf( $phrases['list'],       $this->label ),
			'edit'          => sprintf( $phrases['edit_p'],     $this->plural ),
			'view'          => sprintf( $phrases['view_p'],     $this->plural ),
			'items_archive' => sprintf( $phrases['archive'],    $this->label ),
			'view_items'    => sprintf( $phrases['view_p'],     $this->plural ),
			'attributes'    => sprintf( $phrases['attributes'], $this->plural ),
		);
		return apply_filters( "fluid_post_labels_{$this->type}", $labels );
	}

	/**
	 *  Create messages for the CPT.
	 *
	 * @since 20160205
	 * @param  array  Current post messages.
	 * @return array  New CPT messages.
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 * @link http://thomasmaxson.com/update-messages-for-custom-post-types/
	 */
	public function post_type_messages( $messages ) {
		$phrases = $this->translated_text();
		$strings = $phrases['messages'];
		$view_link = $preview_link = $formed_date = '';
		if ( $post = get_post() ) { #  get_post() call should always succeed when editing a post
			$view_text      = sprintf( $phrases['view_s'], $this->label );
			$preview_text   = sprintf( $strings['preview'], $this->label );
			$link_tag_html  = '  ' . $this->tag( 'a', [ 'href' => '%s', 'target' => sanitize_title( $post->post_title ) ] );
			$view_link      = sprintf( $link_tag_html, esc_url( get_permalink( $post->ID ) ) ) . $view_text . '</a>';
			$preview_link   = sprintf( $link_tag_html, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ) . $preview_text . '</a>';
			$formed_date    = date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) );
		}
		$messages[ $this->type ] = array(
			1  => sprintf( $strings['update'],   $this->label ) . $view_link,
			2  => $strings['custom_u'],
			3  => $strings['custom_d'],
			4  => sprintf( $strings['update'],   $this->label ),
			5  => array_key_exists( 'revision', $_GET ) ? sprintf( $strings['revision'], $this->label, wp_post_revision_title( intval( $_GET['revision'] ), false ) ) : false,
			6  => sprintf( $strings['publish'],  $this->label ) . $view_link,
			7  => sprintf( $strings['saved'],    $this->label ),
			8  => sprintf( $strings['submit'],   $this->label ) . $preview_link,
			9  => sprintf( $strings['schedule'], $this->label,  $formed_date ) . $preview_link,
			10 => sprintf( $strings['draft'],    $this->label ) . $preview_link
		);
		return apply_filters( 'fluid_post_type_messages', $messages );
	}


	/*  Capabilities  */

	/**
	 *  Add CPT role.
	 *
	 * @since 20200422
	 * @param array $roles
	 * @return array
	 */
	public function add_user_role( $roles ) {
		$roles[] = $this->role;
		return array_unique( $roles );
	}

	/**
	 *  Map the basic capability strings.
	 *
	 * @since 20170130
	 * @return array  Mapped capabilities.
	 */
	protected function map_basic_caps() {
		return array (
			'sing' => ( empty( $this->capability_type[0] ) ) ? sanitize_title( $this->label )  : $this->capability_type[0],
			'plur' => ( empty( $this->capability_type[1] ) ) ? sanitize_title( $this->plural ) : $this->capability_type[1]
		);
	}

	/**
	 *  Map the user capabilities.
	 *
	 * @since 20170130
	 * @link http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
	 */
	protected function map_capabilities( ) {
		$base = $this->map_basic_caps();
		extract( $base ); // Extracts as $sing and $plur.
		$caps = array(
			'publish_posts'       => 'publish_' . $plur,
			'edit_posts'          => 'edit_' . $plur,
			'edit_others_posts'   => 'edit_others_' . $plur,
			'delete_posts'        => 'delete_' . $plur,
			'delete_others_posts' => 'delete_others_' . $plur,
			'read_private_posts'  => 'read_private_' . $plur,
			'edit_post'           => 'edit_' . $sing,
			'delete_post'         => 'delete_' . $sing,
			'read_post'           => 'read_' . $sing,
			);
		return apply_filters( "fluid_map_capabilities_{$this->type}", $caps );
	}

	/**
	 *  Map the meta capabilities.
	 *
	 * @since 20170130
	 * @param array  $caps     Array of the user's capabilities.
	 * @param string $cap      Requested capability name.
	 * @param int    $user_id  The user ID.
	 * @param array  $args     Adds the context to the cap. Typically the object ID.
	 * @return array           Remapped user capabilities.
	 * @link http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( ! in_array( $this->caps, [ 'post' ] ) ) {
			$base = $this->map_capabilities();
			if ( in_array( $cap, array( $base['edit_post'], $base['delete_post'], $base['read_post'] ) ) ) {
				$post = get_post( $args[0] );
				$post_type = get_post_type_object( $post->post_type );
				// Set an empty array for the caps.
				$caps = array();
			}
			switch( $cap ) {
				case $base['edit_post'];
					if ( $user_id === $post->post_author ) {
						$caps[] = $post_type->cap->edit_posts;
					} else {
						$caps[] = $post_type->cap->edit_others_posts;
					}
					break;
				case $base['delete_post'];
					if ( $user_id == $post->post_author ) {
						$caps[] = $post_type->cap->delete_posts;
					} else {
						$caps[] = $post_type->cap->delete_others_posts;
					}
					break;
				case $base['read_post'];
					if ( ! in_array( $post->post_status, [ 'private' ] ) ) {
						$caps[] = 'read';
					} else if ( $user_id === $post->post_author ) {
						$caps[] = 'read';
					} else {
						$caps[] = $post_type->cap->read_private_posts;
					}
					break;
				default;
			}
		}
		return apply_filters( "fluid_map_meta_cap_{$this->type}", $caps );
	}

	/**
	 *  Add CPT role for capabilities.
	 *
	 * @since 20160205
	 * @link http://stackoverflow.com/questions/18324883/wordpress-custom-post-type-capabilities-admin-cant-edit-post-type
	 */
	public function add_caps() {
		$all_roles    = apply_filters( "{$this->type}_add_roles",    [ 'contributor', 'author', 'editor', 'administrator' ] );
		$author_roles = apply_filters( "{$this->type}_author_roles", [ 'author', 'editor', 'administrator' ] );
		$editor_roles = apply_filters( "{$this->type}_editor_roles", [ 'editor', 'administrator' ] );
		if ( in_array( $this->role, [ 'admin' ] ) ) {
			$roles = array( 'administrator' );
		}
		foreach( $all_roles as $role ) {
			$this->process_caps( $role, $author_roles, $editor_roles );
		}
	}

	/**
	 *  Add capabilities for each role.
	 *
	 * @since 20160205
	 * @param string $name          Name of role.
	 * @param array  $author_roles  Roles for authors.
	 * @param array  $editor_roles  Roles for editors.
	 */
	private function process_caps( $name, $author_roles, $editor_roles ) {
		$role = get_role( $name );
		if ( $role instanceof WP_Role ) {
			$base = $this->map_basic_caps();
			extract( $base );  #  extracts as $sing and $plur
#			$caps = array( "delete_$sing", "edit_$sing", "read_$sing", "delete_$plur", "edit_$plur");
			$caps = array( "delete_$plur", "edit_$plur");
			$auth = array( "delete_published_$plur", "edit_published_$plur", "publish_$plur");
			$edit = array( "delete_others_$plur", "delete_private_$plur", "edit_others_$plur", "edit_private_$plur", "read_private_$plur" );
			if ( in_array( $role, $author_roles ) ) {
				$caps = array_unique( array_merge( $caps, $auth ) );
			}
			if ( in_array( $name, $editor_roles ) ) {
				$caps = array_unique( array_merge( $caps, $edit ) );
			}
			foreach( $caps as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}


	/* Taxonomy functions */

	/**
	 *  Create taxonomy labels.
	 *
	 * @since 20160205
	 * @param string $single  Single label.
	 * @param string $plural  Plural label.
	 * @return array          Taxonomy labels.
	 */
	protected function taxonomy_labels( $single, $plural ) {
		// Note: do not use a static here.
		$phrases = $this->translated_text();
		$labels  = array(
			'name'              => $plural,
			'singular_name'     => $single,
			'search_items'      => sprintf( $phrases['search'],  $plural ),
			'popular_items'     => sprintf( $phrases['popular'], $plural ),
			'all_items'         => sprintf( $phrases['all'],     $plural ),
			'parent_item'       => sprintf( $phrases['parent'],  $single ),
			'parent_item_colon' => sprintf( $phrases['parent'],  $single ) . ':',
			'edit_item'         => sprintf( $phrases['edit_s'],  $single ),
			'view_item'         => sprintf( $phrases['view_s'],  $single ),
			'update_item'       => sprintf( $phrases['update'],  $single ),
			'add_new_item'      => sprintf( $phrases['add'],     $single ),
			'new_item_name'     => sprintf( $phrases['new'],     $single ),
			'separate_items_with_commas' => sprintf( $phrases['commas'],  $plural ),
			'add_or_remove_items'        => sprintf( $phrases['add_rem'], $plural ),
			'choose_from_most_used'      => sprintf( $phrases['used'],    $plural ),
			'not_found'                  => sprintf( $phrases['404'],     $plural ),
			'menu_name'                  => $plural,
			'no_terms'                   => sprintf( $phrases['none'],     $plural ),
			'items_list_navigation'      => sprintf( $phrases['list_nav'], $plural ),
			'items_list'                 => sprintf( $phrases['list'],     $plural )
		);
		return apply_filters( "fluid_taxonomy_labels_{$this->type}", $labels, $single, $plural );
	}

	/**
	 *  Register a taxonomy.
	 *
	 * @since 20160205
	 * @param array $t  Arguments for taxonomy registration.
	 * @link https://codex.wordpress.org/Function_Reference/register_taxonomy
	 * @link https://make.wordpress.org/plugins/2012/06/07/rewrite-endpoints-api/
	 * @fixme:  overly complicated - simplify
	 */
	protected function taxonomy_registration( $t ) {
		//  Taxonomy slug is required.
		if ( ( ! array_key_exists( 'tax', $t ) ) || empty( $t['tax'] ) ) return;
		$tax = preg_replace( '/[^a-z_]/', '', $t['tax'] );
		// Check for pre-existing taxonomy.
		if ( taxonomy_exists( $tax ) ) return;
		//  Both single and plural labels are required.
		if ( ( ! array_key_exists( 'label',  $t ) ) || empty( $t['label']  ) ) return;
		if ( ( ! array_key_exists( 'plural', $t ) ) || empty( $t['plural'] ) ) return;
		// Create labels.
		$labels = $this->taxonomy_labels( $t['label'], $t['plural'] );
		$args   = array(
			'hierarchical' => ( array_key_exists( 'hierarchical',  $t ) ) ? $t['hierarchical'] : true,
			'label'   => $single,
			'labels'  => apply_filters( "fluid_taxonomy_labels_{$this->type}_$tax", $labels ),
			'public'  => ( array_key_exists( 'public',  $t ) ) ? $t['public'] : true,
			'rewrite' => ( array_key_exists( 'rewrite', $t ) ) ? [ 'slug' => $t['rewrite'] ] : [ 'slug' => $tax ],
		);
		if ( array_key_exists( 'args', $t ) ) {
			$args = array_merge_recursive( $args, $t['args'] );
		}
		$args = apply_filters( "fluid_register_taxonomy_$tax", $args, $args );
		// Ensure pretty permalinks is enabled.
		if ( array_key_exists( 'ep_mask', $args['rewrite'] ) ) {
			if ( ! ( $args['rewrite']['ep_mask'] & EP_PERMALINK ) ) {
				$args['rewrite']['ep_mask'] |= EP_PERMALINK;
			}
		} else {
			$args['rewrite']['ep_mask'] = EP_PERMALINK;
		}
		// Create the taxonomy.
		register_taxonomy( $tax, $this->type, $args );
		if ( taxonomy_exists( $tax ) ) {
			if ( ! in_array( $tax, $this->tax_list ) ) {
				$this->tax_list[] = $tax;
			}
			register_taxonomy_for_object_type( $tax, $this->type );
			$current = get_terms( $tax, 'hide_empty=0' );
			// Add new terms only if no terms currently exist.
			if ( empty( $current ) ) {
				$new = array();
				if ( array_key_exists( 'terms', $t ) ) {
					$new = $terms;
				} else {
					$func = ( array_key_exists( 'create', $t ) ) ? $t['create'] : [ $this, "create_$tax" ];
					if ( is_callable( $func ) ) {
						$new = call_user_func( $func );
					}
				}
				if ( $new ) {
					foreach( $new as $key => $term ) { // TODO:  provide for description
						if ( is_numeric( $key ) ) {
							wp_insert_term( $term, $tax );
						} else {
							wp_insert_term( $term, $tax, [ 'slug' => $key ] );
						}
					}
				}
			}
			if ( array_key_exists( 'nodelete', $t ) ) {
				$this->nodelete[] = $tax;
				if ( ! has_action( 'admin_enqueue_scripts', [ $this, 'stop_term_deletion' ] ) ) {
					add_action( 'admin_enqueue_scripts', [ $this, 'stop_term_deletion' ] );
				}
			}
			if ( array_key_exists( 'omit', $t ) ) {
				$this->omit[ $tax ] = ( empty( $this->omit[ $tax ] ) ) ? $t['omit'] : array_merge( $this->omit[ $tax ], $t['omit'] );
				if ( ! has_filter( 'pre_get_posts', [ $this, 'omit_get_posts' ], 6 ) ) {
					add_filter( 'pre_get_posts', [ $this, 'omit_get_posts' ], 6 );
				}
			}
			// This has to be an anonymous closure because of the $tax variable.
			//   Okay, not true, but my attempt at the other way got really messy,
			//   and was very buggy.  I never could get it work properly within my
			//   time constraint.  If you want it changed, put in a pull request on it.  Please.
			add_filter( "cpt_{$this->type}_pre_get_posts", function( $query ) use ( $tax ) {
				if ( $query->is_search() ) {
					$value = $query->get( $tax );
					if ( $value ) {
						$args = ( $tq = $query->get( 'tax_query' ) ) ? $tq : array();
						$args[] = array(
							'taxonomy' => $tax,
							'field'    => 'slug',
							'terms'    => $value,
						);
						$query->set( 'tax_query', $args );
					}
				}
			}, 11 );
		}
	}

	/**
	 *  Add builtins to the nodelete list.
	 *
	 * @since 20160326
	 */
	private function add_builtins() {
		$check = array( 'post_tag', 'category' );
		foreach( $check as $tax ) {
			$this->nodelete[] = $tax;
		}
		if ( ! has_action( 'admin_enqueue_scripts', [ $this, 'stop_term_deletion' ] ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'stop_term_deletion' ] );
		}
	}

	/**
	 *  Get a label string for a taxonomy.
	 *
	 * @since 20170202
	 * @param string $slug   Taxonomy slug.
	 * @param string $label  Slug of string to retrieve.
	 * @return string        String requested or an error message.
	 */
	public function get_taxonomy_label( $slug, $label ) {
		if ( $slug && taxonomy_exists( $slug ) ) {
			$labels = get_taxonomy( $slug )->labels;
			if ( empty( $labels ) || empty( $labels->$label ) ) {
				return "'$slug' label '$label' not found";
			}
			return $labels->$label;
		}
		return "Taxonomy '$slug' not found";
	}

	/**
	 *  Enqueue the javascript file to prevent slug editing.
	 *
	 * @since 20160205
	 */
	public function stop_slug_edit() {
		$screen = get_current_screen();
		if ( $screen && in_array( $screen->base, [ 'edit-tags' ] ) ) {
			if ( class_exists( 'TCC_Plugin_Paths' ) ) {
				$js_file_uri = TCC_Plugin_Paths::instance()->get_plugin_file_uri( 'js/slug_noedit.js' );
			} else {
				$js_file_uri = plugins_url( '../../js/slug_noedit.js', __FILE__ );
			}
			$js_file_uri = apply_filters( 'fluid_slug_noedit_file_uri', $js_file_uri );
			if ( $js_file_uri ) {
				wp_enqueue_script( 'slug_noedit', $js_file_uri, array( 'jquery' ), null, true );
			}
		}
	}


	/*  Term functions  */

	/**
	 *  Creates the non-deletion term list, and loads the javascript to make it happen.
	 *
	 * @since 20160205
	 */
	public function stop_term_deletion() {
		$screen = get_current_screen();
		if ( in_array( $screen->base, [ 'edit-tags' ] ) && in_array( $screen->taxonomy, $this->nodelete ) ) {
			$keep_list = array();
			if ( ! empty( $this->tax_keep[ $screen->taxonomy ] ) ) {
				foreach( $this->tax_keep[ $screen->taxonomy ] as $term ) {
					$keep_list[] = $this->get_term_id( $term, $screen->taxonomy );
				}
			}
			$term_list = get_terms( $screen->taxonomy, 'hide_empty=1' );
			if ( $term_list ) {
				foreach( $term_list as $term ) {
					$keep_list[] = $term->term_id;
				}
			}
			if ( $keep_list ) {
				$keep_list = array_unique( $keep_list );
				$this->logg( $keep_list );
				if ( class_exists( 'TCC_Plugin_Paths' ) ) {
					$js_file_uri = TCC_Plugin_Paths::instance()->get_plugin_file_uri( 'js/tax_nodelete.js' );
				} else {
					$js_file_uri = plugins_url( '../../js/tax_nodelete.js', __FILE__ );
				}
				$js_file_uri = apply_filters( 'fluid_tax_nodelete_file_uri', $js_file_uri );
				if ( $js_file_uri ) {
					wp_register_script( 'tax_nodelete', $js_file_uri, array( 'jquery' ), null, true );
					wp_localize_script( 'tax_nodelete', 'term_list', $keep_list );
					wp_enqueue_script( 'tax_nodelete' );
				}
			}
		}
	}

	/**
	 *  Get a term id.
	 *
	 * @since 20160327
	 * @param string $term  Term slug or name.
	 * @param string $tax   Taxonomy slug.
	 * @return int|false    Term ID.
	 */
	private function get_term_id( $term, $tax ) {
		if ( $term === sanitize_title( $term ) ) {
			$object = get_term_by( 'slug', $term, $tax );
		} else {
			$object = get_term_by( 'name', $term, $tax );
		}
		return ( $object ) ? $object->term_id : false;
	}


	/*  Post Admin Column functions/filters  */

	/**  CPT screen  **/

	/**
	 *  Setup for the screen columns.
	 *
	 * @since 20160205
	 * @link https://yoast.com/dev-blog/custom-post-type-snippets/
	 */
	private function setup_columns() {
		if ( array_key_exists( 'remove', $this->columns ) ) {
			add_filter( "manage_edit-{$this->type}_columns", [ $this, 'remove_custom_post_columns' ] );
		}
		if ( array_key_exists( 'add', $this->columns ) ) {
			add_filter( "manage_edit-{$this->type}_columns", [ $this, 'add_custom_post_columns' ] );
		}
		if ( array_key_exists( 'sort', $this->columns ) ) {
			add_filter( "manage_edit-{$this->type}_sortable_columns", [ $this, 'add_custom_post_columns_sortable' ] );
		}
		if ( array_key_exists( 'callback', $this->columns ) ) {
			if ( is_callable( $this->columns['callback'] ) ) {
				add_action( 'manage_posts_custom_column', $this->columns['callback'], 10, 2 );
			} else {
				$this->logg( 'columns[callback] function name not callable', $this->columns['callback'] );
			}
		} else {
			add_filter( 'manage_posts_custom_column', [ $this, 'display_custom_post_column' ], 10, 2 );
		}
	}

	/**
	 *  Remove specified columns from the post list screen.
	 *
	 * @since 20160205
	 * @param  array $columns  Columns to be shown on the screen.
	 * @return array           Modified column array.
	 */
	public function remove_custom_post_columns( $columns ) {
		foreach( $this->columns['remove'] as $no_col ) {
			if ( array_key_exists( $no_col, $columns ) ) {
				unset( $columns[ $no_col ] );
			}
		}
		return $columns;
	} //*/

	/**
	 *  Add columns to the post list screen.
	 *
	 * @since 20160205
	 * @param  array $columns  Columns to be shown on the screen.
	 * @return array           Modified column array.
	 */
	public function add_custom_post_columns( $columns ) {
		$place = 'title';
		foreach( $this->columns['add'] as $key => $col ) {
			if ( ! array_key_exists( $key, $columns ) ) {
				$columns = array_insert_after( $place, $columns, $key, $col );
				$place   = $key;
			}
		}
		return $columns;
	} //*/

	/**
	 *  Insert a sortable column.
	 *
	 * @since 20161203
	 * @param  array $columns  Columns to be shown on the screen.
	 * @return array           Modified column array.
	 */
	public function add_custom_post_columns_sortable( $columns ) {
		$place = 'title';
		foreach( $this->columns['add'] as $key => $col ) {
			if ( ! in_array( $key, $this->columns['sort'] ) ) {
				continue;
			}
			if ( ! array_key_exists( $key, $columns ) ) {
				$columns = array_insert_after( $place, $columns, $key, $key );
				$place   = $key;
			}
		}
		return $columns;
	}

	/**
	 *  Provides changes to query to sort the posts.
	 *
	 * @since 20161203
	 * @param WP_Query $query  The query to alter.
	 */
	public function sort_get_posts( $query ) {
		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && in_array( $screen->id, [ "edit-{$this->type}" ] ) ) {
				$orderby = $query->get( 'orderby');
				if ( array_key_exists( 'sort', $this->columns ) && in_array( $orderby, $this->columns['sort'] ) ) {
					$query->set( 'meta_key', $orderby );
					$query->set( 'orderby', 'meta_value' );
				}
			}
		}
	}

	/**
	 *  Display a custom column on posts list
	 *
	 *  css class: .column-{$column}
	 *
	 * @since 20160205
	 * @param string $column   Column slug.
	 * @param int    $post_id  ID of the post.
	 * @link http://wordpress.stackexchange.com/questions/33885/style-custom-columns-in-admin-panels-especially-to-adjust-column-cell-widths
	 */
	public function display_custom_post_column( $column, $post_id ) {
		if ( array_key_exists( $column, $this->columns['add'] ) ) {
			$term   = get_post_meta( $post_id, $column, true );
			$object = get_term_by( 'slug', $term, $column );
			if ( $object ) {
				echo esc_html( $object->name );
			} else {
				echo '--';
			}
		}
	}


	/**  Users screen  **/

	/**
	 *  Add column header.
	 *
	 * @since 20160327
	 * @param  array $headers  Column headers.
	 * @return array           Modified column headers.
	 * @link http://wordpress.stackexchange.com/questions/3233/showing-users-post-counts-by-custom-post-type-in-the-admins-user-list
	 * @link https://gist.github.com/mikeschinkel/643240
	 * @link http://www.wpcustoms.net/snippets/post-count-users-custom-post-type/
	 * @todo get WP to add the num css class
	 */
	public function manage_users_columns( $headers ) {
		$index = "{$this->type} num";
		$headers[ $index ] = $this->plural;
		return $headers;
	}

	/**
	 *  Displays the user entry.
	 *
	 * @since 20160327
	 * @param string $column
	 * @param string $column_name
	 * @param int    $user_id
	 * @todo get WP to add the num css class
	 */
	public function manage_users_custom_column( $column, $column_name, $user_id ) {
		$index = "{$this->type} num";
		if ( $column_name === $index ) {
			$counts = $this->get_author_post_type_counts();
			if ( array_key_exists( $user_id, $counts ) ) {
				$html   = $this->element( 'span', [ 'aria-hidden' => 'true' ], $counts[$user_id] );
				$string = _nx( '%1$s %2$s by this author', '%1$s %2$s by this author', $counts[ $user_id ], 'first placeholder is numeric, second should be a noun', 'tcc-plugin' );
				$place  = ( $counts[ $user_id ] < 2 ) ? $this->label : $this->plural;
				$count  = sprintf( $string, $counts[ $user_id ], $place );
				$html  .= $this->element( 'span', [ 'class' => 'screen-reader-text' ], $count );
				$link   = admin_url() . "edit.php?post_type={$this->type}&author={$user_id}";
				$column = $this->element( 'a', [ 'href' => $link ], $html, true );
			} else {
				$column = '[' . _x( 'none', 'indicates a zero count', 'tcc-plugin' ) . ']';
			}
		}
		return $column;
	}

	/**
	 *  Retrieve the author CPT counts.
	 *
	 * @since 20160327
	 * @return array  Author counts indexed by author name.
	 * @todo check $counts value when loading multiple CPTs.
	 */
	private function get_author_post_type_counts() {
		static $counts = 0;
		if ( ! $counts ) {
			global $wpdb;
			$sql  = "SELECT post_author, COUNT(*) AS post_count FROM posts WHERE post_type = %s";
			$sql .= " AND post_status IN ('publish','pending', 'draft') GROUP BY post_author";
			$authors = $wpdb->get_results( $wpdb->prepare( $sql, $this->type ) );
			foreach( $authors as $author ) {
				$counts[ $author->post_author ] = $author->post_count;
			}
		}
		return $counts;
	}


	/*  Template filters  */

	/**
	 *  Assign a template for a CPT.
	 *
	 * @since 20160205
	 * @param  string $template  The template requested.
	 * @return string            Modified template.
	 * @link http://codex.wordpress.org/Function_Reference/locate_template
	 * @link https://wordpress.org/support/topic/stylesheetpath-in-plugin
	 */
	public function assign_template( $template ) {
		$post_id = get_the_ID();
		if ( $post_id ) {
			$mytype = get_post_type( $post_id );
			if ( in_array( $this->type, [ $mytype ], true ) ) {
				if ( is_single() ) {
					$template = $this->locate_template( $template, 'single' );
				} else if ( is_search() || is_post_type_archive( $this->type ) ) {
					$template = $this->locate_template( $template, 'archive' );
				}
				$template = apply_filters( "fluid_assign_template_{$this->type}", $template );
			}
		}
		return $template;
	}

	/**
	 *  Locate a requested template.
	 *
	 * @since 20160209
	 * @param string $template  Template requested.
	 * @param string $slug      Slug for the CPT template.
	 */
	private function locate_template( $template, $slug ) {
		$testable = $slug . '-' . $this->type . '.php';
		if ( array_key_exists( $slug, $this->templates ) ) {
			$template = $this->templates[ $slug ];
		} else if ( array_key_exists( 'folders', $this->templates ) ) {
			foreach( (array) $this->templates['folders'] as $folder ) {
				$test = $folder . '/' . $testable;
				if ( is_readable( $test ) ) {
					$template = $test;
					break;
				}
			}
		} else {
			$maybe = locate_template( array( $testable ) );
			if ( $maybe ) {
				$template = $maybe;
			}
		}
		return $template;
	}


	/**  Alternate Template filters  **/

	/**
	 *  An alternate methodology for getting template files, just something I experimented with, not finished.
	 *
	 * @since 20160720
	 * @return string  Template file.
	 */
	private function assign_template_filters() {
		if ( ! empty( $this->templates['single'] ) ) {
			add_filter( 'single_template', [ $this, 'single_template' ] );
		}
		if ( ! empty( $this->templates['archive'] ) ) {
			add_filter( 'archive_template', [ $this, 'archive_template' ] );
		}
/*  TODO:  Test this construct
		foreach( $this->templates as $key => $template ) {
			if ( in_array( $key, [ 'folders' ] ) ) {
				// TODO:  this needs to be handled
				continue;
			}
			add_filter(
				"{$key}_template",
				function( $mytemplate ) use ( $key ) { // FIXME:  does it need to use $this?
					global $post;
					if ( $post->post_type === $this->type ) {
						$mytemplate = $this->templates[ $key ];
					}
					return $mytemplate;
				}
			);
		} //*/
	}

	/**
	 *  Assign an archive template for CPT.
	 *
	 * @since 20160720
	 * @param  string $template  Template file.
	 * @return string            Assigned archive template file.
	 */
	public function archive_template( $template ) {
		global $post;
		if ( $post->post_type === $this->type ) {
			$template = $this->templates['archive'];
		}
		return $template;
	}

	/**
	 *  Assign a single template.
	 *
	 * @since 20160720
	 * @param  string $template  Template file.
	 * @return string            Assigned single template file.
	 */
	public function single_template( $template ) {
		global $post;
		if ( $post->post_type === $this->type ) {
			$template = $this->templates['single'];
		}
		return $template;
	}


	/*  Comments  */

	/**
	 *  Instill a comment limit.
	 *
	 * @since 20160205
	 * @param  bool $open     Are the comments open?
	 * @param  int  $post_id  ID of the current post.
	 * @return bool           Keep comments open?
	 */
	public function comments_limit( $open, $post_id ) {
		$mytype = get_post_type( $post_id );
		if ( $this->type === $mytype ) {
			if ( is_singular( $mytype ) ) {
				if ( ( property_exists( $this, 'comments' ) ) && ( $this->comments ) ) {
					if ( is_bool( $this->comments ) ) {
						$open = $this->comments;
					} else { // TODO:  support numeric values
						$open = (bool) $this->comments;
#						$postime = get_the_time('U', $post_id);
						$this->logg("WARNING: Numeric values for {$this->type}->comments is not yet supported.");
					}
				}
			}
		}
		return $open;
	} //*/


	/*  Query modifications  */

	/**
	 *  Make sure the CPT shows up in queries.
	 *
	 * @since 20160205
	 * @param object $query  Current page query.
	 * @link https://wordpress.org/support/topic/custom-post-type-posts-not-displayed
	 */
	public function pre_get_posts( $query ) {
	#	if ( ( ( ! is_admin() ) && $query->is_main_query() && ( ! $query->is_page() ) ) || $query->is_feed ) {
		if ( $query->is_feed || ( $query->is_main_query() && ! ( is_admin() || $query->is_page() ) ) ) {
			$this->add_post_type( $query );
		}
	}

	/**
	 *  Add the CPT to the current query.
	 *
	 * @since 20170104
	 * @param object $query  Query to modify.
	 */
	protected function add_post_type( $query ) {
		$check = $query->get( 'post_type' );
		if ( empty( $check ) ) {
			$query->set( 'post_type', array( 'post', $this->type ) );
		} else if ( is_string( $check ) ) {
			if ( $check !== $this->type ) {
				$query->set( 'post_type', array( $check, $this->type ) );
			}
		} else if ( ! in_array( $this->type, $check ) ) {
			$check[] = $this->type;
			$query->set( 'post_type', $check );
		}
	}

	/**
	 *  Omit certain taxonomies from post searches.
	 *
	 * @since 20160327
	 * @param WP_Query $query  The current query.
	 */
	public function omit_get_posts( $query ) {
		if ( $this->tax_omit ) {
			if ( ! is_admin() ) {  #  && $query->is_main_query()) {
				if ( ( ! $query->is_page() ) || ( is_feed() ) ) {
					$check = $query->get( 'post_type' );
					if ( in_array( $this->type, (array) $check ) ) {
						foreach( $this->tax_omit as $tax => $data ) {
							$terms = array();
							foreach( $data as $term ) {
								$term_id = $this->get_term_id( $term, $tax );
								if ( $term_id ) {
									$terms[] = $term_id;
								}
							}
							$omit = '-' . implode( ',-', $terms );
							if ( $tax === 'category' ) {
								$query->set( 'cat', $omit );
							} elseif ( $tax === 'post_tag' ) {
								$query->set( 'tag', $omit );
							} else {
								$args = array(
									'taxonomy' => $tax,
									'field'    => 'id',
									'terms'    => $terms,
									'operator' => 'NOT IN'
								);
								$query->set( 'tax_query', array( $args ) );
							}
						}
					}
				}
			}
		}
	}


	/*  Meta box  */

	/**
	 *  Remove the author metabox if the user cannot edit other authors' posts.
	 *
	 * @since 20160205
	 * @fixme Check for existing capabilities.
	 * @fixme Alter check condition for when not remapping capabilities.
	 */
	public function check_meta_boxes() {
		if ( ! ( $this->caps === 'post' ) ) {
			$cap = "edit_others_" . sanitize_title( $this->plural );
			if ( ! current_user_can( $cap ) ) {
				remove_meta_box( 'authordiv', $this->type, 'normal' );
			}
		}
	}


}

/**
 * insert a key/value pair into an array after a specific key
 *
 * @param  array  $array      Array to act upon.
 * @param  string $key        Key to search for.
 * @param  string $new_key    Key to insert.
 * @param  mixed  $new_value  Value to insert.
 * @return array              Modified array.
 * @link http://eosrei.net/comment/287
 */
if ( ! function_exists( 'array_insert_after' ) ) {
	function array_insert_after( array $array, $key, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				$new[ $k ] = $value;
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
			}
			return $new;
		}
		return $array;
	}
}
