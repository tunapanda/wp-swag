<?php

require_once __DIR__."/../model/Swagpath.php";

/**
 * Sync swagpaths.
 */
class SwagpathSyncer {

	/**
	 * Get local id by slug.
	 */
	private function getIdBySlug($slug) {
		global $wpdb;

		if (!$slug)
			return 0;

		$q=$wpdb->prepare(
			"SELECT ID ".
			"FROM   {$wpdb->prefix}posts ".
			"WHERE  post_name=%s ".
			"AND    post_type IN ('swagpath') ".
			"AND    post_status IN ('publish','draft')",
			$slug);
		$id=$wpdb->get_var($q);

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);

		return $id;
	}

	/**
	 * Get local id by slug.
	 */
	private function getSlugById($postId) {
		$post=get_post($postId);
		if (!$post)
			return NULL;

		if ($post->post_type!="swagpath")
			throw new Exception("Expected type to be post or page, not: ".$post->post_type);

		if ($post->post_status=="trash" || $post->post_status=="inherit")
			return NULL;

		return $post->post_name;
	}

	/**
	 * List resource slugs.
	 */
	public function listResourceSlugs() {
		$q=new WP_Query(array(
			"post_type"=>"swagpath",
			"post_status"=>"any",
			"posts_per_page"=>-1
		));
		$posts=$q->get_posts();
		$res=array();

		foreach ($posts as $post)
			if ($post->post_status=="publish" || $post->post_status=="draft")
				$res[]=$post->post_name;

		return $res;
	}

	/**
	 * Update resource.
	 */
	public function updateResource($slug, $info) {
		$data=$info->getData();

		if ($info->isCreate()) {
			$id=wp_insert_post(array(
				"post_name"=>$slug,
				"post_title"=>$data["post_title"],
				"post_type"=>"swagpath"
			));
		}

		else {
			$id=$this->getIdBySlug($slug);
		}

		$post=get_post($id);
		if (!$post)
			throw new Exception("No swagpath post, strange");

		if ($data["lessonplan"]) {
			$q=new WP_Query(array(
				"post_type"=>"attachment",
				"name"=>$data["lessonplan"]
			));
			$attachmentPosts=$q->get_posts();
			if (!$attachmentPosts)
				throw new Exception("Lessonplan not found, slug=".$data["lessonplan"]);

			$attachmentPost=$attachmentPosts[0];
			update_post_meta($id,"lessonplan",$attachmentPost->ID);
		}

		else {
			update_post_meta($id,"lessonplan","");
		}

		update_post_meta($id,"swagifact",$data["swagifact"]);
		update_post_meta($id,"providesArray",$data["provides"]);
		update_post_meta($id,"requiresArray",$data["requires"]);

		$post->post_excerpt=$data["excerpt"];
		$post->post_title=$data["title"];
		$post->post_status=$data["status"];

		wp_update_post($post);

		$swagpath=Swagpath::getById($id);
		$swagpath->updateMetas();
	}

	/**
	 * Get resource.
	 */
	public function getResource($slug) {
		$id=$this->getIdBySlug($slug);
		$post=get_post($id);

		if (!$post)
			return NULL;

		$lessonplanPost=get_post(get_post_meta($id,"lessonplan",TRUE));
		if (!$post)
			return NULL;

		return array(
			"title"=>$post->post_title,
			"status"=>$post->post_status,
			"swagifact"=>get_post_meta($id,"swagifact",TRUE),
			"provides"=>get_post_meta($id,"providesArray",TRUE),
			"requires"=>get_post_meta($id,"requiresArray",TRUE),
			"lessonplan"=>$lessonplanPost->post_name,
			"excerpt"=>$post->post_excerpt,
		);
	}

	/**
	 * Delete resource.
	 */
	public function deleteResource($slug) {
		$id=$this->getIdBySlug($slug);
		wp_trash_post($id);
	}
}

/**
 * Syncer.
 */
add_filter("remote-syncers",function($syncers) {
	$syncers[]=new SwagpathSyncer();
	return $syncers;
});