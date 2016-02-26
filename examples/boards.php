<?php

require_once('custom-post.php');

class On_The_Boards extends Custom_Post_Type {

  public function __construct() {
    $data = array('type'      => 'board',
                  'label'     => _x('Board','noun - singular form','creatom'),
                  'plural'    => _x('Boards','noun - plural form','creatom'),
                  'descrip'   => __('On The Boards','creatom'),
                  'position'  => 7,
                  'templates' => array('single' => WP_PLUGIN_DIR.'/creatombuilder/templates/single-board.php'),
                  'columns'   => array('remove' => array('categories')));
    parent::__construct($data);
    add_action('add_meta_boxes', array($this,'add_meta_boxes'));
    add_action('save_post',      array($this,'save_meta_boxes'));
  }

  public function add_meta_boxes() {
    add_meta_box('brd_title',    __('Title Text','creatom'),array($this,'board_title'),'board','normal','high');
    add_meta_box('brd_gallery',  __('Gallery','creatom'),   array($this,'gallery'),    'board','normal','high');
    add_meta_box('brd_resources',__('Resources','creatom'), array($this,'resources'),  'board','normal','high');
    remove_meta_box('categorydiv','board','side');
    remove_meta_box('wordpress-https','board','side');
  }

  public function board_title($post) {
    wp_nonce_field(basename(__FILE__),'pbl_on_the_boards_nonce');
    $text = get_post_meta($post->ID,'pbl_board_title',true);
    if (!$text) $text = '';
    $category_ID = get_cat_ID('On The Boards');
    echo "<div class='hidden'><input id='in-category-$category_ID' type='checkbox' name='post_category[]' value='$category_ID' checked></div>";
    $wpesettings = array('media_buttons'=>false,'textarea_rows'=>7);
    wp_editor( htmlspecialchars_decode($text), 'pbl_board_title',$wpesettings);
  }

  public function gallery($post) {
    $gallery = get_post_meta($post->ID,'pbl_board_gallery',true);
    if (!$gallery) $gallery = array('text'=>'','images'=>array()); ?>
    <div id='gallery-clone' class='hidden'><?php
      $this->generate_single_gallery(); ?>
    </div>
    <div id='pbl-gallery' class='section group' data-cnt='<?php echo count($gallery['images'])+1; ?>'><?php
      $cnt = 0;
      foreach($gallery['images'] as $image) {
        $this->generate_single_gallery($image,$cnt++);
      } ?>
    </div>
    <button id='addGalleryImage' class='title_image marginbone'>Assign/Upload Image</button><?php
    $wpesettings = array('media_buttons'=>false,'textarea_rows'=>7);
    wp_editor( htmlspecialchars_decode($gallery['text']), 'pbl_board_gallery[text]',$wpesettings);
  }

  private function generate_single_gallery($image='',$cnt='clone') { ?>
    <div class='col span_1_of_6 pbl-gallery'><?php
      echo "<input type='text' class='hidden' name='pbl_gallery[images][]' value='$image' />";
      echo "<span class='pull-right'><i class='fa fa-minus-circle meta-minus delete-gallery'></i></span>";
      echo "<img src='$image'>"; ?>
    </div><?php
  }

  public function resources($post) {
    $resources = get_post_meta($post->ID,'pbl_board_resources',true);
    if (!$resources) $resources = array(); ?>
    <div id='pbl-resources' class='section group'>
      <div id='resource-clone' class='hidden'><?php
        $this->generate_single_resource(); ?>
      </div><?php
      $cnt = 0;
      foreach($resources as $key=>$resource) {
        $this->generate_single_resource($resource,$cnt++);
      } ?>
    </div>
    <button id='addResource' data-cnt='<?php echo $cnt; ?>'>Add New Resource</button><?php
  }

  private static function generate_single_resource($data=array(),$cnt='clone') {
    if (empty($data)) $data = array('image'=>'','title'=>'','by'=>'','url'=>''); ?>
    <div class='col span_2_of_2 resource'>
      <div class='col span_1_of_12'></div>
        <div class='col span_3_of_12'>
          <div class='col span_2_of_2'>
            <button class='resource_image'>Assign/Upload Image</button>
          </div><?php
          echo "<input type='text' class='hidden' name='pbl_resource[res_$cnt][image]' value='{$data['image']}' />";
          echo "<img class='img-tiny' src='{$data['image']}'>"; ?>
        </div>
        <div class='col span_7_of_12'><?php
        echo "<textarea class='text1 textwide marginbone' name='pbl_resource[res_$cnt][title]' placeholder='Title'>{$data['title']}</textarea>";
        echo "<textarea class='text1 textwide marginbone' name='pbl_resource[res_$cnt][by]' placeholder='By'>{$data['by']}</textarea>";
        echo "<textarea class='text1 textwide' name='pbl_resource[res_$cnt][url]' placeholder='Site URL'>{$data['url']}</textarea>"; ?>
      </div>
      <div class='col span_1_of_12'>
        <span><i class='fa fa-minus-circle meta-minus delete-resource'></i></span>
      </div>
    </div><?php
  }

  public function save_meta_boxes($post_id) {
    if (!isset($_POST['pbl_on_the_boards_nonce'])) return;
    if (!current_user_can('edit_post',$post_id))   return;
    if (wp_is_post_autosave($post_id))             return;
    if (wp_is_post_revision($post_id))             return;
    if (!wp_verify_nonce($_POST['pbl_on_the_boards_nonce'],basename(__FILE__))) return;
    if (isset($_POST['pbl_board_title'])) {
      $posted = $_POST['pbl_board_title'];
      $title  = esc_textarea($posted);
      update_post_meta($post_id,'pbl_board_title',$title);
    }
    if (isset($_POST['pbl_gallery'])) {
      $posted  = $_POST['pbl_gallery'];
      $text    = (isset($posted['text'])) ? esc_textarea($posted['text']) : '';
      $gallery = array('text'   => $text,
                       'images' => array());
      unset($posted['images']['image_clone']);
      foreach($posted['images'] as $key=>$image) {
        if (empty($image)) continue;
        $gallery['images'][] = esc_url_raw($image);
      }
      update_post_meta($post_id,'pbl_board_gallery',$gallery);
    }
    if (isset($_POST['pbl_resource'])) {
      $posted    = $_POST['pbl_resource'];
      $resources = array();
      unset($posted['res_clone']);
      foreach($posted as $key=>$item) {
        $resources[] = array('image' => esc_url_raw($item['image']),
                             'title' => sanitize_text_field($item['title']),
                             'by'    => sanitize_text_field($item['by']),
                             'url'   => esc_url_raw($item['url']));
      }
      update_post_meta($post_id,'pbl_board_resources',$resources);
    }
  }

}
