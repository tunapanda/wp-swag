<?php
/*
Plugin Name: Swag
Plugin URI: https://github.com/tunapanda/wp-swag
GitHub Plugin URI: https://github.com/tunapanda/wp-swag
Description: The gamified, self-paced, xAPI enabled learning environment from Tunapanda!
Version: 0.0.19
*/

require_once __DIR__."/wp-swag-admin.php";
require_once __DIR__."/src/controller/SwagPageController.php";
require_once __DIR__."/src/syncers/SwagpathSyncer.php";
require_once __DIR__."/src/controller/SwagTgmpaController.php";

define("RWMB_URL",plugins_url()."/wp-swag/ext/meta-box/");
require_once __DIR__."/ext/meta-box/meta-box.php";
require_once __DIR__."/ext/wordpress-settings-api-class/plugin.php";

add_action("init", array("WP_Swag_admin", "init_hooks"));

function swag_activate() {
	if (!function_exists("curl_init"))
		trigger_error("wp-swag requires the cURL module",E_USER_ERROR);

	$basename=basename(__DIR__);
	if ($basename!="wp-swag")
		trigger_error(
			"wp-swag needs to be installed in a plugin directory called wp-swag. ".
			"If you installed it via a .zip file, please rename the zip file to wp-swag.zip",
			E_USER_ERROR
		);

	SwagPageController::instance()->install();
}

register_activation_hook(__FILE__,"swag_activate");

SwagTgmpaController::instance()->init();

function swag_add_action_links($links) {
	$swaglinks=array(
		'<a href="'.admin_url('options-general.php?page=ti_settings').'">Settings</a>',
	);

	return array_merge($links,$swaglinks);
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__),'swag_add_action_links');

if (!session_id())
	session_start();
