<?php

require_once __DIR__."/../../ext/TGM-Plugin-Activation/class-tgm-plugin-activation.php";
require_once __DIR__."/../utils/Singleton.php";

use swag\Singleton;

/**
 * Manage plugin dependencies.
 */
class SwagTgmpaController extends Singleton {

	/**
	 * Init.
	 */
	public function init() {
		add_action("tgmpa_register",array($this,"registerRequiredPlugins"));
	}

	/**
	 * Register plugins we require.
	 */
	public function registerRequiredPlugins() {
		$plugins = array(
			array(
				'name'      => 'H5P',
				'slug'      => 'h5p',
				'required'  => true,
			),
			array(
				'name'      => 'H5P xAPI',
				'slug'      => 'wp-h5p-xapi',
				'required'  => true,
			),
			array(
				'name'      => 'Remote Sync',
				'slug'      => 'wp-remote-sync',
				'source'    => "https://github.com/tunapanda/wp-remote-sync/archive/master.zip",
				'required'  => true,
			),
			array(
				'name'      => "Deliverable",
				"slug"      => "wp-deliverable",
				"source"    => "https://github.com/tunapanda/wp-deliverable/archive/master.zip",
				"required"  => true
			),
			array(
				'name'      => 'xAPI LRS',
				'slug'      => 'wp-xapi-lrs',
				'source'    => "https://github.com/tunapanda/wp-xapi-lrs/archive/master.zip",
			),
			array(
				'name'      => 'GitHub Updater',
				'slug'      => 'github-updater',
				'source'    => "https://github.com/afragen/github-updater/archive/master.zip",
			),
		);

		$config = array(
			'id'           => 'wp-swag',
			'menu'         => 'tgmpa-install-plugins',
			'parent_slug'  => 'plugins.php',
			'capability'   => 'manage_options',
			'dismissable'  => false,
			'message'      => '<p>These plugins are required in order to run Tunapanda Swag.</p>',

			'strings'=>array(
				'menu_title'=> __( 'Plugins required by Tunapanda Swag', 'wp-swag' ),
				'notice_can_install_required'     => _n_noop(
					'Tunapanda Swag requires the following plugin: %1$s.',
					'Tunapanda Swag requires the following plugins: %1$s.',
					'wp-swag'
				),
				'notice_can_install_recommended'     => _n_noop(
					'Tunapanda Swag recommends the following plugin: %1$s.',
					'Tunapanda Swag recommends the following plugins: %1$s.',
					'wp-swag'
				),
				'notice_can_activate_required'    => _n_noop(
					'The following plugin required by Tunapanda Swag is currently inactive: %1$s.',
					'The following plugins required by Tunapanda Swag are currently inactive: %1$s.',
					'wp-swag'
				),
				'notice_can_activate_recommended' => _n_noop(
					'The following plugin recommended by Tunapanda Swag is currently inactive: %1$s.',
					'The following plugins recommended by Tunapanda Swag are currently inactive: %1$s.',
					'wp-swag'
				),
			)
		);

		tgmpa( $plugins, $config );
	}
}