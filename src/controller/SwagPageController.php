<?php

require_once __DIR__."/../model/Swag.php";

/**
 * Controller for showing swag listing and swag map.
 */
class SwagPageController {

	/**
	 * Used when sorting swagpaths, so unprepared comes last.
	 */
	private static function cmpSwagpathViewData($a, $b) {
		if ($a["prepared"] && !$b["prepared"])
			return -1;

		if (!$a["prepared"] && $b["prepared"])
			return 1;

		return 0;
	}

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
		$unprepared=0;

		foreach ($parent->getChildren() as $child) {
			if ($child->getChildren()) {
				$color=$child->getDisplayColor();

				if (!$color)
					$color="#009900";

				$tracks[]=array(
					"title"=>$child->getTitle(),
					"description"=>$child->getDescription(),
					"url"=>$url."?track=".$child->getString(),
					"color"=>$color
				);
			}
			$providing=$child->getProvidingSwagPosts();
			foreach ($providing as $provider) {
				if (!$provider->isCurrentUserPrepared())
					$unprepared++;

				$post=$provider->getPost();
				$swagpaths[]=array(
					"title"=>$post->post_title,
					"description"=>$post->post_excerpt,
					"url"=>get_permalink($post->ID),
					"prepared"=>$provider->isCurrentUserPrepared(),
					"swag"=>$provider->getProvidedSwag(),
					"color"=>$child->getDisplayColor()
				);
			}
		}

		usort($swagpaths,"SwagPageController::cmpSwagpathViewData");

		$trail=array();
		foreach ($parent->getTrail() as $swag) {
			$item=array();
			$item["url"]=$url."?track=".$swag->getString();
			$item["title"]=$swag->getTitle();

			$trail[]=$item;
		}

		$trail[0]["title"]="Tracks";
		if (sizeof($trail)<2)
			$trail=array();

		$template=new Template(__DIR__."/../../tpl/toc.php");
		$template->set("tracks",$tracks);
		$template->set("swagpaths",$swagpaths);
		$template->set("unprepared",$unprepared);
		$template->set("trail",$trail);

		return $template->render();
	}
}