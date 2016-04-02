# Custom Post Type

This is a base class for WordPress custom post types.  This is -not- a UI, nor is it intended to be.  If you are looking for that, then try here: https://github.com/WebDevStudios/custom-post-type-ui

I have seen quite a few different ways of how people handle custom post types in wordpress, but was never really happy with any of them.  They all did what they were designed to, but never really seemed to cover the things that I needed.  So I set out to make my own.  It is still very much a work in progress.  Please drop me a note if you find it useful in your own projects.

The basis for a lot of the code originated from different places on the web.  I have tried to give credit where I can.  My coding style can not be considered 'orthodox' in any way, shape, form, or fashion.

## Features

* Provides a base class for easier CPT creation.
* Control whether the CPT show ups on the Blog page along with regular posts.
* Assign a custom 'single' template for displaying the CPT from a plugin.
* Assign a folder, or list of folders to look for templates in.
* Add a sidebar for the CPT.
* Show a column on the admin Users screen, providing an author count.
* Stop a user from deleting a custom taxonomy term, either permanently or if in use.
* Prevent editing of custom taxonomy term slugs.
* Stop a CPT with a specific taxonomy term from showing up on queries.
* Automatically generate custom capabilites, suitable to be used for custom roles.
* Generate log messages using your own logging function.

## Install

Requires PHP 5.3+, the 'no term deletion' and 'no slug editing' functions require jQuery.

Works with WordPress 4.4.2

This really consists of only three files:
```
  classes/custom-post.php
  js/slug_noedit.js
  js/tax_nodelete.js
```
Simply copy these to their respective location.  That's it.

## Usage

Create your own class extending this one.

A bare minumum child class could look like this:
```
class Simple_Custom_Post_Type extends RC_Custom_Post_Type {

  protected $type = 'simple';
  protected $main_blog = true;

  public function __construct() {
    $args = array( 'label'     => __('Simple', 'text-domain'),
                   'plural'    => __('Simples','text-domain'));
    parent::__construct($args);
  }

}
```

A more complicated class might look like this:
```
class Property extends RC_Custom_Post_Type {

  public function __construct() {
    $data = array('type'       => 'property',
                  'label'      => _x('Property','single plot of land','text-domain'),
                  'plural'     => _x('Properties','multiple plots of land','text-domain'),
                  'descrip'    => __('Real Estate Property','text-domain'),
                  'menu_position' => 6,
                  'menu_icon'  => 'dashicons-admin-home',
                  'taxonomies' => array('category'),
                  'templates'  => array('single'  => plugin_dir_path(__FILE__).'../page-templates/single-property.php',
                                        'archive' => plugin_dir_path(__FILE__).'../page-templates/archive-property.php'),
                  'slug_edit'  => false);
    parent::__construct($data);
    if (is_admin()) add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'));
    add_action( 'tcc_custom_post_'.$this->type, array( $this, 'create_taxonomies'));
    add_action( 'add_meta_boxes_'.$this->type,  array( $this, 'add_meta_boxes'));
    add_action( 'save_post_'.$this->type,       array( $this, 'save_meta_boxes'));
    add_filter( 'tcc_register_post_property',   array( $this, 'register_property'));
  }

  public function register_property($args) {
    $args['show_in_nav_menus'] = false;
    return $args;
  }

}
```
Look in classes/custom-post.php for a list of all available arguments, and what they do.  The method that registers the CPT uses only a subset of arguments available for the WordPress [register_post_type()](http://codex.wordpress.org/Function_Reference/register_post_type) function.  If you need to utilize more, there is a filter 'tcc_register_post_{post-type}' that allows you to modify the $args array.  I seem to be adding something with every project, so it may have the full set at some point.  :)  I have tried to use what I consider reasonable defaults.

## General Guidelines

#### Capabilities
While defaulting to using standard Wordpress caps, the class can generate unique caps, based on the slug for the CPT.  Also, adds the expected caps to the default WordPress user roles. For a listing of those caps, look in `$GLOBALS['wp_post_types'][post type]['cap']`.

#### Labels
The class generates a default array of strings for the labels based upon the singular and plural labels.  This array can be altered using the filter action `tcc_post_label_{post-type}`.  See the 'Text domain' and 'Text strings' sections below for related information.

#### Taxonomies
After the post type has been created, an action hook is run named 'tcc_custom_post_{post-type}'.  Hook there to run code such as registering a taxonomy.  See the 'Taxonomies' sections below.
The class provides its own taxonomy_registration() method.  When used, it provides the ability to prevent term deletion for the taxonomy.  There is also a mechanism in place to prevent specified term deletion.  See the 'Term Deletion' section below for more information.

#### Template
A template file can be assigned for 'single' and 'archive' templates.  A template 'folder' can also be specified.  The filter, `tcc_assign_template_{$this->type}`, can also be used.  I think this still needs more work.  Let me know if you run into use cases this doesn't handle properly.

#### Term Deletion
The taxonomy must have been created use the class method taxonomy_registration() in order for this to work.
There is some support for builtin taxonomies - `'nodelete'=>true` must be passed as a construction argument.
If you want to prevent specific taxonomy terms from being deleted, append an array of the term slugs and/or names to the tax_keep property array, like so:<br>
`$this->tax_keep['taxonomy-slug'] = array('term-slug-one',__('Term Name Two','text-domain'))`

#### Text Domain
All the strings the class uses are defined in the method translated_text().  Redefine the method in the child class to change the text-domain and/or wording of the strings, but be sure to duplicate the array structure __exactly__.  Alternately, you could change the custom-post.php text domain to your domain.  If you are comfortable with the linux command line you could use this command:  `sed -i 's/tcc-custom-post/your-domain-name-here/' path-to/custom-post.php`.  Or you could just do a search and replace in your favorite text editor.

#### Text strings
the method translated_text() provides default strings for both cpt and taxonomy labels.  The methods utilizing the strings are post_type_labels(), taxonomy_labels(), and post_type_messages().  The latter generates CPT specific messages which are displayed in place of the standard WordPress messages.

## Taxonomies

$this->taxonomy_registration($args)

$args must be either an associative array or a string.  If it is a string then it must be parsable by the WordPress [wp_parse_args()](http://codex.wordpress.org/Function_Reference/wp_parse_args) function.  Accepted arguments are:
```
tax      => string -- the taxonomy slug (required)
taxargs  => array --- passed as the third argument to the WordPress register_taxonomy() function if present
single   => string -- single label name, same as `$taxargs['labels']['singular_name']`
                      (one of the two is required)
plural   => string -- plural label name, same as `$taxargs['labels']['name'] or $taxargs['label']`
                      (one of the three is required)
admin    => boolean - same as `$taxargs['show_admin_column']`
rewrite  => string -- same as `$taxargs['rewrite']['slug']`, defaults to taxonomy slug if either is not set
nodelete => boolean - true indicates that a term in this taxonomy cannot be deleted if the term is
                      associated with a post. default: false
terms    => array --- terms to populate the taxonomy with.  Only happens if the taxonomy is completely
                      devoid of terms.  The 'terms' array can be in structured as `array('Term Name One',
                      'term-slug-two'=>'Term Name Two')`.  It does not handle 'alias_of','description', or
                      'parent' at this time.
func     => string -- function/method name - if the 'terms' array is empty, this function will be used to
                      populate the terms array, default function called: '$this->default_{tax-slug}'
                      set to false to disable.
omit     => array --- array of terms not to display in searches. ie:  if a post has this term, that post
                      will be omitted from query results.
```

# Change Log

No formal release yet.  Code still subject to change without notice.
