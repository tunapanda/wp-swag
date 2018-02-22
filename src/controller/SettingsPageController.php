<?php

require_once __DIR__ . "/../utils/Singleton.php";

use swag\Singleton;

/**
 * Controller for showing the settings page.
 */
class SettingsPageController extends Singleton
{

    public function init()
    {
        add_action("wp_ajax_install_swagtoc", array($this, "installSwagToc"));
    }

    /**
     * Install table of contents.
     */
    public function installSwagToc()
    {
        global $wpdb;

        $q = $wpdb->prepare(
            "SELECT ID " .
            "FROM   {$wpdb->prefix}posts " .
            "WHERE  post_type=%s " .
            "AND    post_name=%s ",
            "swag", "toc");
        $id = $wpdb->get_var($q);

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        if (!$id) {
            throw new Exception("No swag toc front page available");
        }

        update_option("show_on_front", "page");
        update_option("page_on_front", $id);
        wp_redirect(admin_url('options-reading.php'));
    }

    /**
     * Render the xapi page.
     */
    private function xapi()
    {
        $t = new Template(__DIR__ . "/../../tpl/settings_xapi.php");

        if (is_plugin_active("wp-xapi-lrs/wp-xapi-lrs.php")) {
            $t->set("usingInternalLrs", true);
        } else {
            $t->set("usingInternalLrs", false);
        }

        return $t->render();
    }

    /**
     * Render the about page.
     */
    private function about()
    {
        $t = new Template(__DIR__ . "/../../tpl/settings_about.php");
        return $t->render();
    }

    /**
     * Process request.
     */
    public function process()
    {
        $template = new Template(__DIR__ . "/../../tpl/settings.php");
        $template->set("adminUrl", admin_url("options-general.php") . "?page=ti_settings");

        $tab = "about";
        if (isset($_REQUEST["tab"])) {
            $tab = $_REQUEST["tab"];
        }

        $template->set("tab", $tab);
        $template->set("tabs", array(
            "about" => "About",
            "xapi" => "xAPI Settings",
        ));

        switch ($tab) {
            case "about":
                $template->set("content", $this->about());
                break;

            case "xapi":
                $template->set("content", $this->xapi());
                break;

            default:
                $template->set("content", "No such tab");
                break;
        }

        $template->show();
    }
}
