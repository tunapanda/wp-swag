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

///$plugin=new WP_Swag_admin();

add_action("init", array("WP_Swag_admin", "init_hooks"));

function swag_activate() {
	SwagData::install();
}

register_activation_hook(__FILE__,"swag_activate");

SwagController::setup();
