<?php

/**
 * Represents one swag, collectible as a badge. Organized in a hierarchy.
 */
class Swag {

	private static $allSwagByPath;
	private static $cacheInitialized=FALSE;

	private $path;
	private $parent;
	private $children;

	/**
	 * Constructor.
	 */
	private function __construct($path=NULL) {
		$this->path=$path;
		$this->children=array();
	}

	/**
	 * Get path.
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get parent swag.
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Get child swags.
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Get swag posts providing this swag.
	 */
	public function getProvidingSwagPosts() {
	}

	/**
	 * Add a child.
	 */
	private function addChild($swag) {
		$this->children[]=$swag;
	}

	/**
	 * Split path.
	 */
	private static function splitPath($s) {
		$parts=explode("/",$s);
		$res=array();

		foreach ($parts as $part)
			if (trim($part))
				$res[]=$part;

		return $res;
	}

	/**
	 * Clear cache.
	 */
	public static function clearCache() {
		Swag::$cacheInitialized=FALSE;
	}

	/**
	 * Find all swag
	 */
	public static function findAll() {
		Swag::initializeCache();

		return array_values(Swag::$allSwagByPath);
	}

	/**
	 * Find a swag by path.
	 */
	public static function findByPath($path="") {
		Swag::initializeCache();

		$path=join("/",Swag::splitPath($path));

		if (isset(Swag::$allSwagByPath[$path]))
			return Swag::$allSwagByPath[$path];

		return NULL;
	}

	/**
	 * Fill internal cache with data from posts.
	 */
	private static function initializeCache() {
		if (Swag::$cacheInitialized)
			return;

		Swag::$cacheInitialized=TRUE;

		$q=new WP_Query(array(
			"post_type"=>"any",
			"post_status"=>"any",
			"metakey"=>"provides"
		));

		foreach ($q->get_posts() as $post) {
			$provides=get_post_meta($post->ID,"provides");

			foreach ($provides as $provide)
				Swag::getOrCreateByPath($provide);
		}
	}

	/**
	 * If it doesn't exist it will be implicitly created.
	 */
	private static function getOrCreateByPath($path="") {
		Swag::initializeCache();

		$path=join("/",Swag::splitPath($path));

		if (!Swag::$allSwagByPath)
			Swag::$allSwagByPath=array();

		if (!isset(Swag::$allSwagByPath[$path])) {
			$parts=Swag::splitPath($path);
			$swag=new Swag($path);

			if ($path) {
				$parent=Swag::getOrCreateByPath(join("/",array_slice($parts,0,sizeof($parts)-1)));
				$swag->parent=$parent;
				$parent->addChild($swag);
			}

			Swag::$allSwagByPath[$path]=$swag;
		}

		return Swag::$allSwagByPath[$path];
	}
}