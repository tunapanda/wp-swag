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
	echo "Processing: ".$sourcePost->post_name."\n";

	$shortcodes=ShortcodeUtil::extractShortcodes($sourcePost->post_content);
	if ($shortcodes && $shortcodes[0]["_"]=="course") {
		$q=$wpdb->prepare(
			"SELECT * ".
			"FROM   {$wpdb->prefix}posts ".
			"WHERE  post_name=%s ".
			"AND    post_type IN ('swagpath') ".
			"AND    post_status='publish'",
			$sourcePost->post_name);
		$swagpathPosts=$wpdb->get_results($q);

		if ($swagpathPosts) {
			echo "  Exists already...\n";
		}

		else {
			$swagifact=array();
			foreach ($shortcodes as $shortcode) {
				switch ($shortcode["_"]) {
					case "h5p":
					case "h5p-course-item":
					case "h5p-course":
						$slug=$shortcode["slug"];
						$h5pId=H5pUtil::getH5pIdBy("slug",$slug);
						if ($h5pId) {
							echo "  H5p: ".$slug."\n";
							$swagifact[]="h5p:".$slug;
						}
						break;

					case "deliverable":
						$slug=$shortcode["slug"];
						echo "  Deliverable: ".$slug."\n";
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
				$postId=wp_insert_post(array(
					"post_name"=>$sourcePost->post_name,
					"post_status"=>"publish",
					"post_excerpt"=>$sourcePost->post_excerpt,
					"post_type"=>"swagpath",
					"post_title"=>$sourcePost->post_title
				));

				$provided=$sourcePost->post_name;
				$parentIds=get_post_ancestors($sourcePost);
				if ($parentIds) {
					$parentId=$parentIds[0];
					$parentPost=get_post($parentId);
					$provided=$parentPost->post_name.".".$provided;

					$swagData=SwagData::findOneBy("string",$parentPost->post_name);
					if (!$swagData) {
						$swagData=new SwagData();
						$swagData->string=$parentPost->post_name;
//						$swagData->description=$parentPost->post_content;
						$swagData->description=$parentPost->post_excerpt;
						$swagData->color=$colors[$colorIndex++];
					}

					$swagData->save();
				}

				update_post_meta($postId,"swagifact",$swagifact);
				update_post_meta($postId,"providesArray",array($provided));
				$swagpath=Swagpath::getById($postId);
				$swagpath->updateMetas();
			}

			else
				echo "No valid swagifacts...\n";
		}
	} else {
		echo "  Unable to parse...\n";
	}
}