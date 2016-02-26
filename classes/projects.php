<?php

require_once('custom-post.php');

class Project_Types extends Custom_Post_Type {

  public function __construct() {
    $data = array('type'      => 'project',
                  'label'     => _x('Project Type','singular form','creatom'),
                  'plural'    => _x('Project Types','plural form','creatom'),
                  'descrip'   => __('Project Type Deep Dives','creatom'),
                  'position'  => 6,
                  'templates' => array('single' => WP_PLUGIN_DIR.'/creatombuilder/templates/single-project.php'));
#                  'columns'   => array('remove' => array('categories')));
    parent::__construct($data);
    add_action('admin_enqueue_scripts',array($this,'enqueue_styles'),11);
    add_action('add_meta_boxes',       array($this,'add_meta_boxes'));
    add_action('save_post',            array($this,'save_meta_boxes'));
    add_action('admin_init', array($this,'check_caps'),20);
  }

  public function check_caps() {
    $roles = array('contributor','author','editor','administrator');
    foreach($roles as $role) {
      log_entry($role,get_role($role));
      }
  }

  public function enqueue_styles() {
    $screen = get_current_screen();
    if(($screen->post_type==='project') || ($screen->post_type==='board')) {
      wp_enqueue_media();
      wp_register_script('cb_postmeta',PBL_URL.'js/post-meta.js', array('jquery','cblibrary'), null, true); // cblibrary provided by pbl_content plugin
      wp_enqueue_script('cb_postmeta');
      wp_enqueue_style('cb_meta_styles',PBL_URL.'css/meta-styles.css');
      wp_enqueue_style('columns');  // provided by pbl_content plugin
      wp_enqueue_style('font-awe'); // provided by pbl_content plugin
    }
  }

  // http://themefoundation.com/wordpress-meta-boxes-guide/
  public function add_meta_boxes() {
    add_meta_box('pbl_icon',  __( 'Post Icon',    'creatom'),array($this,'select_icon'),  'project','side',  'high' );
    add_meta_box('pbl_sub',   __( 'Subtitle',     'creatom'),array($this,'sub_title'),    'project','side',  'high' );
    add_meta_box('pbl_links', __( 'Example Links','creatom'),array($this,'example_links'),'project','normal','high' );
    add_meta_box('pbl_asides',__( 'Gallery',      'creatom'),array($this,'gallery'),      'project','normal','high' );
    remove_meta_box('categorydiv','project','side');
    remove_meta_box('wordpress-https','project','side');
#    remove_meta_box('postimagediv','project','normal');
  }

  public function select_icon($post) {
    wp_nonce_field(basename(__FILE__),'pbl_project_type_nonce');
    $icon_value = get_post_meta($post->ID,'pbl_post_icon',true);
    $icon_array = $this->generate_icon_array();
    $html = "<select id='pbl_icon' name='pbl_post_icon'>";
    foreach($icon_array as $icon) {
      $select = ($icon['icon']===$icon_value) ? 'selected' : '';
      $html  .= "<option value='{$icon['icon']}' $select> {$icon['title']} </option>";
    }
    $html .= "</select>";
    echo $html;
  }

  private function generate_icon_array() {
    $content = pbl_get_content('project');
    $icons   = array();
    foreach($content as $cdata) {
      if (!isset($cdata['templates'])) continue;
      foreach($cdata['templates'] as $template) {
        $icons[] = array('title'=>$template['title'],'icon'=>$template['icon']);
      }
    }
    usort($icons,array($this,'sort_icon_array'));
    return $icons;
  }

  public function sort_icon_array($a,$b) {
    if ($a['title']==$b['title']) return 0;
    return ($a['title']>$b['title']) ? 1 : -1;
  }

  public function sub_title($post) {
    $pbl_sub_title = get_post_meta($post->ID,'pbl_sub_title',true);
    $category_ID   = get_cat_ID('Project Types');
    echo "<div class='hidden'><input id='in-category-$category_ID' type='checkbox' name='post_category[]' value='$category_ID' checked></div>";
    echo "<p><textarea class='text1 textwide' name='pbl_sub_title'>$pbl_sub_title</textarea></p>";
  }

  public function example_links($post) {
    $links   = get_post_meta($post->ID,'pbl_meta_links',true);
    $linkcnt = (is_array($links)) ? count($links) : 0; ?>
    <div id='meta-links' data-cnt='<?php echo $linkcnt+1; ?>'>
      <div id='link-clone' class='hidden'><?php
        $this->generate_single_link(); ?>
      </div>
      <div class='section group'>
        <div class='col span_3_of_12 centered'>Link Name</div>
        <div class='col span_3_of_12 centered'>Link URL</div>
      </div><?php
      for($i=0;$i<$linkcnt;$i++) {
        $this->generate_single_link($links[$i],$i);
      } ?>
      <div id='AddLink' class='section group'>
        <div class='col span_11_of_12'></div>
        <div class='col span_1_of_12'>
          <i class='fa fa-plus-circle meta-plus' onclick='addMetaLink(this);'></i>
        </div>
      </div>
    </div><?php
  }

  private function generate_single_link($data=array(),$cnt='clone') {
    if (empty($data)) $data = array('name'=>'','url'=>'') ?>
    <div class='section group'>
      <div class='col span_3_of_12'><?php
        echo "<textarea class='text1 textwide' name='pbl_links[link_$cnt][name]' placeholder='Name'>{$data['name']}</textarea>"; ?>
      </div>
      <div class='col span_8_of_12'><?php
        echo "<textarea class='text1 textwide' name='pbl_links[link_$cnt][url]' placeholder='URL'>{$data['url']}</textarea>"; ?>
      </div>
      <div class='col span_1_of_12'>
        <i class='fa fa-minus-circle meta-minus delete-link'></i>
      </div>
    </div><?php
  }

  public function gallery($post) {
    $asides   = get_post_meta($post->ID,'pbl_asides',true);
    $asides   = (is_array($asides)) ? $asides : array('title'=>'');
    $asidecnt = count($asides);
    $title    = __('Assign/Upload Gallery Image','creatom');
    $button   = __('Assign Image','creatom');
    $metadata = "data-cnt='$asidecnt' data-post='{$post->ID}' data-title='$title' data-button='$button'"; ?>
    <div id='meta-asides' <?php echo $metadata; ?>>
      <div id='gallery-clone' class='hidden'><?php
        $this->generate_single_gallery(array('image'=>'','text'=>''),'clone'); ?>
      </div>
      <div class='section group'>
        <div class='col span_2_of_12 pull-left'>Title:</div>
        <div class='col span_8_of_12 centered'><?php
          echo "<textarea class='text1 textwide' name='asides[title]'>{$asides['title']}</textarea>"; ?>
        </div>
      </div><?php
      $cnt = 0;
      foreach($asides as $key=>$aside) {
        if (is_string($aside)) continue;
        $this->generate_single_gallery($aside,$cnt++);
      } ?>
      <div id='AddAside' class='section group asidelast'>
        <div class='col span_10_of_12'></div>
        <div class='col span_2_of_12'>
          <i class='fa fa-plus-circle meta-plus' onclick='addAside(this);'></i>
        </div>
      </div>
    </div><?php
  }

  private function generate_single_gallery($data,$cnt) { ?>
    <div class='section group aside'>
      <div class='col span_4_of_12'><?php
        echo "<input type='text' class='hidden' name='asides[aside_$cnt][image]' value='{$data['image']}' />";
        echo "<button class='aside_image'>Assign/Upload Image</button>";
        echo "<div><img class='meta-image' src='{$data['image']}'/></div>"; ?>
      </div>
      <div class='col span_6_of_12'><?php
        echo "<textarea class='text7 textwide' name='asides[aside_$cnt][text]'>{$data['text']}</textarea>"; ?>
      </div>
      <div class='col span_2_of_12'>
        <i class='fa fa-minus-circle meta-minus delete-aside' ></i>
      </div>
    </div><?php
  }

  public function save_meta_boxes($post_id) {
    if ( !isset($_POST['pbl_project_type_nonce'])) return;
    if ( !current_user_can('edit_post',$post_id))  return;
    if (wp_is_post_autosave($post_id))            return;
    if (wp_is_post_revision($post_id))            return;
    if ( !wp_verify_nonce($_POST['pbl_project_type_nonce'],basename(__FILE__))) return;
    if (isset($_POST['pbl_post_icon'])) {
      update_post_meta($post_id,'pbl_post_icon',sanitize_text_field($_POST['pbl_post_icon']));
    }
    if (isset($_POST['pbl_sub_title'])) {
      update_post_meta($post_id,'pbl_sub_title',sanitize_text_field($_POST['pbl_sub_title']));
    }
    if (isset($_POST['pbl_links'])) {
      $data  = $_POST['pbl_links'];
      $links = array();
      unset($data['link_clone']);
      foreach($data as $key=>$link) {
        $links[] = array('name' => esc_textarea($link['name']),
                         'url'  => esc_url_raw($link['url']));
      }
      update_post_meta($post_id,'pbl_meta_links',$links);
    }
    if (isset($_POST['asides'])) {
      $data   = $_POST['asides'];
      $asides = array();
      $asides['title'] = esc_textarea($data['title']);
      unset($data['aside_clone']);
      foreach($data as $aside) {
        if (is_string($aside)) continue;
        $asides[] = array('image' => esc_url_raw($aside['image']),
                          'text'  => esc_textarea($aside['text']));
      }
      update_post_meta($post_id,'pbl_asides',$asides);
    }
  }


}

$project_types = new Project_Types();
