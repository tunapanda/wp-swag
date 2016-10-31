<?php

require_once __DIR__."/WpUtil.php";
require_once __DIR__."/src/model/Swagpath.php";
require_once __DIR__."/src/controller/SwagMapController.php";
require_once WpUtil::getWpLoadPath();

$mode="my";

if (isset($_REQUEST["mode"]))
	$mode=$_REQUEST["mode"];

echo json_encode(SwagMapController::instance()->swagMapData($mode));
