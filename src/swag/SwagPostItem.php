<?php

require_once __DIR__."/../utils/H5pUtil.php";

/**
 * An item in a swag post.
 */
class SwagPostItem {

	private $index;
	private $swagPost;
	private $type;
	private $parameters;

	/**
	 * Constructor.
	 */
	public function __construct($type, $parameters) {
		$this->type=$type;
		$this->parameters=$parameters;
	}

	/**
	 * Notification that this item is about to be shown.
	 */
	public function preShow() {
		switch ($this->type) {
			case 'h5p':
				$id=H5pUtil::getH5pIdByShortcodeArgs($this->parameters);
				$h5p=H5pUtil::getH5pById($id,array("parameters"));
				$h5pParameters=json_decode($h5p["parameters"],TRUE);
				if ($h5pParameters["timeline"]) {
					$this->saveCompletedStatement(SwagUser::getCurrent());
					$this->swagPost->saveProvidedSwagIfCompleted(SwagUser::getCurrent());
				}
				break;
			
			default:
				break;
		}
	}

	/**
	 * Save a completed statement to xapi.
	 * This is normally done by other components, but sometimes
	 * it can be useful to override this...
	 */
	public function saveCompletedStatement($swagUser) {
		$xapi=Swag::instance()->getXapi();
		if (!$xapi)
			return array();

		$user=$swagUser->getUser();
		if (!$user || !$user->ID)
			return;

		$pageUrl=get_permalink($this->swagPost->getPost()->ID);

		$statement=array(
			"actor"=>array(
				"mbox"=>"mailto:".$user->user_email,
				"name"=>$user->display_name
			),

			"object"=>array(
				"objectType"=>"Activity",
				"id"=>$this->getObjectUrl()
			),

			"verb"=>array(
				"id"=>"http://adlnet.gov/expapi/verbs/completed"
			),

			"context"=>array(
				"contextActivities"=>array(
					"grouping"=>array(
						array(
							"objectType"=>"Activity",
							"id"=>$pageUrl,
							"definition"=>array(
								"type"=>"http://activitystrea.ms/schema/1.0/page"
							)
						)
					)
				)
			),
		);

		$xapi->putStatement($statement);
	}

	/**
	 * Set index.
	 */
	public function setSwagPost($swagPost) {
		$this->swagPost=$swagPost;
	}

	/**
	 * Set index.
	 */
	public function setIndex($index) {
		$this->index=$index;
	}

	/**
	 * Is this the selected index? Determines this by checking
	 * the $_REQUEST["tab"] value.
	 */
	public function isSelected() {
		return $_REQUEST["tab"]==$this->index;
	}

	/**
	 * Get direct url.
	 */
	public function getUrl() {
		return $this->swagPost->getPost()->post_permalink."?tab=".$this->index;
	}

	/**
	 * Is this part completed?
	 */
	public function isCompleted($swagUser) {
		$objectUrl=$this->getObjectUrl();

		foreach ($this->swagPost->getRelatedStatements($swagUser) as $statement) {
			if ($statement["object"]["id"]==$objectUrl)
				return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get xAPI for checking completion.
	 */
	public function getObjectUrl() {
		if (!$this->isTypeAvailable())
			return NULL;

		if (!$this->objectUrl) {
			switch ($this->type) {
				case "h5p":
					$id=H5pUtil::getH5pIdByShortcodeArgs($this->parameters);
					$this->objectUrl=
						get_site_url().
						"/wp-admin/admin-ajax.php?action=h5p_embed&id=".$id;
					break;

				case "deliverable":
					$slug=$this->parameters["slug"];
					$this->objectUrl=
						get_site_url().
						"/wp-content/plugins/wp-deliverable/deliverable.php/".$slug;
					break;
			}
		}

		return $this->objectUrl;
	}

	/**
	 * Get the title.
	 */
	public function getTitle() {
		global $wpdb;

		if (!$this->isTypeAvailable())
			return "";

		switch ($this->type) {
			case "h5p":
				$id=H5pUtil::getH5pIdByShortcodeArgs($this->parameters);
				return H5pUtil::getH5pTitleById($id);
				break;

			case "deliverable":
				$slug=$this->parameters["slug"];
				$q=$wpdb->prepare("SELECT title FROM {$wpdb->prefix}deliverable WHERE slug=%s",$slug);
				$title=$wpdb->get_var($q);

				if ($wpdb->last_error)
					throw new Exception($wpdb->last_error);

				return $title;
				break;
		}
	}

	/**
	 * Is the type for this item available?
	 */
	public function isTypeAvailable() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		switch ($this->type) {
			case "h5p":
				return is_plugin_active("h5p/h5p.php");
				break;

			case "deliverable":
				return is_plugin_active("wp-deliverable/wp-deliverable.php");
				break;
		}

		return FALSE;
	}

	/**
	 * Get content.
	 */
	public function getContent() {
		if (!$this->isTypeAvailable())
			return "dependencies missing for ".$this->type;

		switch ($this->type) {
			case "h5p":
				$id=H5pUtil::getH5pIdByShortcodeArgs($this->parameters);
				return do_shortcode("[h5p id='$id']");
				break;

			case "deliverable":
				$slug=$this->parameters["slug"];
				return do_shortcode("[deliverable slug='$slug']");
				break;
		}
	}
}