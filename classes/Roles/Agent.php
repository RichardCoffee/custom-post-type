<?php

if (!class_exists('TCC_Roles_Agent')) {

class TCC_Roles_Agent {

  private $fields = array();

  public function __construct() {
    if (is_admin()) {
      add_filter('user_contactmethods',     array($this,'user_contactmethods'));
      add_action('personal_options',        array($this,'personal_options'),9);
      add_action('personal_options_update', array($this,'save_agent_information'));
      add_action('edit_user_profile_update',array($this,'save_agent_information'));
    }
    add_filter('author_rewrite_rules',array($this,'author_rewrite_rules'));
    add_filter('query_vars',          array($this,'query_vars'));
    add_filter('template_include',    array($this,'template_include'));
    $this->fields = $this->get_field_titles();
  }


  /**  Agent field info  **/

  private function get_field_titles() {
    return array('job_title'      => __('Job Title', 'tcc-real-estate'),
                 'education'      => __('Education One', 'tcc-real-estate'),
                 'edu_two'        => __('Education Two', 'tcc-real-estate'),
                 'edu_three'      => __('Education Three', 'tcc-real-estate'),
                 'certifications' => __('Certifications / Affiliations', 'tcc-real-estate'),
                 'certi_two'      => __('Certifications Two', 'tcc-real-estate'),
                 'certi_three'    => __('Certifications Three', 'tcc-real-estate'),
                 'languages'      => __('Language One', 'tcc-real-estate'),
                 'lang_two'       => __('Language Two', 'tcc-real-estate'),
                 'lang_three'     => __('Language Three', 'tcc-real-estate'),
                 'telephone'      => __('Telephone','tcc-real-estate'),
                 'facebook'       => __('Facebook username', 'tcc-real-estate'),
                 'twitter'        => __('Twitter handle',  'tcc-real-estate'),
                 'linkedin'       => __('LinkedIN Profile', 'tcc-real-estate'),
                 'website_image'  => __('Website Image', 'tcc-real-estate'));
  }


  /**  Agent template  **/

  public function author_rewrite_rules($current) {
    $rules = array(array('regex'    => 'agent/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$',
                         'redirect' => 'index.php?author_name=$matches[1]&agent=true&feed=$matches[2]'),
                   array('regex'    => 'agent/([^/]+)/(feed|rdf|rss|rss2|atom)/?$',
                         'redirect' => 'index.php?author_name=$matches[1]&agent=true&feed=$matches[2]'),
                   array('regex'    => 'agent/([^/]+)/embed/?$',
                         'redirect' => 'index.php?author_name=$matches[1]&embed=true&agent=true'),
                   array('regex'    => 'agent/([^/]+)/page/?([0-9]{1,})/?$',
                         'redirect' => 'index.php?author_name=$matches[1]&paged=$matches[2]&agent=true'),
                   array('regex'    => 'agent/([^/]+)/?$',
                         'redirect' => 'index.php?author_name=$matches[1]&agent=true'));
    foreach($rules as $rule) {
      $current[$rule['regex']] = $rule['redirect'];
    }
    return $current;
  }

  public function query_vars($vars) {
    $vars[] = 'agent';
    return $vars;
  }

  public function template_include($template) {
    $agent = get_query_var('agent', null);
    $name  = get_query_var('author_name', null);
    #tcc_log_entry("agent: $agent  name: $name");
    if($agent && $name) {
      $template = get_template_directory().'/author.php';
    }
    #tcc_log_entry("template: $template");
    return $template;
}


  /**  Agent Profile functions  **/

  public function user_contactmethods($profile_fields,$user=null) {
    if (!$user || in_array('agent',$user->roles)) {
      $fields = array('telephone','facebook','twitter','linkedin');
      foreach($fields as $field) {
        if (!isset($profile_fields[$field])) $profile_fields[$field] = $this->fields[$field];
      }
    }
    return $profile_fields;
  }

  public function personal_options($user) {
    if (in_array('agent',$user->roles)) {
      $this->agent_image($user);
      $fields = array('job_title','education','edu_two','edu_three','certifications','certi_two','certi_three','languages','lang_two','lang_three');
      foreach($fields as $field) {
        $array = get_user_meta($user->ID,$field);
        $value = (empty($array)) ? '' : $array[0]; ?>
        <table class="form-table">
          <tr>
            <th>
              <label for="<?php echo $field; ?>"><?php echo $this->fields[$field]; ?></label>
            </th>
            <td>
              <input type="text" class="regular-text" name="<?php echo $field; ?>" value="<?php echo $value; ?>" />
            </td>
          </tr>
        </table><?php
      }
    }
  }

  private function agent_image($user) {
    #tcc_log_entry('profile user',$user,"user ID: {$user->ID}",get_user_meta($user->ID));
    if (in_array('agent',$user->roles)) {
      $image = get_user_meta($user->ID,'website_image');
      $url   = ($image) ? $image[0] : ''; ?>
      <table class="form-table">
        <tr>
          <th>
            <label for="website_image"><?php echo $this->fields['website_image']; ?></label>
          </th>
          <td>
            <div data-title='<?php _e('Assign/Upload Image','tcc-theme-options'); ?>' data-button='<?php _e('Assign Image','tcc-theme-options'); ?>'>
              <button class='tcc-image'><?php _e('Assign Image','tcc-theme-options'); ?></button>
              <input type='hidden' name='website_image' value='<?php echo esc_url($url); ?>' />
              <div>
                <img class='tcc-image-size' src='<?php echo esc_url($url); ?>'>
              </div>
            </div>
          </td>
        </tr>
      </table><?php //*/
    }
  }

  public function save_agent_information($user_id) {
#tcc_log_entry($_POST);
    foreach($this->fields as $field=>$title) {
#error_log("field: $field");
      if (isset($_POST[$field])) {
#error_log($_POST[$field]);
        update_user_meta($user_id,$field,sanitize_text_field($_POST[$field])); } } /*
    if (isset($_POST['website_image'])) {
      update_user_meta($user_id,'website_image',sanitize_text_field($_POST['website_image'])); } //*/
  }


}

}
