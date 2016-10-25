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

class WP_Swag_admin{
	static $plugins_uri;

	public static function init_hooks(){
		self::$plugins_uri = plugins_url()."/wp-swag";

		// initialise the the admin settings
		add_action('admin_init',array(get_called_class(),'ti_admin_init'));
		add_action('admin_menu',array(get_called_class(),'ti_admin_menu'));

		add_action('wp_enqueue_scripts',array(get_called_class(), "ti_enqueue_scripts"));
		add_action('admin_enqueue_scripts',array(get_called_class(), "ti_enqueue_scripts"));

		add_action("h5p-xapi-post-save",array(get_called_class(),"ti_xapi_post_save"));
		add_action("h5p-xapi-pre-save",array(get_called_class(),"ti_xapi_pre_save"));
		add_action("deliverable-xapi-post-save",array(get_called_class(), "ti_xapi_post_save"));
		add_shortcode("my-swag",array(get_called_class(), "ti_my_swag"));

		add_filter("h5p-xapi-auth-settings",
			array(get_called_class(),"ti_xapi_h5p_auth_settings"));

		add_filter("deliverable-xapi-auth-settings",
			array(get_called_class(),"ti_deliverable_xapi_auth_settings"));

		SwagpathController::instance()->init();
		SwagPageController::instance()->init();
		SettingsPageController::instance()->init();
		SwagTrackController::instance()->init();
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
			array(get_called_class(), 'ti_create_settings_page')
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
		wp_register_script("ti-main",plugins_url("/main.js", __FILE__));

		wp_enqueue_script("ti-main");

		wp_enqueue_script("d3");
	}

	/**
	 * Here we have the chance to modify the statement before it is saved.
	 */
	public function ti_xapi_pre_save($statement) {
		return $statement;
	}

	/**
	 * Act on completed xapi statements.
	 * Save xapi statement for swag if applicable.
	 */
	public function ti_xapi_post_save($statement) {
		if ($statement["verb"]["id"]!="http://adlnet.gov/expapi/verbs/completed")
			return;

		foreach ($statement["context"]["contextActivities"]["grouping"] as $groupingActivity) {
			$id=url_to_postid($groupingActivity["id"]);
			if ($id)
				$postId=$id;
		}

		foreach ($statement["context"]["contextActivities"]["category"] as $categoryActivity) {
			$id=url_to_postid($categoryActivity["id"]);
			if ($id)
				$postId=$id;
		}

		if (!$postId)
			return;

		$post=get_post($postId);

		if (!$post)
			return;

		$swagUser=SwagUser::getByEmail($statement["actor"]["mbox"]);
		$swagpath=SwagPath::getById($post->ID);
		$swagpath->saveProvidedSwagIfCompleted($swagUser);
		/*$swagPost=new SwagPost($post);
		$swagPost->*/
	}

	public function ti_my_swag() {
		$plugins_uri = self::$plugins_uri;
		$swagUser=new SwagUser(wp_get_current_user());
		$completedSwag=$swagUser->getCompletedSwag();
		if (!$completedSwag)
			$completedSwag=array();

		$out="";

		foreach ($completedSwag as $swag) {
			if ($swag) {
				$out.="<div class='swag-badge-container'>\n";
				$out.="<img class='swag-badge-image' src='$plugins_uri/img/badge.png'>\n";
				$out.="<div class='swag-badge-label'>{$swag->getString()}</div>\n";
				$out.="</div>\n";
			}
		}

		return $out;
	}

	/**
	 * Provide settings for wp-h5p-xapi
	 */
	public function ti_xapi_h5p_auth_settings($arg) {
		$xapi=SwagPlugin::instance()->getXapi();

		return array(
			"endpoint_url"=>$xapi->endpoint,
			"username"=>$xapi->username,
			"password"=>$xapi->password
		);
	}

	/**
	 * Provide settings for wp-deliverable
	 */
	public function ti_deliverable_xapi_auth_settings($arg) {
		$xapi=SwagPlugin::instance()->getXapi();

		return array(
			"endpoint_url"=>$xapi->endpoint,
			"username"=>$xapi->username,
			"password"=>$xapi->password
		);
	}
}
