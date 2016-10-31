<?php

require_once __DIR__."/../utils/Singleton.php";
require_once __DIR__."/../model/SwagTrack.php";

use swag\Singleton;

/**
 * Controller for showing swag listing and swag map.
 */
class SwagPageController extends Singleton {

	/**
	 * Init.
	 */
	public function init() {
		if ($this->initCalled)
			return;

		$this->initCalled=true;

		register_post_type("swag",array(
			"labels"=>array(
				"name"=>"Swag",
			),
			"public"=>true,
			"has_archive"=>true,
			"show_in_nav_menus"=>true,
			"show_ui"=>false,
			"has_archive"=>false
		));

		add_action('pre_get_posts', array($this,'enableFrontPage'));
		add_filter('get_pages',array($this,'addSwagToDropDown'));

		add_shortcode("swagtoc", array($this,"swagtocShortcode"));
		add_shortcode("swagmap", array($this,"swagmapShortcode"));
		add_shortcode("my-swag",array($this,"mySwagShortcode"));

		add_shortcode("swag-view-test",array($this,"swagViewTestShortcode"));
	}

	/**
	 * View test.
	 */
	public function swagViewTestShortcode($args) {
		$template=new Template(__DIR__."/../../tpl/myswag.php");
		$template->set("pluginUrl",plugins_url()."/wp-swag/");

		$tracks=array(
			array(
				"name"=>"",
				"badges"=>array(
					array(
						"name"=>"What is Swag?",
					),
				)
			),
			array(
				"name"=>"Technology",
				"score"=>"2 / 10",
				"badges"=>array(
					array(
						"name"=>"Programming",
					),
					array(
						"name"=>"Something else with a long name"
					)
				)
			),
			array(
				"name"=>"Design",
				"score"=>"3 / 15",
				"badges"=>array(
					array(
						"name"=>"GIMP",
					),
					array(
						"name"=>"Inkspace"
					),
					array(
						"name"=>"Photography"
					)
				)
			)
		);

		$template->set("tracks",$tracks);

		return $template->render();
	}

	/**
	 * My Swag.
	 */
	public function mySwagShortcode($args) {
		$swagUser=SwagUser::getCurrent();

		$topLevelTracks=get_terms(array(
			'taxonomy'=>'swagtrack',
			'parent'=>0
		));

		$dummyTrack=new stdClass;
		$dummyTrack->name=NULL;
		$dummyTrack->slug=NULL;
		$dummyTrack->term_id=NULL;
		array_unshift($topLevelTracks,$dummyTrack);

		$tracks=array();
		foreach ($topLevelTracks as $topLevelTrack) {
			$swagTrack=SwagTrack::getById($topLevelTrack->term_id);
			if ($swagTrack)
				$color=$swagTrack->getDisplayColor();

			else
				$color=SwagTrack::DEFAULT_COLOR;

			$trackData=array(
				"name"=>$topLevelTrack->name,
				"color"=>$color,
				"badges"=>array(),
				"score"=>"",
			);

			$swagpaths=$swagUser->getCompletedByTopLevelTrack($topLevelTrack->slug);
			foreach ($swagpaths as $swagpath) {
				$trackData["badges"][]=array(
					"name"=>$swagpath->getPost()->post_title
				);
			}

			$allForTrack=Swagpath::findAllForTopLevelTrack($topLevelTrack->slug);

			$trackData["score"]=sizeof($swagpaths)." / ".sizeof($allForTrack);
			$tracks[]=$trackData;
		}

		$template=new Template(__DIR__."/../../tpl/myswag.php");
		$template->set("pluginUrl",plugins_url()."/wp-swag/");
		$template->set("tracks",$tracks);

		return $template->render();
	}

	public function swagmapShortcode($args) {
		$mode="my";
		if (isset($_REQUEST["mode"]))
			$mode=$_REQUEST["mode"];

		$template=new Template(__DIR__."/../../tpl/swagmap.php");
		$template->set("mode",$mode);
		$template->set("plugins_uri",plugins_url()."/wp-swag");
		$template->set("mylink",home_url()."/swag/map/?mode=my");
		$template->set("fulllink",home_url()."/swag/map/?mode=full");
		$template->show();
	}

	public function addSwagToDropDown($pages) {
	    $args = array(
	        'post_type' => 'swag'
	    );
	    $items = get_posts($args);
	    $pages = array_merge($pages, $items);

		return $pages;
	}

	public function enableFrontPage($query) {
	    if('' == $query->query_vars['post_type'] && 0 != $query->query_vars['page_id'])
    	    $query->query_vars['post_type'] = array( 'page', 'swag' );
	}

	/**
	 * Does this post exist.
	 */
	private static function getPostId($type, $slug) {
		global $wpdb;

		$q=$wpdb->prepare(
			"SELECT ID ".
			"FROM   {$wpdb->prefix}posts ".
			"WHERE  post_type=%s ".
			"AND    post_name=%s ",
			$type,$slug);
		$id=$wpdb->get_var($q);

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);

		return $id;
	}

	/**
	 * Install.
	 */
	public function install() {
		$this->init();

		$postId=SwagPageController::getPostId("swag","toc");
		if (!$postId) {
			$postId=wp_insert_post(array(
				"post_type"=>"swag",
				"post_name"=>"toc",
				"post_title"=>"Swag Table of Contents",
				"post_status"=>"publish",
				"post_content"=>"[swagtoc]",
			));

			if (!$postId)
				throw new Exception("Unable to create post");
		}

		$postId=SwagPageController::getPostId("swag","map");
		if (!$postId) {
			$postId=wp_insert_post(array(
				"post_type"=>"swag",
				"post_name"=>"map",
				"post_title"=>"Swagmap",
				"post_status"=>"publish",
				"post_content"=>"[swagmap]",
			));

			if (!$postId)
				throw new Exception("Unable to create post");
		}
	}

	/**
	 * Used when sorting swagpaths, so unprepared comes last.
	 */
	private static function cmpSwagpathViewData($a, $b) {
		if ($a["prepared"] && !$b["prepared"])
			return -1;

		if (!$a["prepared"] && $b["prepared"])
			return 1;

		return 0;
	}

	/**
	 * Render the table of contents.
	 */
	public function swagtocShortcode($args) {
		$url=get_permalink();

		if (isset($_REQUEST["track"])) {
			$parentTrack=SwagTrack::getBySlug($_REQUEST["track"]);
			$parentTrackId=$parentTrack->getId();
		}

		else {
			$parentTrack=NULL;
			$parentTrackId=NULL;
		}

		$tracks=SwagTrack::getByParentId($parentTrackId);
		$trackViews=array();
		foreach ($tracks as $track) {
			$trackViews[]=array(
				"title"=>$track->getTerm()->name,
				"description"=>$track->getTerm()->description,
				"url"=>$url."?track=".$track->getTerm()->slug,
				"color"=>$track->getDisplayColor()
			);
		}

		$unprepared=0;
		$swagpaths=Swagpath::getByTrackId($parentTrackId);
		$swagpathViews=array();
		foreach ($swagpaths as $swagpath) {
			$swagpathViews[]=array(
				"title"=>$swagpath->getPost()->post_title,
				"description"=>$swagpath->getPost()->post_excerpt,
				"url"=>get_permalink($swagpath->getId()),
				"prepared"=>$swagpath->isCurrentUserPrepared(),
				"complete"=>$swagpath->isCompletedByCurrentUser(),
				"color"=>$swagpath->getDisplayColor(),
			);

			if (!$swagpath->isCurrentUserPrepared())
				$unprepared++;
		}

		usort($swagpathViews,"SwagPageController::cmpSwagpathViewData");

		$trail=array();
		$ancestors=get_ancestors($parentTrackId,"swagtrack","taxonomy");
		if ($parentTrackId)
			array_unshift($ancestors,$parentTrackId);

		$ancestors=array_reverse($ancestors);

		$trail[]=array(
			"title"=>"Tracks",
			"url"=>$url
		);

		foreach ($ancestors as $ancestorId) {
			$ancestor=get_term($ancestorId);
			$item=array();
			$item["url"]=$url."?track=".$ancestor->slug;
			$item["title"]=$ancestor->name;

			$trail[]=$item;
		}

		if (sizeof($trail)<2)
			$trail=array();

		$template=new Template(__DIR__."/../../tpl/toc.php");
		$template->set("pluginurl",plugins_url()."/wp-swag");
		$template->set("tracks",$trackViews);
		$template->set("swagpaths",$swagpathViews);
		$template->set("unprepared",$unprepared);
		$template->set("trail",$trail);

		return $template->render();
	}
}