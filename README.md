# Custom Post Type

This is a base class for WordPress custom post types.  This is -not- a UI, nor is it intended to be.  If you are looking for that, then try here: https://github.com/WebDevStudios/custom-post-type-ui

I have seen quite a few different ways of how people handle custom post types in wordpress, but was never really happy with any of them.  They all did what they were designed to, but never really seemed to cover the things that I needed.  So I set out to make my own.  It is still very much a work in progress.  Please drop me a note if you find it useful in your own projects.

The basis for a lot of the code originated from different places on the web.  I have tried to give credit where I can.  My coding style can not be considered 'orthodox' in any way, shape, form, or fashion.

## Install

Requires PHP 5.3+

This really consists of only three files:
```
  classes/custom-post.php
  js/slug_noedit.js
  js/tax_nodelete.js
```
Simply copy these to their respective location.  That's it.

## Usage

Create your own class extending this one.  Look in the examples/ directory for ideas.  um, there is only one there right now.  more coming though...  I would be must grateful if anyone could contribute an example or two...

The construction method in your child class might look like this:
```
public function __construct() {
    $data = array('type'       => 'property',
                  'label'      => _x('Property','owned plot of land - singular form','text-domain'),
                  'plural'     => _x('Properties','owned plots of land - plural form','text-domain'),
                  'descrip'    => __('Real Estate Property','text-domain'),
                  'position'   => 6,
                  'icon'       => 'dashicons-admin-home',
                  'taxonomies' => array('category'),
                  'template'   => array('single'=>plugins_url('../page_templates/single-property.php',__FILE__)),
                  'slug_edit'  => false);
    parent::__construct($data);
    if (is_admin()) add_action('admin_enqueue_scripts',array($this,'admin_enqueue_scripts'));
    add_action('tcc_custom_post_'.$this->type,array($this,'create_taxonomies'));
    add_action('add_meta_boxes_'.$this->type, array($this,'add_meta_boxes'));
    add_action('save_post_'.$this->type,      array($this,'save_meta_boxes'));
  }
```
The method that registers the CPT uses only a subset of arguments available for the WordPress register_post_type() function.  If you need to utilize more, there is a filter 'tcc_register_post_{post type}' that allows you to modify the array.  These values will be utilized if passed:

## General Guidelines

After the post type has been created, an action hook is run named 'tcc_custom_post_{post slug}'.  Hook there to run any needed code, such as registering a taxonomy.

#### Capabilities
Automatically creates unique caps, based on the slug for the CPT.  Also, adds the expected caps to the default WordPress user roles. Does not handle custom roles, although `$GLOBALS['wp_post_types'][$this->type][cap]` will give you a full list for the CPT.

#### Taxonomies
The class provides a taxonomy_registration() method.  If used, it provides the ability to prevent term deletion for the taxonomy.  There is also a mechanism in place to prevent specific term deletion.  See below for more information.

#### Template
A 'single' template path and name for the CPT can be assigned, and it will be used when displaying the CPT.

#### Term Deletion
If you want to prevent specific taxonomy terms from being deleted, then when creating the taxonomy in the child class, append an array of the term slugs or names to the tax_keep property array, like so:<br>
`$this->tax_keep['taxonomy_slug'] = array('term-slug')`<br>
or<br>
`$this->tax_keep['taxonomy_slug'] = array(__('Term Name One','text-domain'))`
The array must be consistent, either all slugs, or all names.

#### Text domain
A unique string placeholder 'text-domain' is currently used.  If you are familiar with the linux sed command you can use this command:  `sed -i 's/text-domain/your-domain-name-here/' path-to/custom-post.php`  Alternately, override the method translated_text(), but be sure to duplicate the array structure and __all__ the strings it contains.

#### Text strings
The class uses a method named translated_text() to provide default strings for both post labels and taxonomy labels.  The methods utilizing the strings are post_type_labels() and taxonomy_labels().  There is also post_type_messages() which generates CPT specific messages which are displayed in place of the standard WordPress messages.

## Taxonomies

$this->taxonomy_registration($args)

$args must be either an associative array or a string.  If it is a string then it must be parsable by the WordPress wp_parse_args() function.  Accepted arguments are:
```
tax      => string -- the taxonomy slug (required)<br>
taxargs  => array --- passed as the third argument to the WordPress register_taxonomy() function.
single   => string -- single label name, same as $taxargs['labels']['singular_name'] (one of the two is required)<br>
plural   => string -- plural label name, same as $taxargs['labels']['name'] or $taxargs['label'] (one of the three is required)<br>
admin    => boolean - same as $taxargs['show_admin_column']<br>
rewrite  => string -- same as $taxargs['rewrite']['slug'], defaults to taxonomy slug if either is not set<br>
nodelete => boolean - indicates that a term in this taxonomy cannot be deleted if the term is associated with a post. default to false<br>
terms    => array --- terms to populate the taxonomy with.  only happens if the taxonomy is completely devoid of terms.
slug     => boolean - force the method to assume the 'terms' array uses slugs as keys if true, or no keys if false.  otherwise it will guess.
```
This method calls the taxonomy_labels() method, which constructs a labels array, based on the translated_text() method strings.  The terms array should be in structured as `array('Term Name')` or `array('term-slug'=>'Term Name')`.  It does not handle 'alias_of','description', or 'parent' at this time.

