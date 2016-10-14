<?php

require_once __DIR__."/../model/Swag.php";
require_once __DIR__."/../model/SwagData.php";

/**
 * Swag syncer.
 */
class SwagSyncer {

	/**
	 * List resource slugs.
	 */
	public function listResourceSlugs() {
		$swagDatas=SwagData::findAll();

		$res=array();
		foreach ($swagDatas as $swagData)
			$res[]=$swagData->string;

		return $res;
	}

	/**
	 * Get resource.
	 */
	public function getResource($slug) {
		$swagData=SwagData::findOneBy("string",$slug);
		if (!$swagData)
			return NULL;

		return array(
			"color"=>$swagData->color,
			"description"=>$swagData->description
		);
	}

	/**
	 * Update resource.
	 */
	public function updateResource($slug, $info) {
		$swagData=SwagData::findOneBy("string",$slug);
		if (!$swagData)
			$swagData=new SwagData();

		$data=$info->getData();

		$swagData->string=$slug;
		$swagData->color=$data["color"];
		$swagData->description=$data["description"];

		$swagData->save();
	}

	/**
	 * Delete resource.
	 */
	public function deleteResource($slug) {
		$swagData=SwagData::findOneBy("string",$slug);
		if ($swagData)
			$swagData->delete();
	}
}

/**
 * Syncer.
 */
add_filter("remote-syncers",function($syncers) {
	$syncers[]=new SwagSyncer();
	return $syncers;
});
