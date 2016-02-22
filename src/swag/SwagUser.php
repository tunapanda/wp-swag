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
		$this->xapi=new Xapi(
			get_option("ti_xapi_endpoint_url"),
			get_option("ti_xapi_username"),
			get_option("ti_xapi_password")
		);

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
	 * Get collected swag.
	 */
	public function getCompletedSwag() {
		if (!$this->user || !$this->user->user_email)
			return array();

		if ($this->completedSwagFetched)
			return $this->completedSwag;

		$statements=$this->xapi->getStatements(array(
			"agentEmail"=>$this->user->user_email,
			"activity"=>"http://swag.tunapanda.org/",
			"verb"=>"http://adlnet.gov/expapi/verbs/completed",
			"related_activities"=>"true"
		));

		$this->completedSwag=array();

		foreach ($statements as $statement) {
			$objectId=$statement["object"]["id"];
			$swag=str_replace("http://swag.tunapanda.org/","",$objectId);

			if (!in_array($swag,$this->completedSwag))
				$this->completedSwag[]=$swag;
		}

		$this->completedSwagFetched=TRUE;

		return $this->completedSwag;
	}

	/**
	 * Is this swag completed by the user?
	 */
	public function isSwagCompleted($swags) {
		if (!is_array($swags))
			$swags=array($swags);

		$completedSwag=$this->getCompletedSwag();

		foreach ($swags as $swag)
			if (!in_array($swag,$completedSwag))
				return FALSE;

		return TRUE;
	}

	/**
	 * Get which swag has not yet been collected,
	 * out of a set of swag.
	 */
	public function getUncollectedSwag($swags) {
		$completedSwag=$this->getCompletedSwag();

		$uncollected=array();

		foreach ($swags as $swag)
			if (!in_array($swag,$completedSwag))
				$uncollected[]=$swag;

		return $uncollected;
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