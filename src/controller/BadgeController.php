<?php 

class BadgeController {
  function __construct() {
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

    add_filter("rwmb_meta_boxes",array($this ,'meta_boxes'));
  }

  public function meta_boxes($meta_boxes) {
    $meta_boxes[] = array(
			"title" => "Open Badges",
			"post_types" => "badge",
			"priority" => "low",
			"fields" => array(
				
				array(
					"id" => "badge_image",
					"type" => "image_upload",
					"name" => "Badge Image",
					"max_file_uploads" => 1
				)
			)
    );
    return $meta_boxes;
  }
}

add_action("init", function() {
  new BadgeController();
});