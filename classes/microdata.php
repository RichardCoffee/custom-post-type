<?php

/*
 * classes/microdata.php
 *
 *  Usage: $micro = TCC_Microdata::get_instance();
 *
 *  Text Domain:  Only one instance of a text domain is utilized, in the
 *                get_the_author method - change it as required.
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  Sources: http://www.bloggingspell.com/add-schema-org-markup-wordpress/
 *           http://leaves-and-love.net/how-to-improve-wordpress-seo-with-schema-org/
 *           https://github.com/justintadlock/hybrid-core
 *
 */

if (!function_exists('microdata')) {
  function microdata() {
    return TCC_Microdata::get_instance();
  }
}

if (!class_exists('TCC_Microdata')) {

class TCC_Microdata {

  static $instance = null;

  private function __construct() {
    $this->filters();
  }

  public static function get_instance() {
    if (self::$instance===null) {
      self::$instance = new TCC_Microdata();
    }
    return self::$instance;
  }

 /*
  *  These functions should be inserted into elements like so:
  *
  *       <?php $instance = microdata(); ?>
  *       <div class="container" role="main" <?php $instance->Blog(); ?>>
  *  or:  <div class="container" role="main" <?php $instance->microdata('Blog'); ?>>
  *
  *  Due to the sheer number of entries in the schema.org hierarchy, I
  *    have chosen to utilize the php magic __call method.  This is not
  *    what many people would refer to as 'best practice'.  I am simply
  *    going with what works for 'me'.  YMMV
  *
  *  Certain use cases have their own method.  These are simply ones I
  *    have encountered in my programming.  Extend the class for your own
  *    use cases.  The attributes itemprop and itemscope can appear either
  *    before or after the itemtype, and has no impact whatsoever over how
  *    these attributes are interpreted by the browser or search engine.
  *    The itemprop will always apply to any -previously- declared
  *    itemtype.  Do not misinterprete what 'previously' means. :)
  *
  *
  */

  public function __call($name,$arguments) {
    $this->microdata($name);
  }

  public function microdata($type) {
    if (method_exists($this,$type)) { $this->$type();
    } else { echo "itemscope itemtype='http://schema.org/$type'"; }
  }

  public function about()    { $this->AboutPage(); }              // CreativeWork > WebPage > AboutPage
  public function address()  { $this->PostalAddress(); }          // descendant of many types - see itemtype link
  public function agent()    { $this->RealEstateAgent(); }        // Organization|Place > LocalBusiness > RealEstateAgent
  public function company()  { $this->Organization(); }           // first tier type
  public function contact()  { $this->ContactPage(); }            // CreativeWork > WebPage > ContactPage
  public function element()  { $this->WebPageElement(); }         // CreativeWork > WebPage > WebPageElement
  public function footer()   { $this->WPFooter(); }               // CreativeWork > WebPage > WebPageElement > WPFooter
  public function group()    { $this->Organization(); }           // first tier type
  public function header()   { $this->WPHeader(); }               // CreativeWork > WebPage > WebPageElement > WPHeader
  public function item()     { $this->ItemPage(); }               // CreativeWork > WebPage > ItemPage
  public function navigate() { $this->SiteNavigationElement(); }  // CreativeWork > WebPage > WebPageElement > SiteNavigationElement
  public function page()     { $this->WebPage(); }                // CreativeWork > WebPage
  public function post()     { $this->BlogPosting(); }            // CreativeWork > Blog > BlogPosting
  public function profile()  { $this->ProfilePage(); }            // CreativeWork > WebPage > ProfilePage
  public function search()   { $this->SearchResultsPage(); }      // CreativeWork > WebPage > SearchResultsPage
  public function sidebar()  { $this->WPSideBar(); }              // CreativeWork > WebPage > WebPageElement > WPSideBar

  public function BlogPosting() { // CreativeWork > Blog > BlogPosting
    echo "itemprop='blogPost' itemscope itemtype='http://schema.org/BlogPosting'";
  }

  public function PostalAddress() { // descendant of many types - see itemtype link
    echo "itemprop='address' itemscope itemtype='http://schema.org/PostalAddress'";
  }


 /*
  *  These functions can be utilized like so:
  *
  *  $instance = microdata();
  *  echo sprintf(_x('Posted on %1$s by %2$s','1: formatted date, 2: author name','text-domain'),get_the_date(),$instance->get_the_author());
  *
  */

  public function bloginfo($show,$filter='raw') {
    if ($show=='url') { echo esc_url(home_url()); return; } // bloginfo('url') has been deprecated by WordPress
    $string = get_bloginfo($show,$filter);
    if ($show=='name') { $string = "<span itemprop='copyrightHolder'>$string</span>"; }
    echo $string;
  }

  public function get_bloginfo($show,$filter='raw') {
    if ($show=='url') return esc_url(home_url()); // get_bloginfo('url') has been deprecated by WordPress
    $string = get_bloginfo($show,$filter);
    if ($show=='name') { $string = "<span itemprop='copyrightHolder'>$string</span>"; }
    return $string;
  }

  public function get_the_author($addlink=false) {
    $string = '';
    if ($addlink) {
      $title  = sprintf(__('Posts by %s'),get_the_author());
      $string.= "<a itemprop='url' rel='author' title='$title' href='".get_author_posts_url(get_the_author_meta('ID'))."'>";
    }
    $string.= "<span itemprop='author'>";
    $string.= get_the_author();
    $string.= "</span>";
    if ($addlink) { $string.= "</a>"; }
    return $string;
  }

 /*
  *  These are filters, and will do their work behind the scenes.  Nothing else is required.
  *
  *  Note the priority on these.  Extend the class if you need a different priority.
  *  If you find you do need to change the priority, please send me an email if the change
  *    needs to be reflected in the 'core' code.
  *
  */

  private function filters() {
    $pri = 20;
    add_filter('comments_popup_link_attributes',     array($this,'comments_popup_link_attributes'),     $pri);
    add_filter('comment_reply_link',                 array($this,'comment_reply_link'),                 $pri);
    add_filter('get_archives_link',                  array($this,'get_archives_link'),                  $pri);
    add_filter('get_avatar',                         array($this,'get_avatar'),                         $pri);
    add_filter('get_comment_author_link',            array($this,'get_comment_author_link'),            $pri);
    add_filter('get_comment_author_url_link',        array($this,'get_comment_author_url_link'),        $pri);
    add_filter('get_post_time',                      array($this,'get_post_time'),                      $pri, 3);
    add_filter('get_the_archive_description',        array($this,'get_the_archive_description'),        $pri);
    add_filter('get_the_archive_title',              array($this,'get_the_archive_title'),              $pri);
    add_filter('get_the_date',                       array($this,'get_the_date'),                       $pri, 3);
    add_filter('get_the_title',                      array($this,'get_the_title'),                      $pri, 2);
    add_filter('post_thumbnail_html',                array($this,'post_thumbnail_html'),                $pri);
    add_filter('post_type_archive_title',            array($this,'get_the_title'),                      $pri, 2);
    add_filter('single_cat_title',                   array($this,'single_term_title'),                  $pri);
    add_filter('single_post_title',                  array($this,'get_the_title'),                      $pri, 2);
    add_filter('single_tag_title',                   array($this,'single_term_title'),                  $pri);
    add_filter('single_term_title',                  array($this,'single_term_title'),                  $pri);
    add_filter('the_author_posts_link',              array($this,'the_author_posts_link'),              $pri);
    add_filter('wp_get_attachment_image_attributes', array($this,'wp_get_attachment_image_attributes'), $pri, 2);
    add_filter('wp_get_attachment_link',             array($this,'wp_get_attachment_link'),             $pri);
  }

  public function comments_popup_link_attributes($attr) {
if ($attr) tcc_log_entry('micro: comments_popup_link_attributes',$attr);
    return 'itemprop="discussionURL"';
  }

  public function comment_reply_link($link) {
    if (strpos($link,'itemprop')===false)
      $link = preg_replace('/(<a\s)/i','$1 itemprop="replyToUrl"',$link);
    return $link;
  }

  public function get_archives_link($link) {
    if (strpos($link,'itemprop')===false) {
      $patts = array('/(<link.*?)(\/>)/i',"/(<option.*?>)(\'>)/i","/(<a.*?)(>)/i"); # <?php
      $link  = preg_replace($patts,'$1 itemprop="url" $2',$link);
    }
    return $link;
  }

  public function get_avatar($avatar) {
    if (strpos($avatar,'itemprop')===false) {
      $avatar = preg_replace('/(<img.*?)(\/>|>)/i','$1 itemprop="image" $2',$avatar);
    }
    return $avatar;
  }

  public function get_comment_author_link($link) {
    if (strpos($link,'itemprop')===false) {
      $pats = array('/(<a.*?)(>)/i',      '/(<a.*?>)(.*?)(<\/a>)/i'); #<?
      $reps = array('$1 itemprop="url"$2','$1<span itemprop="name">$2</span>$3');
      $link = preg_replace($pats,$reps,$link);
    }
    return $link;
  }

  public function get_comment_author_url_link($link) {
    if (strpos($link,'itemprop')===false) {
      $link = preg_replace('/(<a.*?)(>)/i','$1 itemprop="url"$2',$link);
    }
    return $link;
  }

  public function get_post_time($time,$format,$gmt) {
    if (strpos($time,'itemprop')===false) {
      if ($format==='Y-m-d H:i:s') {  #  This check is not strictly necessary
        $date = $time;
      } else {
        $Date = DateTime::createFromFormat($format,$time);
        $date = $Date->format('Y-m-d H:i:s');
      }
      $time = "<time itemprop='datePublished' datetime='$date'>$time</time>";
    }
    return $time;
  }

  public function get_the_archive_description($descrip) {
    if (!strpos($descrip,'itemprop')===false) return $descrip;
    return "<span itemprop='description'>$descrip</span>";
  }

  public function get_the_archive_title($title) {
    if (!strpos($title,'itemprop')===false) return $title;
    #if ($this->called_by('wp_title')) return $title;
    if (is_author()) {
      $title = preg_replace('/(<span.*?)(>)/i','$1 itemprop="author"$2',$title);
    } elseif ($title==__('Archives')) {  #  do not add text domain to this
      $title = "<span itemprop='headline'>$title</span>";
    }
    return $title;
  }

  public function get_the_date($the_date,$format,$postID) {
    if (strpos($the_date,'itemprop')===false  && $format!=='U') {
      $datetime = mysql2date('Y-m-d',get_post($postID)->post_date);
      $the_date = "<time itemprop='datePublished' datetime='$datetime'>$the_date</time>";
    }
    return $the_date;
  }

  public function get_the_title($title,$id) {
    if (!strpos($title,'itemprop')===false) return $title; // itemprop already present
    if (!strpos($title,'sr-only')===false) return $title;  // bootstrap css
    if (!strpos($title,'screen-reader-text')===false) return $title; // underscore theme
    if ($this->called_by(array('wp_title','_wp_render_title_tag'))) return $title;
    return "<span itemprop='headline'>$title</span>";
  }

  public function post_thumbnail_html($html) {
    if (!strpos($html,'itemprop')===false) return $html;
    return preg_replace('/(<img.*?)(\/>|>)/i','$1 itemprop="image" $2',$html);
  }

  public function single_term_title($title) {
    if (!strpos($title,'itemprop')===false) return $title;
    if ($this->called_by(array('wp_title','wp_get_document_title'))) return $title;
    return "<span itemprop='headline'>$title</span>";
  }

  public function the_author_posts_link($link) {
    if (!strpos($link,'itemprop')===false) return $link;
    $pattern = array('/(<a.*?)(>)/i',      '/(<a.*?>)(.*?)(<\/a>)/i'); #<?
    $replace = array('$1 itemprop="url"$2','$1<span itemprop="name">$2</span>$3');
    return preg_replace($pattern,$replace,$link);
  }

  public function wp_get_attachment_image_attributes($attr,$attachment) {
    if (!isset($attr['itemprop'])) { $attr['itemprop'] = 'image'; }
    return $attr;
  }

  public function wp_get_attachment_link($link) {
    if (!strpos($link,'itemprop')===false) return $link;
    return preg_replace('/(<a.*?)>/i','$1 itemprop="contentURL">',$link);
  }


  /**  Helper functions  **/

  public function description($text) {
    if (!strpos($text,'itemprop')===false) return $text;
    return "<span itemprop='description'>$text</span>";
  }

  public function email_format($email) {
    if (!strpos($email,'itemprop')===false) return $email;
    return "<a href='mailto:$email' itemprop='email'>$email</a>";
  }

  public function image_html($image) {
    return $this->post_thumbnail_html($image);
  }

  public function name($name) {
    if (!strpos($name,'itemprop')===false) return $name;
    return "<span itemprop='name'>$name</span>";
  }

  public function telephone($phone) {
    if (!strpos($phone,'itemprop')===false) return $phone;
    return "<span itemprop='telephone'>$phone</span>";
  }

  public function url_format($url) {
    if (!strpos($url,'itemprop')===false) return $url;
    return "<a href='$url' itemprop='url'>$url</a>";
  }


  /**  Address functions  **/

// FIXME: check for pre-existing itemprop

  public function city($city) {
    return "<span itemprop='addressLocality'>$city</span>";
  }

  public function pobox($pobox) {
    return "<span itemprop='postOfficeBoxNumber'>$pobox</span>";
  }

  public function state($state) {
    return "<span itemprop='addressRegion'>$state</span>";
  }

  public function street($street) {
    return "<span itemprop='streetAddress'>$street</span>";
  }

  public function zipcode($zipcode) {
    return "<span itemprop='postalCode'>$zipcode</span>";
  }


  /**  Private functions  **/

  private function called_by($test=array()) {
    $stack  = debug_backtrace();
    foreach($stack as $entry) {
      if (!isset($entry['function'])) continue;
      if (in_array($entry['function'],(array)$test)) return true;
    }
    return false;
  }


}

}  #  end of class exists check
