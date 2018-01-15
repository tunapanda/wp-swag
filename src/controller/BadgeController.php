<?php 

class BadgeController {
	var $issuer = array(
		"@context" => "https://w3id.org/openbadges/v2",
		"description" => "We focus on low-cost replicability of training facilities and can offer turnkey solutions for learning. We enable anyone to open schools and create learning experiences in their communities using our unique operating pedagogy, open source tools, offline education networks, and more.",
		"url" => "http://tunapanda.org",
		"email" => "info@tunapanda.org",
		"type" => "Issuer",
		"id" => "https://api.badgr.io/public/issuers/_AHjGP_pRouHnxLczMK0eQ?v=2_0",
		"name" => "Tunapanda",
		"image" => "https://api.badgr.io/public/issuers/_AHjGP_pRouHnxLczMK0eQ/image"
	);

  function __construct() {
		$this->settings_api = new WeDevs_Settings_API;

    register_post_type( "badge", array(
      "labels" => array(
				"name" => "Badges",
				"singular_name" => "Badge",
				"not_found" => "No badges found.",
				"add_new_item" => "Add new Badge",
				"edit_item" => "Edit Badge"
			),
			"public" => true,
			"has_archive" => false,
			"supports" => array("title", "editor"),
      "show_in_nav_menus" => false
		));
		
		add_filter('rwmb_badge_json_field_meta', array( $this, 'badge_json'), 10, 3);
		add_filter("rwmb_meta_boxes",array($this,'meta_boxes'), 10, 1);

		add_action( 'admin_init', array($this, 'admin_init') );
		add_action( 'admin_menu', array($this, 'admin_menu') );
	}

	public function admin_init() {
		$this->settings_api->set_sections( array(
			array(
				'id' => 'open_badges_issuer',
				'title' => 'Open Badges Issuer'
			)
		));

		$this->settings_api->set_fields( array(
			'open_badges_issuer' => array(
				array(
					'name' => 'issuer_name',
					'label' => __( 'Issuer Name', 'swag' ),
					'type' => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name' => 'issuer_description',
					'label' => __( 'Issuer Description', 'swag' ),
					'type' => 'textarea',
					'sanitize_callback' => 'sanitize_text_field',
				),
				array(
					'name' => 'issuer_url',
					'label' => __( 'Issuer URL', 'swag' ),
					'type' => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name' => 'issuer_email',
					'label' => __( 'Issuer Email', 'swag' ),
					'type' => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				)
			)
		));

		$this->settings_api->admin_init();
	}

	public function admin_menu() {
		add_options_page( 'Open Badges', 'Open Badges', 'edit_posts', 'swag_open_badges', array($this, 'settings_menu'), '' );		
	}
	
	public function meta_boxes( $meta_boxes ) {
		$meta_boxes[] = array(
			'title' => "Badges",
			"post_types" => "badge",
			"priority" => "high",
			"fields" => array(
				array(
					"id" => 'badge_image',
					"name" => "Image",
					"type" => "image_advanced",
					"max_file_uploads" => 1
				),
				array(
					"id" => "badge_json",
					"name" => "JSON for Badge",
					"type" => "textarea",
					"rows" => 6
				)
			)
		);
		return $meta_boxes;
	}

	function settings_menu() {
		echo '<div class="wrap">';
		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();
		echo '</div>';
	}
 	
	public function badge_json($new, $field, $old) {
		//generated read-only field
		global $post;

		$name = get_the_title( $post->id );
		$desc = 		$desc = apply_filters('the_content', get_post_field('post_content', $post->id));		
		$image = rwmb_meta('badge_image', array( "size" => "large"), $post->id);
		$image_url = $image ? $image['url'] : '';
		$permalink = get_permalink($post->id);

		$json = array(
			"@context" => "https://w3id.org/openbadges/v2",
			"description" => $desc,
			"type" => "BadgeClass",
			"id" => $permalink,
			"name" => $name,
			"issuer" => "https://api.badgr.io/public/issuers/_AHjGP_pRouHnxLczMK0eQ?v=2_0",
			"image" => $image_url,
			"criteria" => array(
				"id" => "http://swag.tunapanda.org/swagpath/sonic-pi/",
				"narrative" => "Compete the course at http://swag.tunapanda.org/swagpath/sonic-pi/"
			),
			"alignment" => array(),
			"tags" => array()
		);

		return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
}

add_action("init", function() {
  new BadgeController();
});