<?php

	require_once __DIR__."/../../src/utils/Xapi.php";

	$xapi=new Xapi(
		"http://localhost:8080/learninglocker/public/data/xAPI/",
		"d088140399292de104e50f2863003c68f036f6b6",
		"0a7ce0ef5d59d4573117d63ff77d6cd50a55d91c"
	);

	$statement=array(
		//"id"=>"44e9516a-95b3-4128-879d-0250e5caf1fe",
		"actor"=>array(
			"mbox"=>"mailto:li.mikael@gmail2.com",
		),
		"verb"=>array(
			"id"=>"http://did",
		),
		"object"=>array(
			"id"=>"http://something"
		)
	);

	$id=$xapi->putStatement($statement);
	echo "id: ".$id."\n";

	/*$statement=$xapi->getStatements(array("statementId"=>$id));
	print_r($statement);*/