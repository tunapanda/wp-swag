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
        $this->settings_api = new WeDevs_Settings_API;
        // add_action("wp_ajax_install_swagtoc", array($this, "installSwagToc"));
        add_action("admin_init", array($this, "admin_init"));
    }

    public function admin_init() {
        $about = new Template(__DIR__ . "/../../tpl/settings_about.php");

        $this->settings_api->set_sections(array(
            array(
                'id' => 'about',
                'title' => 'About'
            ),
            array(
                'id' => 'xapi_settings',
                'title' => 'xAPI Settings'
            ),
            array(
                'id' => 'theme_options',
                'title' => 'Theme Options',
            ),
        ));

        $this->settings_api->set_fields(array(
            'about' => array(
                    array(
                    'name'        => 'About',
                    'desc'        => $about->render(),
                    'type'        => 'html'
                ),
            ),
            'xapi_settings' => array(
                array(
                    'name' => 'xapi_description',
                    'desc' => '
                    <p>
                        The settings in this section specifies the URL and credentials when connecting to
                        the LRS to fetch and store information.
                    </p><p>
                    If the <a href="https://github.com/tunapanda/wp-xapi-lrs">xAPI LRS</a>
                    plugin is installed and activated,
                    it will be used instead of an external LRS.
                </p>',
                'type' => 'html'
                ),
                array(
                    'name' => 'xapi_endpoint_url',
                    'label' => __( 'xAPI Endpoint URL', 'swag' ),
                    'default' => get_option("ti_xapi_endpoint_url"),
                    'type' => 'text'
                ),
                array(
                    'name' => 'xapi_endpoint_username',
                    'label' => __( 'xAPI Username', 'swag' ),
                    'default' => get_option("ti_xapi_username"),
                    'type' => 'text'
                ),
                array(
                    'name' => 'xapi_endpoint_password',
                    'label' => __( 'xAPI Password', 'swag' ),
                    'default' => get_option("ti_xapi_password"),
                    'type' => 'text'
                )
            ),
            'theme_options' => array(
                array(
                    'name' => 'homepage_video',
                    'label' => __('Homepage Video URL', 'swag'),
                    'desc' => __('Youtube and many other embeds are supported.'),
                    'type' => 'file',
                    'options' => array(
                        'button_label' => 'Choose Video'
                    )
                )
            ),
        ));

        $this->settings_api->admin_init();
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

    private function theme()
    {
        ob_start();
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        return ob_get_flush();
    }

    /**
     * Process request.
     */
    public function process()
    {
        echo '<div class="wrap">';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</div>';

        // $template = new Template(__DIR__ . "/../../tpl/settings.php");
        // $template->set("adminUrl", admin_url("options-general.php") . "?page=ti_settings");

        // $tab = "about";
        // if (isset($_REQUEST["tab"])) {
        //     $tab = $_REQUEST["tab"];
        // }

        // $template->set("tab", $tab);
        // $template->set("tabs", array(
        //     "about" => "About",
        //     "theme" => "Theme",
        //     "xapi" => "xAPI Settings",
        // ));

        // switch ($tab) {
        //     case "about":
        //         $template->set("content", $this->about());
        //         break;

        //     case "xapi":
        //         $template->set("content", $this->xapi());
        //         break;
        //     case "theme":
        //         $template->set("content", $this->theme());
        //         break;
        //     default:
        //         $template->set("content", "No such tab");
        //         break;
        // }

        // $template->show();
    }
}
