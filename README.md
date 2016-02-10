# Custom Post Type

This is a base class for WordPress custom post types.  This is -not- a UI, nor is it intended to be.  If you are looking for that, then try here: https://github.com/WebDevStudios/custom-post-type-ui

I have seen quite a few different ways of how people handle custom post types in wordpress, but was never really happy with any of them.  They all did what they were designed to, but never really seemed to cover the things that I needed.  So I set out to make my own.  It is still very much a work in progress.  Please drop me a note if you find it useful in your own projects.

The basis for a lot of the code originated from different places on the web.  I have tried to give credit where I can.  My coding style can not be considered 'orthodox' in any way, shape, form, or fashion.

## Features

* Provides a base class for easier CPT creation.
* You can assign a custom 'single' template for displaying a CPT from a plugin.
* You can assign a folder, or list of folders to look for templates in.
* You have the option of being able to prevent a user from deleting a taxonomy term.
* You can turn off editing of taxonomy term slugs
* Can generate log messages using your own logging function
* Automatically generates custom capabilites, which can be used for custom roles.

## Install

Requires PHP 5.3+, no term deletion and no slug editing functions require jQuery

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
class Simple_Custom_Post_Type extends Custom_Post_Type {

  public function __construct() {
    $args = array( 'type'   => 'simple',
                   'label'  => __('Simple', 'text-domain'),
                   'plural' => __('Simples','text-domain'));
    parent::__construct($data);
  }

}
```

A more complicated construction method might look like this:
```
public function __construct() {
    $data = array('type'       => 'property',
                  'label'      => _x('Property','single plot of land','text-domain'),
                  'plural'     => _x('Properties','multiple plots of land','text-domain'),
                  'descrip'    => __('Real Estate Property','text-domain'),
                  'position'   => 6,
                  'icon'       => 'dashicons-admin-home',
                  'taxonomies' => array('category'),
                  'template'   => array('single' => plugin_dir_path(__FILE__)."../template_parts/single-property.php"),
                  'slug_edit'  => false);
    parent::__construct($data);
    if (is_admin()) add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'));
    add_action( 'tcc_custom_post_'.$this->type, array( $this, 'create_taxonomies'));
    add_action( 'add_meta_boxes_'.$this->type,  array( $this, 'add_meta_boxes'));
    add_action( 'save_post_'.$this->type,       array( $this, 'save_meta_boxes'));
  }
```
The method that registers the CPT uses only a subset of arguments available for the WordPress register_post_type() function.  If you need to utilize more, there is a filter 'tcc_register_post_{post type}' that allows you to modify the array.

## General Guidelines

After the post type has been created, an action hook is run named 'tcc_custom_post_{post slug}'.  Hook there to run code such as registering a taxonomy.

#### Capabilities
Automatically creates unique caps, based on the slug for the CPT.  Also, adds the expected caps to the default WordPress user roles. For listing of those caps, look in `$GLOBALS['wp_post_types'][$this->type]['cap']`.

#### Taxonomies
The class provides a taxonomy_registration() method.  If used, it provides the ability to prevent term deletion for the taxonomy.  There is also a mechanism in place to prevent specific term deletion.  See below for more information.

#### Template
A 'single' template path and name for the CPT can be assigned, and it will be used when displaying the CPT.  I plan to add this for 'search' and 'archive' at some time in the future.

#### Term Deletion
If you want to prevent specific taxonomy terms from being deleted, then after creating the taxonomy in the child class, append an array of the term slugs or names to the tax_keep property array, like so:<br>
`$this->tax_keep['taxonomy_slug'] = array('term-slug')`<br>
or<br>
`$this->tax_keep['taxonomy_slug'] = array(__('Term Name One','text-domain'))`
The array must be consistent, either all slugs, or all names.

#### Text domain
All the strings the class uses are defined in the method translated_text().  Redefine the method in the child class to change the text-domain and/or wording of the strings, but be sure to duplicate the array structure __exactly__.  Alternately, you could change the custom-post.php text to the needed domain.  If you are comfortable with the linux command line you could use this command:  `sed -i 's/tcc-custom-post/your-domain-name-here/' path-to/custom-post.php`

#### Text strings
the method translated_text() provides default strings for both post labels and taxonomy labels.  The methods utilizing the strings are post_type_labels(), taxonomy_labels(), and post_type_messages().  The latter generates CPT specific messages which are displayed in place of the standard WordPress messages.

## Taxonomies

$this->taxonomy_registration($args)

$args must be either an associative array or a string.  If it is a string then it must be parsable by the WordPress wp_parse_args() function.  Accepted arguments are:
```
tax      => string -- the taxonomy slug (required)
taxargs  => array --- passed as the third argument to the WordPress register_taxonomy() function if present
single   => string -- single label name, same as $taxargs['labels']['singular_name']
                      (one of the two is required)
plural   => string -- plural label name, same as $taxargs['labels']['name'] or $taxargs['label']
                      (one of the three is required)
admin    => boolean - same as $taxargs['show_admin_column']
rewrite  => string -- same as $taxargs['rewrite']['slug'], defaults to taxonomy slug if either is not set
nodelete => boolean - true indicates that a term in this taxonomy cannot be deleted if the term is
                      associated with a post. default: false
terms    => array --- terms to populate the taxonomy with.  Only happens if the taxonomy is completely
                      devoid of terms.  The 'terms' array should be in structured as `array('Term Name One')`
                      or `array('term-slug-one'=>'Term Name One')`.  Be consistant, do not mix slugs with
                      names.  It does not handle 'alias_of','description', or 'parent' at this time.
slug     => boolean - true: force the method to assume the 'terms' array uses slugs as keys, false: names
                      only, unset: it will try to guess.
func     => string -- function/method name - if the 'terms' array is empty, this function will be used to
                      populate the terms array, default function called: '$this->default_{tax-slug}'
                      set to false to disable.
```

