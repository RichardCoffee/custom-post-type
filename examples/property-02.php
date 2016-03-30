<?php

/*
 *  File:  classes/property.php
 *
 *  Notes:  create custom post type
 */

require_once('custom-post.php');

class Real_Estate_Property extends RC_Custom_Post_Type {

  protected $type         = 'property';
#  protected $debug        =  true;
  protected $logging      = 'log_entry';
  protected $main_blog    =  false;
  protected $supports     = array('title', 'editor', 'excerpt', 'custom-fields', 'thumbnail', 'page-attributes');
  protected $taxonomies   = array('category');
  protected $rewrite      = array('slug' => 'properties');

  public function __construct() {
    $data = array('label'     => __('Property Listing','walsh'),
                  'nodelete'  => true,
                  'plural'    => __('Property Listings','walsh'),
                  'menu_icon' => plugins_url( '../images/property.png', __FILE__ ));
    parent::__construct($data);
  }

  protected function translated_text() {
    static $text;
    if (empty($text)) {
      $text =  array('404'     => _x('No %s found',          'placeholder is plural form',  'walsh'),
                     'add'     => _x('Add New %s',           'placeheader is singular form','walsh'),
                     'add_rem' => _x('Add or remove $s',     'placeholder is plural form',  'walsh'),
                     'all'     => _x('All %s',               'placeholder is plural form',  'walsh'),
                     'archive' => _x('%s Archive',           'placeholder is singular form','walsh'),
                     'commas'  => _x('Separate %s with commas','placeholder is plural form','walsh'),
                     'edit_p'  => _x('Edit %s',              'placeholder is plural form',  'walsh'),
                     'edit_s'  => _x('Edit %s',              'placeholder is singular form','walsh'),
                     'filter'  => _x('Filter %s list',       'placeholder is plural form',  'walsh'),
                     'insert'  => _x('Insert into %s',       'placeholder is singular form','walsh'),
                     'list'    => _x('%s list',              'placeholder is singular form','walsh'),
                     'navig'   => _x('%s list navigation',   'placeholder is plural form',  'walsh'),
                     'new'     => _x('New %s',               'placeholder is singular form','walsh'),
                     'none'    => _x('No %s',                'placeholder is plural form',  'walsh'),
                     'parent'  => _x('Parent %s',            'placeholder is singular form','walsh'),
                     'popular' => _x('Popular %s',           'placeholder is plural form',  'walsh'),
                     'search'  => _x('Search %s',            'placeholder is plural form',  'walsh'),
                     'trash'   => _x('No %s found in trash', 'placeholder is plural form',  'walsh'),
                     'update'  => _x('Update %s',            'placeholder is singular form','walsh'),
                     'upload'  => _x('Uploaded to this %s',  'placeholder is singular form','walsh'),
                     'used'    => _x('Choose from the most used %s','placeholder is plural form','walsh'),
                     'view_p'  => _x('View %s',              'placeholder is plural form',  'walsh'),
                     'view_s'  => _x('View %s',              'placeholder is singular form','walsh'),
                     'messages'=> array('custom_u' => __('Custom field updated.', 'walsh'),
                         'custom_d' => __('Custom field deleted.','walsh' ),
                         'draft'    => _x('%s draft updated.','placeholder is singular form', 'walsh'),
                         'preview'  => _x('Preview %s',       'placeholder is singular form', 'walsh'),
                         'publish'  => _x('%s published.',    'placeholder is singular form', 'walsh'),
                         'revision' => _x('%1$s restored to revision from %2$s', '1: label in singular form, 2: date and time of the revision','walsh'),
                         'saved'    => _x('%s saved.',        'placeholder is singular form', 'walsh'),
                         'schedule' => _x('%1$s publication scheduled for %2$s', '1: label in singular form, 2: formatted date string','walsh'),
                         'submit'   => _x('%s submitted.',    'placeholder is singular form', 'walsh'),
                         'update'   => _x('%s updated.',      'placeholder is singular form', 'walsh')));
    }
    return $text;
  }

}
