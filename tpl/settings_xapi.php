<p>
    The settings in this section specifies the URL and credentials when connecting to
    the LRS to fetch and store information.
</p>
<?php if ($usingInternalLrs) { ?>
    <p>
        The 
        <a href="<?php echo admin_url("options-general.php?page=xapilrs_info_page"); ?>">xAPI LRS</a>
        plugin is active, so this will be used as LRS.
        Disable this plugin if you want to use an external LRS.
    </p>
<?php } else { ?>
    <p>
        If the <a href="https://github.com/tunapanda/wp-xapi-lrs">xAPI LRS</a>
        plugin is installed and activated,
        it will be used instead of an external LRS.
    </p>
    <form method="post" action="options.php">
        <?php settings_fields('ti'); ?>
        <?php do_settings_sections('ti'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">xAPI Endpoint URL</th>
                <td>
                    <input type="text" name="ti_xapi_endpoint_url" 
                        value="<?php echo esc_attr(get_option("ti_xapi_endpoint_url")); ?>" 
                        class="regular-text swag-xapi-setting"/>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">xAPI Username</th>
                <td>
                    <input type="text" name="ti_xapi_username" 
                        value="<?php echo esc_attr(get_option("ti_xapi_username")); ?>" 
                        class="regular-text swag-xapi-setting"/>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">xAPI Password</th>
                <td>
                    <input type="text" name="ti_xapi_password" 
                        value="<?php echo esc_attr(get_option("ti_xapi_password")); ?>" 
                        class="regular-text swag-xapi-setting"/>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
<?php } ?>
