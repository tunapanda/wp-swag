<?php

require_once __DIR__."/WpUtil.php";
require_once __DIR__."/src/model/Swag.php";
require_once __DIR__."/src/model/Swagpath.php";
require_once WpUtil::getWpLoadPath();

$data=array();
$data["nodes"]=array();
$data["links"]=array();
$swagNodeIndex=array();

foreach (Swag::findAllImplied() as $swag) {
	if ($swag->getProvidingSwagPosts()) {
		$swagData=array(
			"name"=>$swag->getString(),
			"type"=>"swag",
			"completed"=>$swag->isCompletedByCurrentUser(),
			"color"=>$swag->getDisplayColor()
		);

		$swagNodeIndex[$swag->getString()]=sizeof($data["nodes"]);
		$data["nodes"][]=$swagData;
	}
}

foreach (Swagpath::findAll() as $swagPost) {
	$swagPostNodeIndex=sizeof($data["nodes"]);
	$data["nodes"][]=array(
		"type"=>"swagpath",
		"name"=>$swagPost->getPost()->post_title,
		"url"=>get_permalink($swagPost->getPost()->ID)
	);

	foreach ($swagPost->getRequiredSwag() as $swag)
		$data["links"][]=array(
			"source"=>$swagNodeIndex[$swag->getString()],
			"target"=>$swagPostNodeIndex
		);

	foreach ($swagPost->getProvidedSwag() as $swag)
		$data["links"][]=array(
			"source"=>$swagPostNodeIndex,
			"target"=>$swagNodeIndex[$swag->getString()]
		);
}

echo json_encode($data);
