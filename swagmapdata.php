<?php

require_once __DIR__."/src/utils/WpUtil.php";
require_once __DIR__."/src/model/Swagpath.php";
require_once __DIR__."/src/controller/SwagMapController.php";

swag\WpUtil::bootstrap();

$mode="my";

if (isset($_REQUEST["mode"]))
	$mode=$_REQUEST["mode"];

echo json_encode(SwagMapController::instance()->swagMapData($mode));
