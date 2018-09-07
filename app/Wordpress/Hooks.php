<?php

namespace Swag\App\Wordpress;

$project_root = dirname(dirname(__FILE__));

/**
 * Register the post types and taxonomies
 */
add_action('init', function() {
  register_post_type("swagpath", [
    "labels" => [
      "name" => "Swagpaths",
      "singular_name" => "Swagpath",
      "not_found" => "No swagpaths found.",
      "add_new_item" => "Add Swagpath",
      "edit_item" => "Edit Swagpath",
    ],
    "description" => "A swagpath is a course in the system. It is named this way because it takes the user from one swag to another, i.e. the path from one swag to another. Each swagpath can have a number of required swag, i.e. the prerequisites for the swagpath. It can also have a number of provided swag, which are the badges that the user will earn upon completing the swagpath.",
    "public" => true,
    "hierarchical" => false,
    "show_in_menu" => "swag",
    "show_in_nav_menus" => false,
    "show_in_rest" => true,
    "supports" => ["title", "excerpt", "comments"],
    "taxonomies" => ["swagtrack"],
    "has_archive" => true
  ]);

  register_post_type("swagifact", [
    'labels' => [
      'name' => 'Swagifacts',
      'singular_name' => 'Swagifact'
    ],
    'public' => true,
    'show_in_menu' => false,
    "supports" => ["title", "excerpt"]
  ]);
  
  register_taxonomy("swagtrack", "swagpath", [
    "labels" => [
      "name" => "Swagtracks",
      "singular_name" => "Swagtrack",
    ],
    "description" => "Collections of swagpaths that are related.",
    "public" => true,
    "hierarchical" => true,
    "show_in_rest" => true,
  ]);
});

/**
 * makes sure swag menu is open in the admin when editing a swagtrack
 */
add_filter( 'parent_file', function( $parent_file ) {
  global $submenu_file, $current_screen, $pagenow;

  if ( $current_screen->taxonomy == 'swagtrack' ) {
    $parent_file = 'swag';
  }

  return $parent_file;
} );


/**
 * Create table to track progress
 */
register_activation_hook( $project_root . 'plugin.php', function() {
  global $wpdb;

  $charset_collate = $wpdb->get_charset_collate();

  $swagpath_status = $wpdb->prefix . "swagpath_status";
  $swagifact_status = $wpdb->prefix . "swagifact_status";

  $swagpath_has_swagifacts = $wpdb->prefix . "swagpath_swagifacts";

  $sql = "CREATE TABLE $swagpath_status (
    `id` mediumint(9) NOT NULL AUTO_INCREMENT,
    `swagpath_id` mediumint(9) NOT NULL,
    `user_id` mediumint(9) NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` text DEFAULT 'attempted',
    PRIMARY KEY (id)
    UNIQUE KEY (`swagpath_id`)
  ) $charset_collate;";

  $sql .= "CREATE TABLE $swagifact_status (
    `id` mediumint(9) NOT NULL AUTO_INCREMENT,
    `swagpath_id` mediumint(9) NOT NULL,
    `user_id` mediumint(9) NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` text DEFAULT 'attempted',
    PRIMARY KEY (id)
    UNIQUE KEY (`swagifact_id`)
  ) $charset_collate;";

  $sql .= "CREATE TABLE $swagpath_has_swagifacts (
    `swagifact_id` mediumint(9) NOT NULL,
    `swagpath_id` mediumint(9) NOT NULL,
    `order` mediumint(9) NOT NULL,
    `type` text,
    PRIMARY KEY (swagifact_id, swagpath_id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
});

add_action('wp_enqueue_scripts', function () use ($project_root) {
  global $post;

  wp_enqueue_script('dist/main.js', plugins_url('dist/main.js', $project_root), ['jquery'], null, true);

  wp_localize_script('dist/main.js', 'api_settings', [
    "ajax_url" => '/wp-json/swag/v1/swagifact/progress',
    "swagpath_id" => $post->ID,
    "swagifact_slug" => get_query_var('swagifact'),
    "nonce" => wp_create_nonce( 'wp_rest' )
  ]);
});

/**
 * Path to ACF files
 */
add_filter('acf/settings/path', function ($path) use ($project_root) {
  return plugins_url('/acf/', $project_root);
});

add_filter('acf/settings/dir', function ($dir) use ($project_root) {
  return plugins_url('/acf/', $project_root);
});

/**
 * load H5Ps into the select box for swagifacts
 */
add_filter('acf/load_field/name=swagifacts', function( $field ) {
  global $wpdb;

  $field['choices'] = [];

  $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}h5p_contents", ARRAY_A);

  foreach($results as $h5p) {
    $field['choices'][$h5p['id']] = $h5p['title'];
  }

  return $field;
});

// add_filter('acf/load_field/name=swagifact_content', function( $field ) {
//   global $wpdb;

//   $field['choices'] = [];

//   $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}h5p_contents", ARRAY_A);

//   foreach($results as $h5p) {
//     $field['choices'][$h5p['id']] = $h5p['title'];
//   }

//   return $field;
// });

// if (false) {
//   global $wpdb;

//   $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}post_meta WHERE meta_key = swagifact", ARRAY_A);

//   foreach($results as $result) {
//     $old_swagifacts = 
//   }

// }