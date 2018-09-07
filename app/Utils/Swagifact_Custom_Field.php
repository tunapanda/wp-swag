<?php
namespace Swag\App\Utils;

use \Swag\App\Models\H5P;

/**
 * A swagifact custom field for ACF
 */
class Swagifact_Custom_Field extends \acf_field {
  public function initialize() {
    $this->name = 'swagifacts';
    $this->label = __('Swagifacts', 'swag');
    $this->category = 'relational';

    $this->defaults = array(
			'allow_null' 	=> 0,
			'multiple'		=> 0,
			'return_format'	=> 'object',
			'ui'			=> 1,
    );

    		// extra
		add_action('wp_ajax_acf/fields/swagifacts/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/swagifacts/query',	array($this, 'ajax_query'));
  }

  function ajax_query() {
		
		// validate
		if( !acf_verify_ajax() ) die();
		
		
		// get choices
		$response = $this->get_ajax_query( $_POST );
		
		
		// return
		acf_send_ajax_results($response);
			
  }
  
  function get_ajax_query( $options = []) {
    $options = acf_parse_args($options, array(
			'post_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
			'paged'			=> 1
    ));
    
    $field = acf_get_field( $options['field_key'] );
		if( !$field ) return false;
		
		
		// vars
   		$results = array();
		$args = array();
		$s = false;
		$is_search = false;
		
		
   		// paged
   		$args['posts_per_page'] = 20;
   		$args['paged'] = $options['paged'];
   		
   		
   		// search
		if( $options['s'] !== '' ) {
			
			// strip slashes (search may be integer)
			$s = wp_unslash( strval($options['s']) );
			
			
			// update vars
			$args['s'] = $s;
			$is_search = true;
			
    }
    
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}h5p_contents", ARRAY_A);
    $data = [];

    foreach($results as $h5p) {
      $data[$h5p['id']] = $h5p['title'];
    }

		$response = array(
			'results'	=> $data,
			'limit'		=> $args['posts_per_page']
		);
		
		
		// return
		return $response;
  }

  public function render_field($field) {
    global $wpdb;
    $field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = [];

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}h5p_contents", ARRAY_A);

    foreach($results as $h5p) {
      $field['choices'][$h5p['id']] = $h5p['title'];
    }

    var_dump($field);
    acf_render_field( $field );
  }

  public function render_field_settings($field) {
    		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
  }

  function load_value( $value, $post_id, $field ) {
		
		// ACF4 null
		if( $value === 'null' ) return false;
		
		
		// return
		return $value;
		
  }
  
  function format_value( $value, $post_id, $field ) {
		
    $h5p = H5P::get_by_id($value);
    
    return $h5p;
  }
  
  function update_value($value, $post_id, $field) {
    $data = [];
    if (!is_array($value)) {

    }
    foreach($value as $i => $h5p) {
      $data[$i] = $h5p['id'];
    }

    $value = array_map('strval', $data);

    return $value;
  }
}