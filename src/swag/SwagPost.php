<?php

require_once __DIR__."/SwagPostItem.php";
require_once __DIR__."/../plugin/SwagPlugin.php";

/**
 * Per post related swag operations.
 */
class SwagPost {

	private $swagPostItems;
	private $relatedStatementsByEmail;

	/**
	 * Construct.
	 */
	public function __construct($post) {
		$this->post=$post;
		$this->swagPostItems=NULL;
		$this->relatedStatementsByEmail=array();
	}

	/**
	 * Get required swag.
	 */
	public function getRequiredSwag() {
		return get_post_meta($this->post->ID,"requires");
	}

	/**
	 * Get required swag.
	 */
	public function getProvidedSwag() {
		return get_post_meta($this->post->ID,"provides");
	}

	/**
	 * Get post.
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * Get the ids for the swagpaths that provides the swag.
	 */
	public function getPostsProvidingSwag($swags) {
		$posts=array();
		$postIds=array();

		foreach ($swags as $swag) {
			$q=new WP_Query(array(
				"post_type"=>"any",
				"meta_key"=>"provides",
				"meta_value"=>$swag
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
	 * Get swag post items.
	 */
	public function getSwagPostItems() {
		if (!is_array($this->swagPostItems)) {
			$this->swagPostItems=array();
			$shortcodes=ShortcodeUtil::extractShortcodes($this->getPost()->post_content);
			foreach ($shortcodes as $shortcode) {
				$item=NULL;

				switch ($shortcode["_"]) {
					case "h5p":
					case "h5p-course-item":
						$item=new SwagPostItem("h5p",$shortcode);
						break;

					case "deliverable":
					case "deliverable-course-item":
						$item=new SwagPostItem("deliverable",$shortcode);
						break;
				}
				

				if ($item) {
					$item->setSwagPost($this);
					$item->setIndex(sizeof($this->swagPostItems));
					$this->swagPostItems[]=$item;
				}
			}

			print_r($shortCodes);
		}

		return $this->swagPostItems;
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

	/**
	 * Get selected item based on $_REQUEST["tab"]
	 */
	public function getSelectedItem() {
		$tab=intval($_REQUEST["tab"]);
		$swagPostItems=$this->getSwagPostItems();
		return $swagPostItems[$tab];
	}

	/**
	 * Get the current swag post, created from the current
	 * Wordpress post.
	 */
	public static function getCurrent() {
		static $current;

		if (!$current)
			$current=new SwagPost(get_post());

		return $current;
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
			$statement=array(
				"actor"=>array(
					"mbox"=>"mailto:".$user->user_email,
					"name"=>$user->display_name
				),

				"object"=>array(
					"objectType"=>"Activity",
					"id"=>"http://swag.tunapanda.org/".$provided,
					"definition"=>array(
						"name"=>array(
							"en-US"=>$provided
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
}