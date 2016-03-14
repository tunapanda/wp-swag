<?php

/**
 * Represents one swag, collectible as a badge. Organized in a hierarchy.
 */
class Swag {

	private static $allSwagByString;
	private static $cacheInitialized=FALSE;

	private $string;
	private $parent;
	private $children;

	/**
	 * Constructor.
	 */
	private function __construct($string=NULL) {
		$this->string=$string;
		$this->children=array();
	}

	/**
	 * Get swagpaths providing this swag.
	 */
	public function getProvidingSwagPosts() {
		if (!$this->string)
			return array();

		return SwagPost::getSwagPostsProvidingSwag($this);
	}

	/**
	 * Get title
	 */
	public function getTitle() {
		$parts=explode("/",$this->string);
		return ucfirst($parts[sizeof($parts)-1]);
	}

	/**
	 * Get string representation of this swag.
	 */
	public function getString() {
		return $this->string;
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

		return array_values(Swag::$allSwagByString);
	}

	/**
	 * Find a swag by path.
	 */
	public static function findByString($path="") {
		Swag::initializeCache();

		$path=str_replace("http://swag.tunapanda.org/","",$path);
		$path=join("/",Swag::splitPath($path));

		if (isset(Swag::$allSwagByString[$path]))
			return Swag::$allSwagByString[$path];

		//echo "not found: ".$path;

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
			"metakey"=>"provides",
			"nopaging"=>TRUE
		));

		foreach ($q->get_posts() as $post) {
			$provides=get_post_meta($post->ID,"provides");

			foreach ($provides as $provide)
				Swag::getOrCreateByString($provide);
		}

		$q=new WP_Query(array(
			"post_type"=>"any",
			"post_status"=>"any",
			"metakey"=>"requires",
			"nopaging"=>TRUE
		));

		foreach ($q->get_posts() as $post) {
			$requires=get_post_meta($post->ID,"requires");

			foreach ($requires as $require)
				Swag::getOrCreateByString($require);
		}
	}

	/**
	 * Has the current user completed this swag?
	 */
	public function isCompletedByCurrentUser() {
		return SwagUser::getCurrent()->isSwagCompleted($this);
	}

	/**
	 * If it doesn't exist it will be implicitly created.
	 */
	private static function getOrCreateByString($string="") {
		Swag::initializeCache();

		$string=join("/",Swag::splitPath($string));

		if (!Swag::$allSwagByString)
			Swag::$allSwagByString=array();

		if (!isset(Swag::$allSwagByString[$string])) {
			$parts=Swag::splitPath($string);
			$swag=new Swag($string);

			if ($string) {
				$parent=Swag::getOrCreateByString(join("/",array_slice($parts,0,sizeof($parts)-1)));
				$swag->parent=$parent;
				$parent->addChild($swag);
			}

			Swag::$allSwagByString[$string]=$swag;
		}

		return Swag::$allSwagByString[$string];
	}
}