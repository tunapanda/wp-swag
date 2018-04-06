<?php

require __DIR__ . "/../utils/Xapi.php";

/**
 * Common base functions.
 */
class SwagPlugin
{

    private static $instance;

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Get xapi endpoint, if configured.
     */
    public function getXapi()
    {
        static $xapi;

        if (!$xapi) {
            if (is_plugin_active("wp-xapi-lrs/wp-xapi-lrs.php")) {
                $endpoint = site_url() . "/wp-content/plugins/wp-xapi-lrs/endpoint.php";
                $username = get_option("xapilrs_username");
                $password = get_option("xapilrs_password");

                $xapi = new Xapi($endpoint, $username, $password);
            } else {
                $settings = get_option('xapi_settings');
                $old_endpoint = get_option("ti_xapi_endpoint_url");
                

                if (isset($settings["xapi_endpoint_url"]) && isset($settings["xapi_endpoint_username"]) && isset($settings["xapi_endpoint_password"])) {
                    $endpoint = $settings["xapi_endpoint_url"];
                    $username = $settings["xapi_endpoint_username"];
                    $password = $settings["xapi_endpoint_password"];

                    $xapi = new Xapi($endpoint, $username, $password);
                } else if ($old_endpoint) {
                    $old_username = get_option("ti_xapi_username");
                    $old_password = get_option("ti_xapi_password");

                    $xapi = new Xapi($old_endpoint, $old_username, $old_password);
                } else {
                    $xapi = null;
                }

            }
        }

        return $xapi;
    }

    /**
     * Get sinleton instance.
     */
    public static function instance()
    {
        if (!SwagPlugin::$instance) {
            SwagPlugin::$instance = new SwagPlugin();
        }

        return SwagPlugin::$instance;
    }
}
