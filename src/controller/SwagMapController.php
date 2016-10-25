<?php

require_once __DIR__."/../utils/Singleton.php";

use swag\Singleton;

/**
 * Manage the swag taxonomy.
 */
class SwagMapController extends Singleton {

	/**
	 * Init.
	 */
	public function init() {
	}

	/**
	 * Get swagmap data.
	 */
	public function swagMapData() {
		$nodes=array();
		$links=array();

		$q=new WP_Query(array(
			"post_type"=>"swagpath",
			"post_status"=>"publish",
			"posts_per_page"=>-1
		));

		$posts=$q->get_posts();
		$nodeIndexByPostId=array();

		foreach ($posts as $post) {
			$nodeIndexByPostId[$post->ID]=sizeof($nodes);
			$nodes[]=array(
				"name"=>$post->post_title,
				"type"=>"swag",
				"completed"=>false,
				"color"=>"#009900",
				"url"=>get_permalink($post->ID)
			);
		}

		foreach ($posts as $post) {
			$pres=get_post_meta($post->ID,"prerequisites",TRUE);
			if ($pres) {
				foreach ($pres as $pre) {
					if ($nodeIndexByPostId[$pre]) {
						$link=array(
							"source"=>$nodeIndexByPostId[$pre],
							"target"=>$nodeIndexByPostId[$post->ID]
						);

						$links[]=$link;
					}
				}
			}
		}

		return array(
			"nodes"=>$nodes,
			"links"=>$links
		);
	}
}