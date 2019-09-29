<?php

class TCC_Role_Agent {

	private $fields = array();

	use TCC_Trait_Singleton;

	protected function __construct() {
		if ( is_admin() ) {
			add_filter( 'user_contactmethods',      [ $this, 'user_contactmethods' ] );
			add_action( 'personal_options',         [ $this, 'personal_options' ], 9 );
			add_action( 'personal_options_update',  [ $this, 'save_agent_information' ] );
			add_action( 'edit_user_profile_update', [ $this, 'save_agent_information' ] );
		}
		if ( tcc_estate( 'register' ) === 'agents' ) {
#			add_filter( 'edit_profile_url',       [ $this, 'edit_profile_url' ], 10, 3 );
			add_filter( 'tcc_login_username',     [ $this, 'login_prefix' ] );
			add_filter( 'tcc_login_password',     [ $this, 'login_prefix' ] );
			add_filter( 'tcc_login_widget_title', [ $this, 'login_prefix' ] );
		}
		add_filter( 'author_rewrite_rules', [ $this, 'agent_rewrite_rules' ] );
		add_filter( 'query_vars',           [ $this, 'query_vars' ] );
		add_filter( 'template_include',     [ $this, 'template_include' ] );
		$this->fields = $this->get_field_titles();
	}

	public function login_prefix( $input ) {
		$title  = _x( 'Agent', 'noun - user role, prefixed to login placeholder string', 'tcc-plugin' );
		$format = _x( '%1$s %2$s', '1 - noun serving as an adjective, 2 - primary noun', 'tcc-plugin' );
		return sprintf( $format, $title, $input );
	}


	/**  Agent field info  **/

	private function get_field_titles() {
		return array(
			'job_title'      => __( 'Job Title', 'tcc-plugin' ),
			'education'      => __( 'Education One', 'tcc-plugin' ),
			'edu_two'        => __( 'Education Two', 'tcc-plugin' ),
			'edu_three'      => __( 'Education Three', 'tcc-plugin' ),
			#  TODO:  allow for variable number
			'certifications' => __( 'Certifications / Affiliations', 'tcc-plugin' ),
			'certi_two'      => __( 'Certs / Affiliations Two', 'tcc-plugin' ),
			'certi_three'    => __( 'Certs / Affiliations Three', 'tcc-plugin' ),
			#  TODO:  allow for variable number
			'languages'      => __( 'Language One', 'tcc-plugin' ),
			'lang_two'       => __( 'Language Two', 'tcc-plugin' ),
			'lang_three'     => __( 'Language Three', 'tcc-plugin' ),
			'telephone'      => __( 'Telephone', 'tcc-plugin' ),
			'facebook'       => __( 'Facebook username', 'tcc-plugin' ),
			'twitter'        => __( 'Twitter handle',  'tcc-plugin' ),
			'linkedin'       => __( 'LinkedIN Profile', 'tcc-plugin' ),
			'website_image'  => __( 'Website Image', 'tcc-plugin' ),
		);
	}


  /**  Agent template  **/

	public function agent_rewrite_rules( $current ) {
		$rules = array(
			array(
				'regex'    => 'agent/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$',
				'redirect' => 'index.php?author_name=$matches[1]&agent=true&feed=$matches[2]',
			),
			array(
				'regex'    => 'agent/([^/]+)/(feed|rdf|rss|rss2|atom)/?$',
				'redirect' => 'index.php?author_name=$matches[1]&agent=true&feed=$matches[2]',
			),
			array(
				'regex'    => 'agent/([^/]+)/embed/?$',
				'redirect' => 'index.php?author_name=$matches[1]&embed=true&agent=true',
			),
			array(
				'regex'    => 'agent/([^/]+)/page/?([0-9]{1,})/?$',
				'redirect' => 'index.php?author_name=$matches[1]&paged=$matches[2]&agent=true',
			),
			array(
				'regex'    => 'agent/([^/]+)/?$',
				'redirect' => 'index.php?author_name=$matches[1]&agent=true',
			),
		);
		foreach( $rules as $rule ) {
			$current[ $rule['regex'] ] = $rule['redirect'];
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
    if($agent && $name) {
      $template = get_template_directory().'/author.php';
    }
    return $template;
}


  /**  Agent Profile functions  **/

	public function user_contactmethods( $profile_fields, $user = null ) {
		if ( $user && in_array( 'agent', $user->roles ) ) {
			$fields = array( 'telephone', 'facebook', 'twitter', 'linkedin' );
			foreach( $fields as $field ) {
				if ( ! array_key_exists( $field, $profile_fields ) ) {
					$profile_fields[ $field ] = $this->fields[ $field ];
				}
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
              <label for="<?php e_esc_attr( $field); ?>"><?php e_esc_html( $this->fields[$field] ); ?></label>
            </th>
            <td>
              <input type="text" class="regular-text" name="<?php e_esc_attr( $field ); ?>" value="<?php e_esc_attr( $value ); ?>" />
            </td>
          </tr>
        </table><?php
      }
    }
  }

	protected function agent_image_text() {
		return array(
			'assign' => __('Assign Image','tcc-plugin'),
			'upload' => __('Assign/Upload Image','tcc-plugin'),
		);
	}

  protected function agent_image($user) {
    if (in_array('agent',$user->roles)) {
      $image = get_user_meta($user->ID,'website_image');
		$text  = $this->agent_image_text();
      $url   = ($image) ? $image[0] : ''; ?>
      <table class="form-table">
        <tr>
          <th>
            <label for="website_image"><?php e_esc_html( $this->fields['website_image'] ); ?></label>
          </th>
          <td>
            <div data-title='<?php echo esc_attr( $text['upload'] ); ?>' data-button='<?php echo esc_attr( $text['assign'] ); ?>'>
              <button class='tcc-image'><?php echo esc_attr( $text['assign'] ); ?></button>
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
    foreach($this->fields as $field=>$title) {
#error_log("field: $field");
      if ( array_key_exists( $field, $_POST ) ) {
#error_log($_POST[$field]);
        update_user_meta($user_id,$field,sanitize_text_field($_POST[$field])); } } /*
    if ( array_key_exists( 'website_image', $_POST ) ) {
      update_user_meta($user_id,'website_image',sanitize_text_field($_POST['website_image'])); } //*/
  }


}
