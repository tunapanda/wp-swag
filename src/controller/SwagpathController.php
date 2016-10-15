<?php

require_once __DIR__."/../utils/Singleton.php";
require_once __DIR__."/../model/Swagpath.php";

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
		));

		add_action("get_template_part_content",array($this,'getTemplatePart'));
		add_action("save_post",array($this,'savePost'));
	}

	/**
	 * After the post is saved. Update meta values.
	 */
	public function savePost($postId) {
		$post=get_post($postId);
		if ($post->post_type=="swagpath") {
			$swagpath=Swagpath::getById($postId);
			$swagpath->updateMetas();
		}
	}

	/**
	 * Do meta boxes, meta-box style.
	 */
	public function rwmbMetaBoxes($metaBoxes) {
		global $wpdb;

		$options=array();

		$h5ps=$wpdb->get_results("SELECT slug,title FROM {$wpdb->prefix}h5p_contents",ARRAY_A);
		foreach ($h5ps as $h5p) {
			$options["h5p:".$h5p["slug"]]="H5P: ".$h5p["title"];
		}

		if (is_plugin_active("wp-deliverable/wp-deliverable.php")) {
			$deliverables=$wpdb->get_results("SELECT slug,title FROM {$wpdb->prefix}deliverable",ARRAY_A);
			foreach ($deliverables as $deliverable) {
				$options["deliverable:".$deliverable["slug"]]="Deliverable: ".$deliverable["title"];
			}
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
	                'id'   => 'providesArray',
	                'name' => 'Provides',
	                'type' => 'text',
	                "size"=>20,
	                "clone"=>true,
	                "desc"=>
	                	"The swag badges the user will receive upon completing the swagpath. ".
	                	"Swags are written on the same form as a newsgroup name, e.g. tech.programming.php"
	            ),
	            array(
	                'id'   => 'requiresArray',
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

	function getTemplatePart() {
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

		if ($swagUser->isSwagCompleted($swagpath->getProvidedSwag())) {
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
		if (!$swagUser->isSwagCompleted($swagpath->getRequiredSwag())) {
			$template->set("showHintInfo",TRUE);

			$uncollected=$swagUser->getUncollectedSwag($swagpath->getRequiredSwag());
			$uncollectedFormatted=array();

			foreach ($uncollected as $swag)
				$uncollectedFormatted[]="<b>{$swag->getString()}</b>";

			$swagpaths=Swagpath::getSwagpathsProvidingSwag($uncollected);
			$swagpathsFormatted=array();

			foreach ($swagpaths as $swagpath)
				$swagpathsFormatted[]=
					"<a href='".get_post_permalink($swagpath->getPost()->ID)."'>".
					$swagpath->getPost()->post_title.
					"</a>";

			$template->set("uncollectedSwag",join(", ",$uncollectedFormatted));
			$template->set("uncollectedSwagpaths",join(", ",$swagpathsFormatted));
		}

		// Trail
		$swag=$swagpath->getProvidedSwag()[0];
		if (!$swag) {
			$trail=array(
				array(
					"url"=>home_url(),
					"title"=>"Tracks"
				),

				array(
					"url"=>get_permalink(),
					"title"=>get_the_title()
				)
			);
		}

		else {
			$trail=array();
			foreach ($swagpath->getProvidedSwag()[0]->getTrail() as $swag) {
				$item=array();
				$item["url"]=home_url()."?track=".$swag->getString();
				$item["title"]=$swag->getTitle();

				$trail[]=$item;
			}

			array_pop($trail);
			$trail[]=array(
				"url"=>get_permalink(),
				"title"=>get_the_title()
			);
			$trail[0]["title"]="Tracks";
		}

		$template->set("trail",$trail);

		$template->show();
	}
}

add_filter("rwmb_meta_boxes",array(SwagpathController::instance(),'rwmbMetaBoxes'));
