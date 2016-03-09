<?php

require_once __DIR__."/../../src/model/Swag.php";

class SwagTest extends WP_UnitTestCase {

	/**
	 * Does it get filled by post data?
	 */
	function testFillByPostData() {
		Swag::clearCache();

		$id=wp_insert_post(array(
			"post_title"=>"hello world"
		));

		update_post_meta($id,"provides","/hello/world");

		$this->assertEquals(3,sizeof(Swag::findAll()));
		$this->assertEquals(1,sizeof(Swag::findByPath("hello")->getChildren()));
	}
}