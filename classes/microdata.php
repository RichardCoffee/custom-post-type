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

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'microdata' ) ) {
	function microdata() {
		static $library = null;
		if ( empty( $library ) ) {
			$library = TCC_Microdata::instance();
		}
		return $library;
	}
}

if ( ! class_exists( 'TCC_Microdata' ) ) {

class TCC_Microdata {

	use TCC_Trait_Singleton;

	private function __construct() {
		$this->filters();
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

	public function __call( $name, $args ) {
		return $this->microdata( $name, $args );
	}

	public function microdata( $type, $as_attr = false ) {
		if ( $as_attr ) {
			return $this->microdata_attrs( $type );
		} else if ( method_exists( $this, $type ) ) {
			$this->$type();
		} else {
			echo 'itemscope itemtype="http://schema.org/' . esc_attr( $type ) . '"';
		}
	}

	public function microdata_attrs( $type ) {
		return array(
			'itemscope' => 'itemscope',
			'itemtype'  => 'http://schema.org/' . $type,
		);
	}

	/***   helper shortcuts   ***/
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
		echo 'itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting"';
	}

	public function PostalAddress() { // descendant of many types - see itemtype link
		echo 'itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"';
	}

	/**
	 * @link https://github.com/schemaorg/schemaorg/issues/1912
	 */
	public function SiteNavigationElement() {
		echo 'itemprop="WebPageElement" itemscope itemtype="http://schema.org/SiteNavigationElement"';
	}


	/**
	 *  These functions are designed to be called in place of the native wordpress function.
	 *
	 *  These functions should be called like so:
	 *    microdata()->bloginfo( 'name' );
	 *    $name = microdata()->get_bloginfo( 'name' );
	 *
	 */

	public function bloginfo( $show, $filter = 'raw' ) {
		echo $this->get_bloginfo( $show, $filter );
	}

	public function get_bloginfo( $show, $filter = 'raw' ) {
		if ( $show === 'url' ) { // bloginfo('url') has been deprecated by WordPress
			$string = esc_url( home_url( '/' ) );
		} else {
			$string = esc_html( get_bloginfo( $show, $filter ) );
			if ( $show === 'name' ) {
				$string = '<span itemprop="copyrightHolder">' . $string . '</span>';
			}
		}
		return $string;
	}

	/*
	 *  These are filters, and will do their work behind the scenes.  Nothing else is required.
	 *
	 *  Note the priority on these.  Extend the class if you need a different priority.
	 *  If you find you do need to change the priority, please send me an email if you
	 *    feel the change should be reflected in the 'core' code.
	 *
	 */

	private function filters( $pri = 20 ) {
		add_filter('comments_popup_link_attributes',     [ $this, 'comments_popup_link_attributes' ],     $pri );
		add_filter('comment_reply_link',                 [ $this, 'comment_reply_link' ],                 $pri );
		add_filter('get_archives_link',                  [ $this, 'get_archives_link' ],                  $pri );
		add_filter('get_avatar',                         [ $this, 'get_avatar' ],                         $pri );
		add_filter('get_comment_author_link',            [ $this, 'get_comment_author_link' ],            $pri );
		add_filter('get_comment_author_url_link',        [ $this, 'get_comment_author_url_link' ],        $pri );
		add_filter('get_comment_date',                   [ $this, 'get_comment_date' ],                   $pri, 3 );
		add_filter('get_comment_text',                   [ $this, 'get_comment_text' ],                   $pri, 3 );
		add_filter('get_post_time',                      [ $this, 'get_post_time' ],                      $pri, 3 );
		add_filter('get_the_archive_description',        [ $this, 'get_the_archive_description' ],        $pri );
		add_filter('get_the_archive_title',              [ $this, 'get_the_archive_title' ],              $pri );
		add_filter('get_the_date',                       [ $this, 'get_the_date' ],                       $pri, 3 );
		add_filter('get_the_modified_date',              [ $this, 'get_the_modified_date' ],              $pri, 3 );
		add_filter('get_the_title',                      [ $this, 'get_the_title' ],                      $pri, 2 );
		add_filter('post_thumbnail_html',                [ $this, 'post_thumbnail_html' ],                $pri );
		add_filter('post_type_archive_title',            [ $this, 'get_the_title' ],                      $pri, 2 );
		add_filter('single_cat_title',                   [ $this, 'single_term_title' ],                  $pri );
		add_filter('single_post_title',                  [ $this, 'get_the_title' ],                      $pri, 2 );
		add_filter('single_tag_title',                   [ $this, 'single_term_title' ],                  $pri );
		add_filter('single_term_title',                  [ $this, 'single_term_title' ],                  $pri );
		add_filter('the_author',                         [ $this, 'the_author' ],                         $pri );
		add_filter('the_author_posts_link',              [ $this, 'the_author_posts_link' ],              $pri );
		add_filter('wp_get_attachment_image_attributes', [ $this, 'wp_get_attachment_image_attributes' ], $pri, 2 );
		add_filter('wp_get_attachment_link',             [ $this, 'wp_get_attachment_link' ],             $pri );
	}

	public function comments_popup_link_attributes( $attr ) {
		return 'itemprop="discussionURL"';
	}

	public function comment_reply_link( $link ) {
		if ( strpos( $link, 'itemprop' ) === false ) {
			$patts  = [ '/(<a\s)/i', '/(<button\s)/i' ];
			$link   = preg_replace( $patts, '$1 itemprop="url"', $link );
			$schema = '<span itemprop="potentialAction" itemscope itemtype="http://schema.org/ReplyAction">';
			$link   = $schema . $link . '</span>';
		}
		return $link;
	}

	public function get_archives_link( $link ) {
		if ( strpos( $link, 'itemprop' ) === false ) {
			$patts = array( '/(<link.*?)(\/>)/i', "/(<option.*?>)(\'>)/i", "/(<a.*?)(>)/i" ); #<?
			$link  = preg_replace( $patts, '$1 itemprop="url" $2', $link );
		}
		return $link;
	}

	public function get_avatar( $avatar ) {
		if ( strpos( $avatar, 'itemprop' ) === false ) {
			$avatar = preg_replace( '/(<img.*?)(\/>|>)/i', '$1 itemprop="image" $2', $avatar );
		}
		return $avatar;
	}

	public function get_comment_author_link( $link ) {
		if ( strpos( $link, 'target=' ) === false ) {
			$link = preg_replace( '/(<a.*?)(>)/i', '$1 target="_blank" $2', $link );
		}
		if ( strpos( $link, 'itemprop' ) === false ) {
			$pats = array( '/(<a.*?)(>)/i',       '/(<a.*?>)(.*?)(<\/a>)/i' ); #<?
			$reps = array( '$1 itemprop="url"$2', '$1<span itemprop="creator">$2</span>$3' );
			$link = preg_replace( $pats, $reps, $link );
		}
		return $link;
	}

	public function get_comment_author_url_link( $link ) {
		if ( strpos( $link, 'itemprop' ) === false ) {
			$link = preg_replace( '/(<a.*?)(>)/i', '$1 itemprop="url"$2', $link );
		}
		return $link;
	}

	public function get_comment_date( $date, $d, $comment ) {
		if ( strpos( $date, 'time itemprop' ) === false ) {
			$datetime = mysql2date( 'Y-m-d H:i:s', $comment->comment_date );
			$date = '<time itemprop="dateCreated" datetime="' . $datetime . '">' . esc_html( $date ) . '</time>';
		}
		return $date;
	}

	public function get_comment_text( $text, $comment, $args ) {
		if ( strpos( $text, 'span itemprop' ) === false ) {
			$text = '<span itemprop="text">' . $text . '</span>';
		}
		return $text;
	}

	public function get_post_time( $time, $format, $gmt ) {
		if ( strpos( $time, 'itemprop' ) === false ) {
			$date_time = DateTime::createFromFormat( $format, $time );
			if ( $date_time ) {
				$date = $date_time->format( 'Y-m-d H:i:s' );
				$time = '<time itemprop="datePublished" datetime="' . $date . '">' . esc_html( $time ) . '</time>';
			}
		}
		return $time;
	}

	public function get_the_archive_description( $descrip ) {
		if ( ! ( strpos( $descrip, 'itemprop' ) === false ) ) { return $descrip; }
		return '<span itemprop="description">' . esc_html( $descrip ) . '</span>';
}

	public function get_the_archive_title( $title ) {
		if ( strpos( $title, 'itemprop' ) === false ) {
			if ( is_author() ) {
				$title = preg_replace( '/(<span.*?)(>)/i', '$1 itemprop="author"$2', $title );
			} else if ( $title === __( 'Archives' ) ) {  #  Translatable in core
				$title = '<span itemprop="headline">' . esc_html( $title ) . '</span>';
			}
		}
		return $title;
	}

	public function get_the_date( $the_date, $format, $postID ) {
		if ( ( strpos( $the_date, 'itemprop' ) === false ) && ( ! ( $format === 'U' ) ) ) {
			$datetime = mysql2date( 'Y-m-d H:i:s', get_post( $postID )->post_date );
			$string   = '<time itemprop="datePublished" datetime="%1$s">%2$s</time>';
			return sprintf( $string, $datetime, esc_html( $the_date ) );
		}
		return $the_date;
	}

	public function get_the_modified_date( $the_date, $format, $postID ) {
		if ( ( strpos( $the_date, 'itemprop' ) === false ) && ( ! ( $format === 'U' ) ) ) {
			$datetime = mysql2date( 'Y-m-d H:i:s', get_post( $postID )->post_modified );
			$string   = '<time itemprop="dateModified" datetime="%1$s">%2$s</time>';
			return sprintf( $string, $datetime, esc_html( $the_date ) );
		}
		return $the_date;
	}

	public function get_the_title( $title, $id ) {
		if ( ! ( strpos( $title, 'itemprop' ) === false ) )               return $title;  // itemprop already present
		if ( ! ( strpos( $title, 'sr-only' ) === false ) )                return $title;  // bootstrap css
		if ( ! ( strpos( $title, 'screen-reader-text') === false ) )      return $title;  // underscore theme
		if ( $this->called_by( [ 'wp_title', '_wp_render_title_tag' ] ) ) return $title;  // string already processed
		return '<span itemprop="headline">' . esc_html( $title ) . '</span>';
	}

	public function post_thumbnail_html( $html ) {
		if ( ! ( strpos( $html, 'itemprop' ) === false ) ) return $html;
		return preg_replace( '/(<img.*?)(\/>|>)/i', '$1 itemprop="image" $2', $html );
	}

	public function single_term_title($title) {
		if ( ! ( strpos( $title, 'itemprop' ) === false ) ) { return $title; }
		if ( $this->called_by( array( 'wp_title', 'wp_get_document_title' ) ) ) { return $title; }
		return '<span itemprop="headline">'. esc_html( $title ) . '</span>';
	}

	public function the_author( $author ) {
		if ( ! ( strpos( $author, 'itemprop' ) === false ) ) { return $author; }
		if ( $this->called_by( [ 'get_the_author_posts_link' ] ) ) { return $author; }
		return '<span itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name">' . esc_html( $author ) . '</span></span>';
	}

	public function the_author_posts_link( $link ) {
		if ( ! ( strpos( $link, 'itemprop' ) === false ) ) return $link;
		$schema  = '<span itemprop="author" itemscope itemtype="http://schema.org/Person">';
		$pattern = array( '/(<a.*?)(>)/i',       '/(<a.*?>)(.*?)(<\/a>)/i' ); #<?
		$replace = array( '$1 itemprop="url"$2', '$1<span itemprop="name">$2</span>$3' );
		return $schema . preg_replace( $pattern, $replace, $link ) . '</span>';
}

	public function wp_get_attachment_image_attributes( $attr, $attachment ) {
		if ( ! isset( $attr['itemprop'] ) ) {
			$attr['itemprop'] = 'image';
		}
		return $attr;
	}

	public function wp_get_attachment_link( $link ) {
		if ( ! ( strpos( $link, 'itemprop' ) === false ) ) return $link;
		return preg_replace( '/(<a.*?)>/i', '$1 itemprop="contentURL">', $link );
	}


	/***   Helper functions   ***/

	public function description( $text ) {
		if ( ! ( strpos( $text, 'itemprop' ) === false ) ) { return $text; }
		return '<span itemprop="description">' . esc_html( $text ) . '</span>';
	}

	public function email_format( $email ) {
		if ( ! ( strpos( $email, 'itemprop' ) === false ) ) { return $email; }
		$email  = sanitize_email( $email );
		$string = '<a href="mailto:%s" itemprop="email">%s</a>';
		return sprintf( $string, $email, $email );
	}

	public function image_html( $image ) {
		return $this->post_thumbnail_html( $image );
	}

	public function name( $name ) {
		if ( ! ( strpos( $name, 'itemprop' ) === false ) ) { return $name; }
		return '<span itemprop="name">' . esc_html( $name ) . '</span>';
	}

	public function telephone( $phone ) {
		if ( ! ( strpos( $phone, 'itemprop' ) === false ) ) { return $phone; }
		return '<span itemprop="telephone">' . esc_html( $phone ). '</span>';
	}

	public function url_format( $url ) {
		if ( ! ( strpos( $url, 'itemprop' ) === false ) ) { return $url; }
		$string = '<a href="%s" itemprop="url">%s</a>';
		return sprintf( $string, esc_url( $url ), esc_url( $url ) );
	}


	/***   Address helper functions   ***/

	public function city( $city ) {
		if ( ! ( strpos( $city, 'itemprop' ) === false ) ) { return $city; }
		return '<span itemprop="addressLocality">' . esc_html( $city ) . '</span>';
}

	public function pobox( $pobox ) {
		if ( ! ( strpos( $pobox, 'itemprop' ) === false ) ) { return $pobox; }
		return '<span itemprop="postOfficeBoxNumber">' . esc_html( $pobox ) . '</span>';
}

	public function state( $state ) {
		if ( ! ( strpos( $state, 'itemprop' ) === false ) ) { return $state; }
		return '<span itemprop="addressRegion">' . esc_html( $state ) . '</span>';
	}

	public function street( $street ) {
		if ( ! ( strpos( $street, 'itemprop' ) === false ) ) { return $street; }
		return '<span itemprop="streetAddress">' . esc_html( $street ) . '</span>';
	}

	public function zipcode( $zipcode ) {
		if ( ! ( strpos( $zipcode, 'itemprop' ) === false ) ) { return $zipcode; }
		return '<span itemprop="postalCode">' . esc_html( $zipcode ) . '</span>';
	}


	/***   Private functions   ***/

	private function called_by( $test = array() ) {
		$stack  = debug_backtrace();
		foreach( $stack as $entry ) {
			if ( ! isset( $entry['function'] ) ) continue;
			if ( in_array( $entry['function'], (array) $test ) ) return true;
		}
		return false;
	}


}  #  end of class TCC_Microdata

}  #  end of class exists check
