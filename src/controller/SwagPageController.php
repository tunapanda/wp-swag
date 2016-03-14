<?php

require_once __DIR__."/../model/Swag.php";

/**
 * Controller for showing swag listing and swag map.
 */
class SwagPageController {

	/**
	 * Render the table of contents.
	 */
	public function toc($args) {
		$track="";

		if (isset($_REQUEST["track"]))
			$track=$_REQUEST["track"];

		$parent=Swag::findByString($track);
		$tracks=array();
		$swagpaths=array();
		$url=get_permalink();

		foreach ($parent->getChildren() as $child) {
			if ($child->getChildren()) {
				$tracks[]=array(
					"title"=>$child->getTitle(),
					"url"=>$url."?track=".$child->getString()
				);
			}

			$providing=$child->getProvidingSwagPosts();
			foreach ($providing as $provider) {
				$post=$provider->getPost();
				$swagpaths[]=array(
					"title"=>$post->post_title,
					"description"=>$post->post_excerpt,
					"url"=>get_page_link($post->ID),
					"swag"=>$provider->getProvidedSwag()
				);
			}
		}

		$template=new Template(__DIR__."/../../tpl/toc.php");
		$template->set("tracks",$tracks);
		$template->set("swagpaths",$swagpaths);

		return $template->render();
	}
}