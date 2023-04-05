<?php

namespace FoxApp\CustomPopupTrigger;
/*
@link https://plugins.foxapp.net/
Plugin Name: FoxApp - Custom Elementor Popup Triggers
Plugin URI: https://plugins.foxapp.net/
Description: Plugin adds additional triggers to Elementor Pro Popups.
Version: 1.0.2
Author: FoxApp
Author URI: https://plugins.foxapp.net/
Requires at least: 6.0
Requires PHP: 7.4
Text Domain: foxapp-custom-popup-triggers
Domain Path: /languages
*/
define('CPT_FILE', __FILE__);
//Plugin Check Dependencies
require_once( __DIR__ . "/plugin_check_dependencies.php" );
new \FoxApp\CustomPopupTrigger\PluginCheckDependencies();

//Plugin Admin Page
require_once( __DIR__ . "/plugin_admin_page.php" );
new \FoxApp\CustomPopupTrigger\PluginAdminPage();

//Plugin Init Hooks
require_once( __DIR__ . "/plugin_init_hooks.php" );
new \FoxApp\CustomPopupTrigger\PluginInitHooks();

// Init Fox App GitHub Updater for current Plugin File
add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'FoxApp\GitHub\Init' ) ) {
			new \FoxApp\GitHub\Init( __FILE__ );
		}
	}
);