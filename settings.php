<div class="wrap">
    <h2>Tunapanda Learning</h2>

    <h3>About</h3>
    <p>
        These settings come as part of installing the theme for content.tunapanda.org.
    </p>

    <h3>xAPI Endpoint Settings</h3>
    <p>
        The settings in this section specifies the URL and credentials when connecting to
        the LRS to fetch information.
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
                        class="regular-text"/>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">xAPI Username</th>
                <td>
                    <input type="text" name="ti_xapi_username" 
                        value="<?php echo esc_attr(get_option("ti_xapi_username")); ?>" 
                        class="regular-text"/>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">xAPI Password</th>
                <td>
                    <input type="text" name="ti_xapi_password" 
                        value="<?php echo esc_attr(get_option("ti_xapi_password")); ?>" 
                        class="regular-text"/>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>