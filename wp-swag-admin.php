<?php
/*
	wp-swag plugin admin functionalities and hooks
*/

require_once __DIR__."/utils.php";
require_once __DIR__."/src/model/SwagUser.php";
require_once __DIR__."/src/utils/Xapi.php";
require_once __DIR__."/src/model/Swagpath.php";
require_once __DIR__."/src/utils/Template.php";
require_once __DIR__."/src/utils/ShortcodeUtil.php";
require_once __DIR__."/src/controller/SettingsPageController.php";
require_once __DIR__."/src/controller/SwagPageController.php";
require_once __DIR__."/src/controller/SwagpathController.php";
require_once __DIR__."/src/controller/SwagTrackController.php";
require_once __DIR__."/src/controller/BadgeController.php";
require_once __DIR__."/src/utils/WpUtil.php";
require_once __DIR__ . "/src/utils/H5pUtil.php";

class WP_Swag_admin{
	static $plugins_uri;
	static $adminMessage;

	function __construct() {
		add_action("init", array($this, "init_hooks"));
	}

	public function init_hooks(){
		self::$plugins_uri = plugins_url()."/wp-swag";

		// initialise the the admin settings
		add_action('admin_init',array($this,'ti_admin_init'));
		add_action('admin_menu',array($this,'ti_admin_menu'));

		add_action('wp_enqueue_scripts',array($this, "ti_enqueue_scripts"));
		add_action('admin_enqueue_scripts',array($this, "ti_enqueue_scripts"));

		add_action('wp_ajax_swagmap_data', array($this, 'swagmapdata'));
		add_action('wp_ajax_nopriv_swagmap_data', array($this, 'swagmapdata'));

		add_filter("h5p-xapi-post-save",array($this,"ti_xapi_post_save"));
		add_filter("h5p-xapi-pre-save",array($this,"ti_xapi_pre_save"));
		add_action("deliverable-xapi-post-save",array($this, "ti_xapi_post_save"));

		add_filter("h5p-xapi-auth-settings",
			array($this,"ti_xapi_h5p_auth_settings"));

		add_filter("deliverable-xapi-auth-settings",
			array($this, "ti_deliverable_xapi_auth_settings"));

		SwagpathController::instance()->init();
		SwagPageController::instance()->init();
		SettingsPageController::instance()->init();
		SwagTrackController::instance()->init();

		if (is_admin()) {
			$basepath=swag\WpUtil::getWpBasePath();
			$data=get_plugin_data($basepath."/wp-content/plugins/wp-h5p-xapi/wp-h5p-xapi.php",FALSE,FALSE);
			if ($data) {
				$version=$data["Version"];
				$versionParts=explode(".",$version);

				if (intval($versionParts[1])==1 && intval($versionParts[2])<4) {
					self::$adminMessage="Your version of wp-h5p-xapi is old, you need at least version 0.1.4";
					add_action("admin_notices",array($this,"adminNotices"));
				}
			}
		}
	}

	/**
	 * Admin notice.
	 */
	public static function adminNotices() {
		echo "<div class='notice notice-error'><p>".self::$adminMessage."</p></div>";
	}

	/**
	 * Create the admin menu.
	 */
	public function ti_admin_menu() {
		add_options_page(
			'Tunapanda Swag',
			'Tunapanda Swag',
			'manage_options',
			'ti_settings',
			array($this, 'ti_create_settings_page')
		);
	}

	/**
	 * Admin init.
	 */
	public function ti_admin_init() {
		register_setting("ti","ti_xapi_endpoint_url");
		register_setting("ti","ti_xapi_username");
		register_setting("ti","ti_xapi_password");
	}

	/**
	 * Create settings page.
	 */
	public function ti_create_settings_page() {
		SettingsPageController::instance()->process();
	}

	/**
	 * Scripts and styles in the plugin
	 */
	public function ti_enqueue_scripts() {
		wp_register_style("wp_swag",plugins_url( "/style.css", __FILE__)); //?v=x added to refresh browser cache when stylesheet is updated.
		wp_enqueue_style("wp_swag");

		wp_register_script("d3",plugins_url("/d3.v3.min.js", __FILE__));
		wp_register_script("ti-main",plugins_url("/main.js", __FILE__), array('jquery'));

		wp_localize_script('ti-main', 'swag_settings', array('swagmap_url' => admin_url('admin-ajax.php?action=swagmap_data')));

		wp_enqueue_script("ti-main");

		wp_enqueue_script("d3");
	}

	public function swagmapdata() {
		require_once __DIR__ . '/swagmapdata.php';
		wp_die();
	}

	/**
	 * Here we have the chance to modify the statement before it is saved.
	 */
	public function ti_xapi_pre_save($statement) {
		$currentUser=SwagUser::getCurrent();

		if ($currentUser->isLoggedIn() &&
				$statement["actor"]["mbox"]!="mailto:".$currentUser->getEmail())
			throw new Exception("logged in, but with a different user");

		if (!$currentUser->isLoggedIn() &&
				isset($statement["actor"]["mbox"]))
			throw new Exception("not logged in, but got email in the statement");

		if (!isset($statement["actor"]["mbox"]))
			$statement["actor"]["mbox"]="mailto:".$currentUser->getEmail();

		unset($statement["actor"]["account"]);

		if (class_exists('Groups_User')) {
			$groups_user = new Groups_User( get_current_user_id() );
			$groups = $groups_user->groups;

			if (in_array(2, $groups_user->group_ids_deep)) {
				foreach($groups as $group) {
					if($group->parent_id === "2") {
						$member = array($statement["actor"]);
						$statement['context']['team'] = array(
							"name" => $group->name,
							"objectType" => "Group",
							"member" => $member
						);
					}
				}
			}

			// 
		}

		return $statement;
	}

	/**
	 * Act on completed xapi statements.
	 * Save xapi statement for swag if applicable.
	 */
	public function ti_xapi_post_save($statement) {
		/*if ($statement["verb"]["id"]!="http://adlnet.gov/expapi/verbs/completed")
			return;*/

		$postId=NULL;

		foreach ($statement["context"]["contextActivities"]["grouping"] as $groupingActivity) {
			// preg_match("/swagtrack\/[0-9a-z\-]+\/([0-9a-z\-]+)\/([0-9a-z\-]+)\/?$/", $groupingActivity["id"], $ids);
			$id=url_to_postid($groupingActivity["id"]);
			// $id = $ids[2];
			if ($id)
				$postId=$id;
		}

		// if (isset($statement["context"]["contextActivities"]["category"])) {
		// 	foreach ($statement["context"]["contextActivities"]["category"] as $categoryActivity) {
		// 		$id=$categoryActivity["id"];
		// 		if ($id)
		// 			$postId=$id;
		// 	}
		// }

		$swagpath=Swagpath::getById($postId);
		if (!$swagpath)
			return;

		$swagUser=SwagUser::getByEmail($statement["actor"]["mbox"]);
		$swagpath->saveProvidedSwagIfCompleted($swagUser);

		$path=parse_url($id,PHP_URL_PATH);
		$vars = explode('/', $path);

		$swagifact_slug = end($vars);

		$swagifacts=$swagpath->getSwagPostItems();
		$h5p=H5pUtil::getH5pById($statement["object"]["definition"]["extensions"]["http://h5p.org/x-api/h5p-local-content-id"]);
		$swagifact = SwagPostItem::create("h5p", $h5p['slug']);
		$swagifact->setSwagPost($swagpath);

		return array(
			"swagpathComplete"=>$swagpath->isCompletedByUser($swagUser),
			"swagifactComplete"=>$swagifact->isCompleted($swagUser)
		);
	}

	/**
	 * Provide settings for wp-h5p-xapi
	 */
	public function ti_xapi_h5p_auth_settings($arg) {
		$xapi=SwagPlugin::instance()->getXapi();

		if ($xapi) {
			return array(
				"endpoint_url"=>$xapi->endpoint,
				"username"=>$xapi->username,
				"password"=>$xapi->password
			);
		}
		return;
	}

	/**
	 * Provide settings for wp-deliverable
	 */
	public function ti_deliverable_xapi_auth_settings($arg) {
		$xapi=SwagPlugin::instance()->getXapi();

		if ($xapi) {
			return array(
				"endpoint_url"=>$xapi->endpoint,
				"username"=>$xapi->username,
				"password"=>$xapi->password
			);
		}
		return;
	}
}
