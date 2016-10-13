<?php

require_once __DIR__."/../utils/Singleton.php";

use swag\Singleton;

/**
 * Edit and show swagpaths.
 */
class SwagpathController extends Singleton {

	/**
	 * Init
	 */
	public function init() {
		register_post_type("swagpath",array(
			"labels"=>array(
				"name"=>"Swagpaths",
				"singular_name"=>"Swagpath",
				"not_found"=>"No swagpaths found.",
				"add_new_item"=>"Add new swagpath"
			),
			"public"=>true,
			"has_archive"=>true,
			"supports"=>array("title","excerpt"),
		));

		//add_action('add_meta_boxes',array($this,'addMetaBox'));
		add_filter("rwmb_meta_boxes",array($this,'rwmbMetaBoxes'));
	}

	public function rwmbMetaBoxes($metaBoxes) {
		global $wpdb;

		$options=array();
		$h5ps=$wpdb->get_results("SELECT slug,title FROM {$wpdb->prefix}h5p_contents",ARRAY_A);

		foreach ($h5ps as $h5p) {
			$options[$h5p["slug"]]="H5P: ".$h5p["title"];
		}

		$metaBoxes[]=array(
	        'title'      => __( 'Swagifacts', 'textdomain' ),
	        'post_types' => 'swagpath',
	        'fields'     => array(
	            array(
	                'type' => 'select_advanced',
	                'id'   => 'swagifact',
	                'name' => "Swagifacts",
	                "clone"=>true,
	                "sort_clone"=>true,
	                "std"=>"hello",
	                "options"=>$options
	            ),
	        ),
		);

		$metaBoxes[]=array(
	        'title'      => "Swag",
	        'post_types' => 'swagpath',
	        'fields'     => array(
	            array(
	                'id'   => 'provides',
	                'name' => 'Provides',
	                'type' => 'text',
	                "size"=>20,
	                "clone"=>true,
	                "desc"=>
	                	"The swag badges the user will receive upon completing the swagpath. ".
	                	"Swags are written on the same form as a newsgroup name, e.g. tech.programming.php"
	            ),
	            array(
	                'id'   => 'requires',
	                'name' => 'Requires',
	                'type' => 'text',
	                "clone"=>true,
	                "size"=>20,
	                "desc"=>
	                	"The swag that is recommended for the user to complete before attempting this swagpath."
	            ),
	        ),
		);

		$metaBoxes[]=array(
			"title"=>"Lesson plan",
			"post_types"=>"swagpath",
			"priority"=>"low",
			"fields"=>array(
				array(
					"id"=>"lessonplan",
					"type"=>"file_upload",
					"name"=>"Lesson plan",
					"max_file_uploads"=>1
				)
			)
		);

		return $metaBoxes;
	}
}

add_filter("rwmb_meta_boxes",array(SwagpathController::instance(),'rwmbMetaBoxes'));
