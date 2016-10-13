<?php

require_once __DIR__."/../utils/ArrayUtil.php";
require_once __DIR__."/../plugin/SwagPlugin.php";
require_once __DIR__."/../model/SwagPostItem.php";

use swag\ArrayUtil;

/**
 * Represents a Swagpath.
 * Has an underlying post of the "swagpath" post type.
 */
class Swagpath {

	private $post;
	private $swagPostItems;
	private $relatedStatementsByEmail;


	/**
	 * Construct.
	 */
	private function __construct($post) {
		$this->post=$post;
		$this->relatedStatementsByEmail=array();
		$this->swagPostItems=NULL;
	}

	/**
	 * Get underlying post.
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * Get lesson plan url, if any.
	 */
	public function getLessonPlanUrl() {
		$lessonPlanPostId=get_post_meta($this->post->ID,"lessonplan",TRUE);
		if (!$lessonPlanPostId)
			return NULL;

		return wp_get_attachment_url($lessonPlanPostId);
	}

	/**
	 * Get items.
	 */
	public function getSwagPostItems() {
		if (!is_array($this->swagPostItems)) {
			$this->swagPostItems=array();

			$swagifactSlugs=get_post_meta($this->post->ID,"swagifact",TRUE);
			foreach ($swagifactSlugs as $swagifactSlug) {
				$parts=explode(":",$swagifactSlug);
				$type=$parts[0];
				$slug=$parts[1];

				$item=NULL;
				switch ($type) {
					case "h5p":
					case "h5p-course-item":
						$item=new SwagPostItem("h5p",array(
							"slug"=>$slug
						));
						break;

					case "deliverable":
					case "deliverable-course-item":
						$item=new SwagPostItem("deliverable",array(
							"slug"=>$slug
						));
						break;
				}
				
				if ($item) {
					$item->setSwagPost($this);
					$item->setIndex(sizeof($this->swagPostItems));
					$this->swagPostItems[]=$item;
				}
			}

			//print_r($shortCodes);
		}

		return $this->swagPostItems;
	}

	/**
	 * Get selected item based on $_REQUEST["tab"]
	 */
	public function getSelectedItem() {
		$tab=intval($_REQUEST["tab"]);
		$swagPostItems=$this->getSwagPostItems();
		return $swagPostItems[$tab];
	}

	/**
	 * Get swag provided by this Swagpath.
	 * Returns an array of Swag objects.
	 */
	public function getProvidedSwag() {
		$provided=array();
		$metas=ArrayUtil::flattenArray(get_post_meta($this->post->ID,"provides"));

		foreach ($metas as $meta)
			$provided[]=Swag::findByString($meta);

		return $provided;
	}

	/**
	 * Get swag required by this Swagpath.
	 * Returns an array of Swag objects.
	 */
	public function getRequiredSwag() {
		$provided=array();
		$metas=ArrayUtil::flattenArray(get_post_meta($this->post->ID,"requires"));

		foreach ($metas as $meta)
			$provided[]=Swag::findByString($meta);

		return $provided;
	}

	/**
	 * Get the current swag path, created from the current
	 * Wordpress post.
	 */
	public static function getCurrent() {
		static $current;

		if (!$current) {
			$post=get_post();
			if ($post->post_type!="swagpath")
				throw new Exception("Current post is not a swagpath.");

			$current=new Swagpath($post);
		}

		return $current;
	}

	/**
	 * Get related statements for current user.
	 */
	public function getRelatedStatements($swagUser) {
		if (!$swagUser->isLoggedIn())
			return array();

		$email=$swagUser->getEmail();

		if (array_key_exists($email,$this->relatedStatementsByEmail))
			return $this->relatedStatementsByEmail[$email];

		$xapi=SwagPlugin::instance()->getXapi();
		if (!$xapi)
			return array();

		$this->relatedStatementsByEmail[$email]=$xapi->getStatements(array(
			"agentEmail"=>$swagUser->getEmail(),
			"activity"=>get_permalink($this->getPost()->ID),
			"verb"=>"http://adlnet.gov/expapi/verbs/completed",
			"related_activities"=>"true"
		));

		return $this->relatedStatementsByEmail[$email];
	}

	/**
	 * Get swagpaths providing this swag.
	 */
	public static function getSwagpathsProvidingSwag($swag) {
		$wpPosts=Swagpath::getPostsProvidingSwag($swag);
		$swagPosts=array();

		foreach ($wpPosts as $wpPost)
			$swagPosts[]=new Swagpath($wpPost);

		return $swagPosts;
	}

	/**
	 * Flatten provides and requires.
	 */
	public function updateMetas() {
		$providesArray=get_post_meta($this->post->ID,"providesArray",TRUE);
		delete_post_meta($this->post->ID,"provides");
		foreach ($providesArray as $provide)
			add_post_meta($this->post->ID,"provides",$provide);

		$requiresArray=get_post_meta($this->post->ID,"requiresArray",TRUE);
		delete_post_meta($this->post->ID,"requires");
		foreach ($requiresArray as $require)
			add_post_meta($this->post->ID,"requires",$require);
	}

	/**
	 * Does the current user have all prerequisites?
	 */
	public function isCurrentUserPrepared() {
		$swagUser=SwagUser::getCurrent();
		return $swagUser->isSwagCompleted($this->getRequiredSwag());
	}

	/**
	 * Get posts providing this swag.
	 */
	private static function getPostsProvidingSwag($swags) {
		if (!is_array($swags))
			$swags=array($swags);

		$posts=array();
		$postIds=array();

		foreach ($swags as $swag) {
			$q=new WP_Query(array(
				"post_type"=>"any",
				"meta_key"=>"provides",
				"meta_value"=>$swag->getString()
			));

			foreach ($q->get_posts() as $post) {
				if (!in_array($post->ID,$postIds)) {
					$posts[]=$post;
					$postIds[]=$post->ID;
				}
			}
		}

		return $posts;
	}

	/**
	 * Get Swagpath by id.
	 */
	public static function getById($postId) {
		$post=get_post($postId);

		if (!$post)
			throw new Exception("Post not found");

		if ($post->post_type!="swagpath")
			throw new Exception("This is not a swagpath post.");

		return new Swagpath($post);
	}

	/**
	 * Find all swagpaths.
	 */
	public static function findAll() {
		$all=array();

		$q=new WP_Query(array(
			"post_type"=>"swagpath",
			"meta_key"=>"provides"
		));

		foreach ($q->get_posts() as $post)
			$all[]=new Swagpath($post);

		return $all;
	}

	/**
	 * Save xapi statements for provided swag for current user.
	 */
	public function saveProvidedSwag($swagUser) {
		$xapi=SwagPlugin::instance()->getXapi();
		if (!$xapi)
			return array();

		$user=$swagUser->getUser();
		if (!$user || !$user->ID)
			return;

		foreach ($this->getProvidedSwag() as $provided) {
			$providedString=$provided->getString();

			$statement=array(
				"actor"=>array(
					"mbox"=>"mailto:".$user->user_email,
					"name"=>$user->display_name
				),

				"object"=>array(
					"objectType"=>"Activity",
					"id"=>"http://swag.tunapanda.org/".$providedString,
					"definition"=>array(
						"name"=>array(
							"en-US"=>$providedString
						)
					)
				),

				"verb"=>array(
					"id"=>"http://adlnet.gov/expapi/verbs/completed"
				),

				"context"=>array(
					"contextActivities"=>array(
						"category"=>array(
							array(
								"objectType"=>"Activity",
								"id"=>"http://swag.tunapanda.org/"
							)
						)
					)
				),
			);

			$xapi->putStatement($statement);
		}		
	}

	/**
	 * Are all the swag post items completed?
	 */
	public function isAllSwagPostItemsCompleted($swagUser) {
		foreach ($this->getSwagPostItems() as $swagPostItem) {
			if (!$swagPostItem->isCompleted($swagUser))
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Save provided swag if the user has completed all
	 * swagifacts for the swagpath.
	 */
	public function saveProvidedSwagIfCompleted($swagUser) {
		if ($this->isAllSwagPostItemsCompleted($swagUser))
			$this->saveProvidedSwag($swagUser);
	}
}