<?php

require_once __DIR__."/../utils/Singleton.php";
require_once __DIR__."/../model/Swagpath.php";
require_once __DIR__."/../controller/SwagTrackController.php";
require_once ABSPATH."wp-admin/includes/plugin.php";

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
				"add_new_item"=>"Add new swagpath",
				"edit_item"=>"Edit Swagpath",
			),
			"public"=>true,
			"has_archive"=>true,
			"supports"=>array("title","excerpt"),
			"show_in_nav_menus"=>false
		));

		add_filter("template_include",array($this,"templateInclude"));
	}

	/**
	 * Template include hook. Always use custome page template for swagpaths.
	 */
	public function templateInclude($template) {
		$post=get_post();
		if ($post->post_type!="swagpath")
			return $template;

		$template=__DIR__."/../../tpl/swagpath_page.php";

		return $template;
	}

	/**
	 * Do meta boxes, meta-box style.
	 */
	public function rwmbMetaBoxes($metaBoxes) {
		global $wpdb;

		$options=array();

		if (is_plugin_active("h5p/h5p.php")) {
			$h5ps=$wpdb->get_results("SELECT slug,title FROM {$wpdb->prefix}h5p_contents",ARRAY_A);
			foreach ($h5ps as $h5p) {
				$options["h5p:".$h5p["slug"]]="H5P: ".$h5p["title"];
			}
		}

		if (is_plugin_active("wp-deliverable/wp-deliverable.php")) {
			$deliverables=$wpdb->get_results("SELECT slug,title FROM {$wpdb->prefix}deliverable",ARRAY_A);
			foreach ($deliverables as $deliverable) {
				$options["deliverable:".$deliverable["slug"]]="Deliverable: ".$deliverable["title"];
			}
		}

		if (!$options)
			$options=array("_"=>"(No swagifacts available)");

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
	                "options"=>$options
	            ),
	        ),
		);

		$metaBoxes[]=array(
	        'title'      => "Swag",
	        'post_types' => 'swagpath',
	        'context'=>'side',
	        'fields'     => array(
	            array(
	            	"id"   => "prerequisites",
	            	"name" => "Prerequisites",
	            	"type" => "post",
	                "clone"=>true,
	                "post_type"=>"swagpath",
	                "desc"=>
	                	"The Swagpaths recommended to complete before attempting this swagpath."
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

	function showCurrentSwagpath() {
		if (get_post_type()!="swagpath")
			return;

		$swagpath=Swagpath::getCurrent();
		$swagUser=SwagUser::getCurrent();

		$template=new Template(__DIR__."/../../tpl/course.php");
		$template->set("swagUser",$swagUser);
		$template->set("swagpath",$swagpath);
		$template->set("showLessonPlan",FALSE);

		// Lesson plan
		$template->set("showLessonPlan",FALSE);
		$lessonPlanUrl=$swagpath->getLessonPlanUrl();

		if ($lessonPlanUrl && is_user_logged_in()) {
			$template->set("lessonPlan",$swagpath->getLessonPlanUrl());
			$template->set("showLessonPlan",TRUE);
		}

		if ($swagUser->isSwagpathCompleted($swagpath)) {
			$template->set("lessonplanAvailable",TRUE);
		}
		else if  (current_user_can('edit_others_pages') || get_the_author_id() == get_current_user_id()) {
			$template->set("lessonplanAvailable",TRUE);
		}
		else {
			$template->set("lessonplanAvailable",FALSE);
		}

		// Hint
		$template->set("showHintInfo",FALSE);
		if (!$swagUser->isSwagpathCompleted($swagpath)) {
			$uncollected=array();
			$pres=$swagpath->getPrerequisites();
			foreach ($pres as $pre)
				if (!$pre->isCompletedByCurrentUser())
					$uncollected[]=$pre;

			if ($uncollected) {
				$template->set("showHintInfo",TRUE);
				$uncollectedView=array();

				foreach ($uncollected as $u) {
					$uncollectedView[]=array(
						"title"=>$u->getPost()->post_title,
						"url"=>get_permalink($u->getPost())
					);
				}

				$template->set("uncollected",$uncollectedView);
			}
		}

		// Trail
		$url=home_url();
		$trail=array();
		$trail[]=array(
			"title"=>"Tracks",
			"url"=>$url
		);

		$terms=wp_get_post_terms($swagpath->getPost()->ID,"swagtrack");
		$termId=$terms[0]->term_id;
		$ancestors=get_ancestors($termId,"swagtrack","taxonomy");
		$ancestors=array_reverse($ancestors);
		$ancestors[]=$termId;

		foreach ($ancestors as $ancestorId) {
			$ancestor=get_term($ancestorId);
			$trail[]=array(
				"url"=>$url."?track=".$ancestor->slug,
				"title"=>$ancestor->name,
			);
		}

		$trail[]=array(
			"url"=>get_permalink($swagpath->getPost()->ID),
			"title"=>$swagpath->getPost()->post_title
		);

		$template->set("trail",$trail);
		$template->show();
	}
}

add_filter("rwmb_meta_boxes",array(SwagpathController::instance(),'rwmbMetaBoxes'));
