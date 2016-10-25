<?php

require_once __DIR__."/../../src/utils/WpUtil.php";
require_once swag\WpUtil::getWpLoadPath();

require_once __DIR__."/../../src/utils/ShortcodeUtil.php";
require_once __DIR__."/../../src/utils/H5pUtil.php";
require_once __DIR__."/../../src/model/SwagData.php";

$colors=array(
  '#7f910e','#347c01','#447500','#10a5a0','#0d8721',
  '#199909','#009682','#008c43','#a3aa0f','#027717',
  '#058c75','#2b9100','#057f05','#016d47','#11ad3d',
  '#0dad33','#1d960d','#006654','#05934a','#057241'
);

$colorIndex=0;

$q=new WP_Query(array(
	"post_type"=>"any",
	"post_status"=>"publish",
	"posts_per_page"=>-1
));

$posts=$q->get_posts();
echo "Total number of posts: ".sizeof($posts)."\n";

foreach ($posts as $sourcePost) {
	//echo "Processing: ".$sourcePost->post_name."\n";

	$shortcodes=ShortcodeUtil::extractShortcodes($sourcePost->post_content);
	if ($shortcodes && $shortcodes[0]["_"]=="course") {
		$swagifact=array();
		foreach ($shortcodes as $shortcode) {
			switch ($shortcode["_"]) {
				case "h5p":
				case "h5p-course-item":
				case "h5p-course":
					$slug=$shortcode["slug"];
					//echo "  H5p: ".$slug."\n";
					$swagifact[]="h5p:".$slug;
					break;

				case "deliverable":
					$slug=$shortcode["slug"];
					//echo "  Deliverable: ".$slug."\n";
					$swagifact[]="deliverable:".$slug;
					break;

				case "fruitful_btn":
				case "/fruitful_btn":
				case "course":
				case "/course":
					break;

				default:
					exit("Unknown item: ".$shortcode["_"]."\n");
			}
		}

		if ($swagifact) {
			$provided=$sourcePost->post_name;
			$parentIds=get_post_ancestors($sourcePost);

			foreach ($parentIds as $parentId) {
				$parentPost=get_post($parentId);
				if ($parentPost->post_name!="courses") {
					$provided=$parentPost->post_name.".".$provided;
				}
			}

			echo "'{$sourcePost->post_name}'=>'$provided',\n";
		}

		else {
			//echo "No valid swagifacts...\n";
		}
	} else {
		//echo "  Unable to parse...\n";
	}
}