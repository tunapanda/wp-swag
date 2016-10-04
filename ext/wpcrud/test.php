<?php

/*
Plugin Name: WpCrud Test
Plugin URI: http://github.com/limikael/wpcrud
GitHub Plugin URI: http://github.com/limikael/wpcrud
Description: Test for WpCrud. This is a library not a plugin! It exploses itself as a plugin for testing purposes only!
Version: 0.0.2
*/

require_once __DIR__."/WpCrud.php"; /* test ... !!! */

class WpCrudTest extends WpCrud {

	function init() {
		$this->addField("text")
			->description("this is a field");

		$this->addField("stamp")
			->type("timestamp")
			->description("this is a timestamp");

		$this->addField("sel")
			->type("select")
			->options(array(
				"a"=>"First Letter",
				"b"=>"Second Letter"
			));

		$box=$this->addBox("Images");

		$box->addField("im")
			->type("media-image")
			->description("You can select an image from the media library here");

		$box->addField("im2")
			->type("media-image");

//		$this->setListFields(array("text","sel","im2"));
//		$this->setParentMenuSlug("h5p");
	}

	function getLiteral($literal) {
		switch ($literal) {
			case "description":
				return "hello world bla";
				break;
		}
	}

	function getItem($id) {
		global $wpdb;

		$row=$wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}crudtest ".
			"WHERE  id=%s",
			$id
		));

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);

		return $row;
	}

	function saveItem($item) {
		global $wpdb;

		if ($item->id) {
			$wpdb->query($wpdb->prepare(
				"UPDATE {$wpdb->prefix}crudtest ".
				"SET    text=%s, ".
				"       stamp=%s, ".
				"       sel=%s, ".
				"       im=%s, ".
				"       im2=%s ".
				"WHERE  id=%s",
				$item->text,
				$item->stamp,
				$item->sel,
				$item->im,
				$item->im2,
				$item->id
			));
		}

		else {
			$wpdb->query($wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}crudtest ".
				"SET         text=%s, ".
				"            stamp=%s, ".
				"            sel=%s, ".
				"            im=%s, ".
				"            im2=%s ",
				$item->text,
				$item->stamp,
				$item->sel,
				$item->im,
				$item->im2
			));
		}

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);
	}

	function deleteItem($item) {
		global $wpdb;

		$wpdb->query($wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}crudtest ".
			"WHERE  id=%s",
			$item->id
		));

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);
	}

	function getAllItems() {
		global $wpdb;

		$results=$wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}crudtest ",
			NULL
		));

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);

		return $results;
	}
}

function wpcrudtest_activate() {
	global $wpdb;

	$wpdb->query($wpdb->prepare(
		"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}crudtest ( ".
		"    id INTEGER NOT NULL auto_increment, ".
		"    text VARCHAR(255) NOT NULL, ".
		"    stamp INTEGER NOT NULL, ".
		"    sel VARCHAR(255) NOT NULL, ".
		"    im VARCHAR(255) NOT NULL, ".
		"    im2 VARCHAR(255) NOT NULL, ".
		"    PRIMARY KEY (id) ".
		")",
		NULL
	));

	if ($wpdb->last_error)
		throw new Exception($wpdb->last_error);
}

function wpcrudtest_deactivate() {
	global $wpdb;

	$wpdb->query($wpdb->prepare(
		"DROP TABLE IF EXISTS {$wpdb->prefix}crudtest",
		NULL
	));

	if ($wpdb->last_error)
		throw new Exception($wpdb->last_error);
}

register_activation_hook(__FILE__, "wpcrudtest_activate");
register_deactivation_hook(__FILE__, "wpcrudtest_deactivate");

WpCrudTest::setup();
