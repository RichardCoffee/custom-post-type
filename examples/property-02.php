<?php

/*
 *  File:  classes/property.php
 *
 *  Notes:  create custom post type
 */

require_once('custom-post.php');

class Real_Estate_Property extends RC_Custom_Post_Type {

  protected $type         = 'property';
  protected $label        = 'Property Listing';
  protected $plural       = 'Property Listings';
  protected $supports     = array('title', 'editor', 'excerpt', 'custom-fields', 'thumbnail', 'page-attributes');
  protected $taxonomies   = array('category');
  protected $rewrite      = array('slug' => 'properties');
  protected $cpt_nodelete = true;

  public function __construct() {
    $data = array('menu_icon' => plugins_url( '../images/property.png', __FILE__ ));
    parent::__construct($data);
  }


}
