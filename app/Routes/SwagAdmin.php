<?php

namespace Swag\App\Routes;

use \Swag\App\Utils\Settings_API;

/**
 * Manages Admin pages for Swag
 */
class SwagAdmin
{
  /**
   * Initialize settings
   */
  public function __construct()
  {
    $this->settings_api = new Settings_API;

    $this->init_hooks();
  }

  /**
   * Wordpress Admin Hooks
   *
   * @return void
   */
  private function init_hooks()
  {
    add_action('admin_menu', function () {
      add_menu_page('Swag', 'Swag', 'manage_options', 'swag', [$this, 'about_page'], plugins_url('assets/images/swag_icon_sm.png', dirname(dirname(__FILE__))), 30);
      add_submenu_page('swag', 'New Swagpath', 'New Swagpath', 'manage_options', 'post-new.php?post_type=swagpath');
      add_submenu_page('swag', 'Swagtracks', 'Swagtracks', 'manage_options', 'edit-tags.php?taxonomy=swagtrack');
      add_submenu_page('swag', 'xAPI Settings', 'xAPI Settings', 'manage_options', 'swag_xapi', [$this, 'settings']);
      add_submenu_page('swag', 'Badge Settings', 'Badge Settings', 'manage_options', 'swag_badges', [$this, 'settings']);
      add_submenu_page('swag', 'About Swag', 'About', 'manage_options', 'swag_about', array($this, 'about_page'));

    });

    add_action("admin_init", [$this, "admin_init"]);
  }

  /**
   * Output the about page
   *
   * @return void
   */
  public function about_page()
  {
    require_once dirname(dirname(__FILE__)) . '/Templates/about_swag.php';
  }

  /**
   * Uses settings API to create options pages
   *
   * @return void
   */
  public function admin_init()
  {
    $this->settings_api->set_sections([
      [
        'id' => 'swag_xapi',
        'title' => 'xAPI Settings',
      ],
      [
        'id' => 'swag_badges',
        'title' => 'Open Badges',
      ],
    ]);

    $this->settings_api->set_fields([
      'swag_xapi' => [
        [
          'name' => 'endpoint_url',
          'label' => __('xAPI Endpoint URL', 'swag'),
          'desc' => __('Full URL to xAPI server'),
          'type' => 'text',
          'sanitize_callback' => 'sanitize_text_field',
        ],
        [
          'name' => 'endpoint_client_key',
          'label' => __('xAPI Client Key', 'swag'),
          'desc' => __('Key for xAPI endpoint'),
          'type' => 'text',
          'sanitize_callback' => 'sanitize_text_field',
        ],
        [
          'name' => 'endpoint_client_secret',
          'label' => __('xAPI Client Secret', 'swag'),
          'desc' => __('Secrent for xAPI endpoint'),
          'type' => 'text',
          'sanitize_callback' => 'sanitize_text_field',
        ],
      ],
      'swag_badges' => [
        [
          'name' => 'issuer_name',
          'label' => __('Issuer Name', 'swag'),
          'desc' => __('Defaults to Site Title'),
          'default' => get_option('blogname'),
          'type' => 'text',
          'sanitize_callback' => 'sanitize_text_field',
        ],
        [
          'name' => 'issuer_description',
          'label' => __('Issuer Description', 'swag'),
          'type' => 'textarea',
          'sanitize_callback' => 'sanitize_text_field',
        ],
        [
          'name' => 'image',
          'label' => __('Image', 'swag'),
          'type' => 'file',
          'default' => '',
          'options' => [
            'button_label' => 'Choose Image',
          ],
        ],
        [
          'name' => 'issuer_url',
          'label' => __('Issuer URL', 'swag'),
          'desc' => __('Defaults to Site Homepage'),
          'default' => get_option('siteurl'),
          'type' => 'text',
          'sanitize_callback' => 'sanitize_text_field',
        ],
        [
          'name' => 'issuer_email',
          'desc' => __('Defaults to Admin Email'),
          'label' => __('Issuer Email', 'swag'),
          'default' => get_option('admin_email'),
          'type' => 'text',
          'sanitize_callback' => 'sanitize_text_field',
        ],
        [
          'name' => 'default_badge_image',
          'label' => __('Default Badge Image', 'swag'),
          'type' => 'file',
          'desc' => __('Default Image for badges', 'swag'),
          'default' => '',
          'options' => [
            'button_label' => 'Choose Image',
          ],
        ],
      ],
    ]);

    $this->settings_api->admin_init();
  }

  /**
   * Output the settings page
   *
   * @return void
   */
  public function settings()
  {
    echo '<div class="wrap">';
    $this->settings_api->show_navigation();
    $this->settings_api->show_forms();
    echo '</div>';
  }
}
