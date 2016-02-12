<?php
/*
Plugin Name: Swag
Plugin Description: It privides the following functionality; Allowing creation of tracklisting, swagpaths and swagmaps as well as allowing xAPI communication.
Plugin URL: https://github.com/tunapanda/wp-swag
Version: 0.0.1 
*/

function swag_admin_menu(){
	add_menu_page(
		"Swag", 
		"Swag",
		"manage_options",
		"swag",
		"swag_settings_page"
	);
}

function swag_settings_page(){
	require_once(__DIR__."/settings.php");
}
add_action("admin_menu", "swag_admin_menu");