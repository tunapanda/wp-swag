<?php

/**
 * Per user swag.
 */
class SwagUser {

	/**
	 * Construct.
	 */
	public function __construct($user) {
		$this->user=$user;
		$this->xapi=SwagPlugin::instance()->getXapi();

		$this->completedSwagFetched=NULL;
		$this->completedSwag=NULL;
	}

	/**
	 * Get user.
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Get completed for top level track.
	 */
	public function getCompletedByTopLevelTrack($trackSlug) {
		$swagpaths=$this->getCompletedSwagpaths();
		$res=array();

		foreach ($swagpaths as $swagpath) {
			if ($swagpath->getTopLevelTrack()==$trackSlug)
				$res[]=$swagpath;
		}

		return $res;
	}

	/**
	 * Get collected swag.
	 */
	public function getCompletedSwagpaths() {
		if (!$this->user || !$this->user->user_email)
			return array();

		if ($this->completedSwagFetched)
			return $this->completedSwag;

		if ($this->xapi) {
			$statements=$this->xapi->getStatements(array(
				"agentEmail"=>$this->user->user_email,
				"activity"=>"http://swag.tunapanda.org/",
				"verb"=>"http://adlnet.gov/expapi/verbs/completed",
				"related_activities"=>"true"
			));
		}

		else {
			$statements=array();
		}

		$this->completedSwag=array();
		foreach ($statements as $statement) {
			$slug=str_replace("http://swag.tunapanda.org/","",$statement["object"]["id"]);
			$swagpath=Swagpath::getBySlug($slug);

			if ($swagpath)
				$this->completedSwag[]=$swagpath;
		}

		$this->completedSwagFetched=TRUE;

		return $this->completedSwag;
	}

	/**
	 * Is this swag completed by the user?
	 */
	public function isSwagpathCompleted($swagpath) {
		$completed=$this->getCompletedSwagpaths();
		foreach ($completed as $c)
			if ($c->getXapiObjectId()==$swagpath->getXapiObjectId())
				return TRUE;

		return FALSE;
	}

	/**
	 * Logged in?
	 */
	public function isLoggedIn() {
		if (!$this->user)
			return FALSE;

		if (!$this->user->ID)
			return FALSE;

		return TRUE;
	}

	/**
	 * Get email.
	 */
	public function getEmail() {
		$email=$this->user->user_email;

		if (!$email)
			throw new Exception("User doesn't have an email");

		return $email;
	}

	/**
	 * Get current SwagUser.
	 */
	public static function getCurrent() {
		static $current;

		if (!$current)
			$current=new SwagUser(wp_get_current_user());

		return $current;
	}

	/**
	 * Get by email.
	 */
	public static function getByEmail($email) {
		$email=str_replace("mailto:","",$email);

		$user=get_user_by("email",$email);
		if (!$user || !$user->ID)
			throw new Exception("user not found: ".$email);

		return new SwagUser($user);
	}
}