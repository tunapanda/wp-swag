<?php

require_once __DIR__."/../../src/utils/WpUtil.php";
require_once swag\WpUtil::getWpLoadPath();
require_once __DIR__."/../../src/model/Swagpath.php";

$providesMap=array(
'form-one'=>'form-one',
'phil'=>'testing.phil',
'the-agile-software-development-system'=>'technology.the-agile-software-development-system',
'scrum'=>'technology.scrum',
'networking-101'=>'technology.networking.networking-101',
'mysql'=>'technology.mysql',
'how-computers-work'=>'technology.how-computers-work',
'version-control-system'=>'technology.command-line-interface.version-control-system',
'terminal'=>'technology.command-line-interface.terminal',
'using-functions'=>'technology.coding.using-functions',
'scratch'=>'technology.coding.scratch',
'restful-apis'=>'technology.coding.restful-apis',
'introduction-to-p5js'=>'technology.coding.introduction-to-p5js',
'web-development'=>'design.web-development',
'videography'=>'design.videography',
'photography'=>'design.photography',
'introduction-to-graphic-design'=>'design.introduction-to-graphic-design',
'filmography'=>'design.filmography',
'typographyinforgraphics'=>'design.design-3.typographyinforgraphics',
'inkscape'=>'design.design-3.inkscape',
'drawing'=>'design.design-3.drawing',
'animation-course-using-synfig'=>'design.design-3.animation-course-using-synfig',
'creating-swagpaths-3'=>'design.creating-swagpaths-3',
'blender'=>'design.blender',
'addie'=>'design.addie',
'pitching'=>'communication-business-organization.self-expression.pitching',
'feedback'=>'communication-business-organization.self-expression.feedback',
'body-language'=>'communication-business-organization.self-expression.body-language',
'introduction-to-storytelling'=>'communication-business-organization.introduction-to-storytelling',
'cse-part-1'=>'communication-business-organization.common-sense-economics.cse-part-1',
'certell3'=>'communication-business-organization.common-sense-economics.certell3',
'certell-9'=>'communication-business-organization.common-sense-economics.certell-9',
'certell-10'=>'communication-business-organization.common-sense-economics.certell-10',
'common-sense-economics'=>'communication-business-organization.common-sense-economics',
);

foreach ($providesMap as $mapId=>$mapProvides) {
	$q=new WP_Query(array(
		"name"=>$mapId,
		"post_type"=>"swagpath",
		"post_status"=>"publish"
	));

	$posts=$q->get_posts();

	foreach ($posts as $post) {
		echo $mapId." -> ".$mapProvides."\n";

		update_post_meta($post->ID,"providesArray",array($mapProvides));

		$swagpath=Swagpath::getById($post->ID);
		$swagpath->updateMetas();
	}
}

