<?php

/**
 * H5p utilities.
 */
class H5pUtil {

	/**
	 * Get H5p title.
	 */
	public static function getH5pTitleById($id) {
		global $wpdb;

		$q=$wpdb->prepare(
			"SELECT title ".
			"FROM   {$wpdb->prefix}h5p_contents ".
			"WHERE  id=%s",
			$id
		);

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);

		return $wpdb->get_var($q);
	}

	/**
	 * Get h5p id.
	 */
	public static function getH5pIdBy($by, $value) {
		global $wpdb;

		$q=$wpdb->prepare(
			"SELECT id ".
			"FROM   {$wpdb->prefix}h5p_contents ".
			"WHERE  $by=%s",
			$value
		);

		if ($wpdb->last_error)
			throw new Exception($wpdb->last_error);

		return $wpdb->get_var($q);
	}

	/**
	 * Find h5p id.
	 */
	public static function getH5pIdByShortcodeArgs($args) {
		if (array_key_exists("id", $args))
			return H5pUtil::getH5pIdBy("id",$args["id"]);

		else if (array_key_exists("title", $args))
			return H5pUtil::getH5pIdBy("title",$args["title"]);

		else if (array_key_exists("slug", $args))
			return H5pUtil::getH5pIdBy("slug",$args["slug"]);
	}	
}