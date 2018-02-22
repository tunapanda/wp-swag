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
                $endpoint = get_option("ti_xapi_endpoint_url");
                $username = get_option("ti_xapi_username");
                $password = get_option("ti_xapi_password");

                if ($endpoint) {
                    $xapi = new Xapi($endpoint, $username, $password);
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
