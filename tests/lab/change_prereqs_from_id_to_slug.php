<?php

$slugs=array(
	152=>"scrum",
	153=>"networking-101",
	154=>"how-computers-work",
	155=>"version-control-system",
	156=>"terminal",
	157=>"using-functions",
	158=>"scratch",
	159=>"restful-apis",
	160=>"web-development",
	161=>"photography",
	162=>"introduction-to-graphic-design",
	163=>"filmography",
	164=>"typographyinforgraphics",
	165=>"drawing",
	167=>"creating-swagpaths-3",
	168=>"blender",
	169=>"pitching",
	170=>"feedback",
	171=>"body-language",
	172=>"introduction-to-storytelling",
	173=>"certell-9",
	174=>"certell-10",
	296=>"synfig",
	297=>"introduction-to-3d-printing",
	298=>"how-are-you",
	299=>"inkscape",
	300=>"what-are-you-doing",
	301=>"p5js-course",
	302=>"introduction-to-p5js",
	310=>"customer-relations",
	319=>"addie",
	320=>"computer-animation-using-synfig",
	321=>"kdenlive-video-editing-software",
	323=>"what-is-swag",
);

foreach ($slugs as $id=>$slug) {
	$old="a:1:{i:0;s:3:\"$id\";}";
	$new="a:1:{i:0;s:".strlen($slug).":\"$slug\";}";
	echo "UPDATE wp_postmeta SET meta_value='$new' WHERE meta_key='prerequisites' AND meta_value='$old';\n";
}