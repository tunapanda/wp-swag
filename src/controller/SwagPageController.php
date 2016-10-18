<?php

require_once __DIR__."/../model/Swag.php";
require_once __DIR__."/../utils/Singleton.php";

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
	}

	public function swagmapShortcode($args) {
		$plugins_uri = plugins_url()."/wp-swag";
		return "<div id='swagmapcontainer'>
		<div id='swag_description_container'>A swagmap is gamified display of performance. The green hollow nodes indicate the swagpath is not completed or attempted while non-hollow green nodes indicate the swagpaths is completed and questions answered.
		</div>
		<script>
		var PLUGIN_URI = '$plugins_uri';
		</script>
		</div>";		
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
		$track="";

		if (isset($_REQUEST["track"]))
			$track=$_REQUEST["track"];

		$parent=Swag::findByString($track);
		$tracks=array();
		$swagpaths=array();
		$url=get_permalink();
		$unprepared=0;

		foreach ($parent->getChildren() as $child) {
			if ($child->getChildren()) {
				$color=$child->getDisplayColor();

				if (!$color)
					$color="#009900";

				$tracks[]=array(
					"title"=>$child->getTitle(),
					"description"=>$child->getDescription(),
					"url"=>$url."?track=".$child->getString(),
					"color"=>$color
				);
			}
			$providing=$child->getProvidingSwagPosts();
			foreach ($providing as $provider) {
				if (!$provider->isCurrentUserPrepared())
					$unprepared++;

				$post=$provider->getPost();
				$swagpaths[]=array(
					"title"=>$post->post_title,
					"description"=>$post->post_excerpt,
					"url"=>get_permalink($post->ID),
					"prepared"=>$provider->isCurrentUserPrepared(),
					"swag"=>$provider->getProvidedSwag(),
					"color"=>$child->getDisplayColor()
				);
			}
		}

		usort($swagpaths,"SwagPageController::cmpSwagpathViewData");

		$trail=array();
		foreach ($parent->getTrail() as $swag) {
			$item=array();
			$item["url"]=$url."?track=".$swag->getString();
			$item["title"]=$swag->getTitle();

			$trail[]=$item;
		}

		$trail[0]["title"]="Tracks";
		if (sizeof($trail)<2)
			$trail=array();

		$template=new Template(__DIR__."/../../tpl/toc.php");
		$template->set("tracks",$tracks);
		$template->set("swagpaths",$swagpaths);
		$template->set("unprepared",$unprepared);
		$template->set("trail",$trail);

		return $template->render();
	}
}