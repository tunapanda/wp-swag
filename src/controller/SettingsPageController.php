<?php

/**
 * Controller for showing the settings page.
 */
class SettingsPageController {

	/**
	 * Render the dependencies page.
	 */
	private function dependencies() {
		$dependencies=array(
			array(
				"title"=>"GitHub Updater",
				"description"=>"Provides a way to install plugins from GitHub.",
				"plugin"=>"github-updater/github-updater.php",
				"link"=>$this->createBlankTargetLink("GitHub Updater","https://github.com/afragen/github-updater")
			),

			array(
				"title"=>"H5P",
				"description"=>"H5P is used for creating and adding rich content to your website. It is used for the presentations in the swagifacts.",
				"plugin"=>"h5p/h5p.php",
				"link"=>$this->createStandardPluginInstallLink("H5P","h5p")
			),

			array(
				"title"=>"H5P xAPI",
				"description"=>"H5P xAPI is used for sending achievements for H5P to an xAPI enabled LRS.",
				"plugin"=>"wp-h5p-xapi/wp-h5p-xapi.php",
				"link"=>$this->createStandardPluginInstallLink("H5P xAPI","wp-h5p-xapi")
			),

			array(
				"title"=>"Deliverable",
				"description"=>"This plugin lets learners submit deliverables and have coaches review them.\nNeeds te be installed using github-updater",
				"plugin"=>"wp-deliverable/wp-deliverable.php",
				"link"=>$this->createBlankTargetLink("Deliverable","https://github.com/tunapanda/wp-deliverable")
			),
		);

		foreach ($dependencies as &$dependency) {
			if (is_plugin_active($dependency["plugin"]))
				$dependency["status"]="ok";

			else
				$dependency["status"]="missing";
		}

		$t=new Template(__DIR__."/../../tpl/settings_dependencies.php");
		$t->set("dependencies",$dependencies);
		return $t->render();
	}

	/**
	 * Create a link for installing standard wordpress plugins.
	 */
	private function createStandardPluginInstallLink($title, $slug) {
		$url=admin_url("plugin-install.php?tab=plugin-information&amp;plugin=".$plugin."&amp;TB_iframe=true&amp;width=712&amp;height=500");

		return "<a href='$url' class='thickbox' aria-label='More information about $plugin' data-title='$plugin'>$title</a>";
	}

	/**
	 * Create a link for installing standard wordpress plugins.
	 */
	private function createBlankTargetLink($title, $url) {
		return "<a href='$url' target='_blank'>$title</a>";
	}

	/**
	 * Render the xapi page.
	 */
	private function xapi() {
		$t=new Template(__DIR__."/../../tpl/settings_xapi.php");
		return $t->render();
	}

	/**
	 * Render the about page.
	 */
	private function about() {
		$t=new Template(__DIR__."/../../tpl/settings_about.php");
		return $t->render();
	}

	/**
	 * Process request.
	 */
	public function process() {
		$template=new Template(__DIR__."/../../tpl/settings.php");
		$template->set("adminUrl",admin_url("options-general.php")."?page=ti_settings");

		$tab="about";
		if (isset($_REQUEST["tab"]))
			$tab=$_REQUEST["tab"];

		$template->set("tab",$tab);
		$template->set("tabs",array(
			"about"=>"About",
			"dependencies"=>"Dependencies",
			"xapi"=>"xAPI Settings"
		));

		switch ($tab) {
			case "about":
				$template->set("content",$this->about());
				break;

			case "xapi":
				$template->set("content",$this->xapi());
				break;

			case "dependencies":
				$template->set("content",$this->dependencies());
				break;

			default:
				$template->set("content","No such tab");
				break;
		}

		$template->show();
	}
}