<?php

use  Swag\App\Models\Swagpath;

final class SwagpathModelTest extends WP_UnitTestCase
{
  public function test_create_swagpath() {
    $post_id = $this->factory->post->create(array( 'post_title' => 'Test', 'post_type' => 'swagpath'));

    $post = get_post($post_id);

    $swagpath = new Swagpath($post);

    $this->assertEquals($swagpath->id, $post_id);
  }
}