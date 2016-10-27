<?php

require_once __DIR__."/../utils/Singleton.php";

use swag\Singleton;

/**
 * Manage the swag taxonomy.
 */
class SwagTrackController extends Singleton {

	/**
	 * Init.
	 */
	public function init() {
		register_taxonomy("swagtrack","swagpath",array(
			"label"=>"Swagtracks",
			"hierarchical"=>TRUE
		));
	}
}