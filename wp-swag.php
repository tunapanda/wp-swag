<?php
/*
Plugin Name: Swag
Plugin URI: https://github.com/tunapanda/wp-swag
GitHub Plugin URI: https://github.com/tunapanda/wp-swag
Description: It privides the following functionality; Allowing creation of tracklisting, swagpaths and swagmaps as well as allowing xAPI communication.
Version: 0.0.4
*/

require_once __DIR__."/wp-swag-admin.php";
require_once __DIR__."/src/controller/SwagController.php";
require_once __DIR__."/src/model/SwagData.php";
require_once __DIR__."/src/syncers/SwagpathSyncer.php";

///$plugin=new WP_Swag_admin();
define("RWMB_URL",plugins_url()."/wp-swag/ext/meta-box/");
require_once __DIR__."/ext/meta-box/meta-box.php";

add_action("init", array("WP_Swag_admin", "init_hooks"));

function swag_activate() {
	SwagData::install();
}

register_activation_hook(__FILE__,"swag_activate");

SwagController::setup();
