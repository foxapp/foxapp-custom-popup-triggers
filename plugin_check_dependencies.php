<?php

namespace FoxApp\CustomPopupTrigger;

class PluginCheckDependencies {
	public $file;

	public function __construct() {
		$this->file = CPT_FILE;
		register_activation_hook( $this->file, array(
			$this,
			'on_activation'
		) );
	}

	public function on_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( ! class_exists( 'ElementorPro\Plugin' ) || ! class_exists( 'Elementor\Plugin' ) ) {
			// Deactivate the plugin.
			deactivate_plugins( plugin_basename( $this->file ) );
			// Throw an error in the WordPress admin console.
			$error_message = '<p>' . esc_html__( 'This plugin requires ', 'foxapp-delasport-api' ) .
			                 '<a href="' . esc_url( 'https://elementor.com/pricing/#features' ) . '">"ELEMENTOR" AND "ELEMENTOR PRO"</a>' .
			                 esc_html__( ' plugins to be active.', 'foxapp-custom-popup-triggers' ) . '</p>';
			die( $error_message ); // WPCS: XSS ok.
		}
	}
}