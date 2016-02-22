<?php

require __DIR__."/../utils/Xapi.php";

/**
 * Common base functions.
 */
class Swag {

	private static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {
	}

	/**
	 * Get xapi endpoint, if configured.
	 */
	public function getXapi() {
		$endpoint=get_option("ti_xapi_endpoint_url");
		$username=get_option("ti_xapi_username");
		$password=get_option("ti_xapi_password");

		if ($endpoint)
			return new Xapi($endpoint,$username,$password);

		else
			return NULL;
	}

	/**
	 * Get sinleton instance.
	 */
	public static function instance() {
		if (!Swag::$instance)
			Swag::$instance=new Swag();

		return Swag::$instance;
	}
}