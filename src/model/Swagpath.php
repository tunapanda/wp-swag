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

	private static $swagpathById=array();
	private static $swagpathBySlug=array();
	private static $all=array();

	/**
	 * Construct.
	 */
	private function __construct($post) {
		$this->post=$post;
		$this->relatedStatementsByEmail=array();
		$this->swagPostItems=NULL;

		Swagpath::$swagpathById[$post->ID]=$this;
		Swagpath::$swagpathBySlug[$post->post_name]=$this;
	}

	/**
	 * Get id.
	 */
	public function getId() {
		return $this->getPost()->ID;
	}

	/**
	 * Get display color.
	 */
	public function getDisplayColor() {
		$track=$this->getTrack();

		if (!$track)
			return SwagTrack::DEFAULT_COLOR;

		return $track->getDisplayColor();
	}

	/**
	 * Get track.
	 */
	public function getTrack() {
		$trackIds=wp_get_object_terms($this->post->ID,"swagtrack");
		if (!$trackIds)
			return NULL;

		return SwagTrack::getById($trackIds[0]);
	}

	/**
	 * Get slug for top level track.
	 */
	public function getTopLevelTrack() {
		$tracks=wp_get_object_terms($this->post->ID,"swagtrack");
		if (!$tracks)
			return NULL;

		$track=$tracks[0];
		while ($track->parent)
			$track=get_term($track->parent);

		return $track->slug;
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
	 * Get prerequisite swagpaths.
	 */
	public function getPrerequisites() {
		$prerequisites=array();

		$ids=ArrayUtil::flattenArray(get_post_meta($this->post->ID,"prerequisites"));
		foreach ($ids as $id)
			$prerequisites[]=Swagpath::getById($id);

		return $prerequisites;
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
	 * Get related statements for the given user.
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
	 * Get object id.
	 */
	public function getXapiObjectId() {
		return "http://swag.tunapanda.org/".$this->post->post_name;
	}

	/**
	 * Is the current user prepared for the prerequisites?
	 */
	public function isCurrentUserPreparedForPrerequisites() {
		if ($this->isCompletedByCurrentUser())
			return TRUE;

		if ($this->isCurrentUserPrepared())
			return TRUE;

		foreach ($this->getPrerequisites() as $p)
			if (!$p->isCurrentUserPrepared())
				return FALSE;

		return TRUE;
	}

	/**
	 * Does the current user have all prerequisites?
	 */
	public function isCurrentUserPrepared() {
		if ($this->isCompletedByCurrentUser())
			return TRUE;

		foreach ($this->getPrerequisites() as $p)
			if (!$p->isCompletedByCurrentUser())
				return FALSE;

		return TRUE;
	}

	/**
	 * Is this swagpath completed by the user?
	 */
	public function isCompletedByUser($swagUser) {
		return $swagUser->isSwagpathCompleted($this);
	} 

	/**
	 * Is this swagpath completed by the current user?
	 */
	public function isCompletedByCurrentUser() {
		return SwagUser::getCurrent()->isSwagpathCompleted($this);
	}

	/**
	 * Get by track id.
	 */
	public static function getByTrackId($parentTrackId) {
		if ($parentTrackId) {
			$q=new WP_Query(array(
				"post_type"=>"swagpath",
				"tax_query"=>array(
					array(
						"taxonomy"=>"swagtrack",
						"include_children"=>false,
						"field"=>"term_id",
						"terms"=>$parentTrackId
					)
				),
				"posts_per_page"=>-1
			));
		}

		else {
			$notTerms=get_terms(array(
				'taxonomy'=>'swagtrack',
				'hide_empty'=>false,
				"fields"=>'ids'
			));

			$q=new WP_Query(array(
				"post_type"=>"swagpath",
				"tax_query"=>array(
					array(
						"taxonomy"=>"swagtrack",
						"include_children"=>false,
						"field"=>"term_id",
						"terms"=>$notTerms,
						"operator"=>"NOT IN"
					)
				),
				"posts_per_page"=>-1
			));
		}

		$posts=$q->get_posts();
		$res=array();
		foreach ($posts as $post)
			$res[]=new Swagpath($post);

		return $res;
	}

	/**
	 * Get Swagpath by id.
	 */
	public static function getById($postId) {
		if (isset(Swagpath::$swagpathById[$postId]))
			return Swagpath::$swagpathById[$postId];

		$post=get_post($postId);

		if (!$post)
			throw new Exception("Post not found");

		if ($post->post_type!="swagpath")
			throw new Exception("This is not a swagpath post.");

		return new Swagpath($post);
	}

	/**
	 * Get Swagpath by slug.
	 */
	public static function getBySlug($slug) {
		if (isset(Swagpath::$swagpathBySlug[$slug]))
			return Swagpath::$swagpathBySlug[$slug];

		$posts=get_posts(array(
			'name'=>$slug,
			'post_type'=>'swagpath',
			'post_status'=>'publish',
			'numberposts'=>1
		));

		if (!$posts)
			return NULL;

		return new Swagpath($posts[0]);
	}

	/**
	 * Find all for a certain top level track.
	 */
	public static function findAllForTopLevelTrack($slug) {
		$all=Swagpath::findAll();

		$res=array();
		foreach ($all as $swagpath)
			if ($swagpath->getTopLevelTrack()==$slug)
				$res[]=$swagpath;

		return $res;
	}

	/**
	 * Find all swagpaths.
	 */
	public static function findAll() {
		if (Swagpath::$all)
			return Swagpath::$all;

		$q=new WP_Query(array(
			"post_type"=>"swagpath",
			"post_status"=>"publish",
			"posts_per_page"=>-1
		));

		Swagpath::$all=array();
		foreach ($q->get_posts() as $post)
			Swagpath::$all[]=new Swagpath($post);

		return Swagpath::$all;
	}

	/**
	 * Save xapi statements for provided swag for current user.
	 * If there is already a matching statement, a new one will
	 * not be saved.
	 */
	public function saveProvidedSwag($swagUser) {
		$xapi=SwagPlugin::instance()->getXapi();
		if (!$xapi)
			return array();

		$user=$swagUser->getUser();
		if (!$user || !$user->ID)
			return;

		$current=$xapi->getStatements(array(
			"agentEmail"=>$swagUser->getEmail(),
			"activity"=>$this->getXapiObjectId(),
			"verb"=>"http://adlnet.gov/expapi/verbs/completed",
		));

		if ($current)
			return;

		$statement=array(
			"actor"=>array(
				"mbox"=>"mailto:".$user->user_email,
				"name"=>$user->display_name
			),

			"object"=>array(
				"objectType"=>"Activity",
				"id"=>$this->getXapiObjectId(),
				"definition"=>array(
					"name"=>array(
						"en-US"=>$this->post->post_title
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