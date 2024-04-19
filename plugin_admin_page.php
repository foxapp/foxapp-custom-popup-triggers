<?php

namespace FoxApp\CustomPopupTrigger;

class PluginAdminPage {
	public $plugin;
	public $plugin_slug;
	public $plugin_identifier;
	public $plugin_text_domain;

	public function __construct() {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$this->plugin             = get_plugin_data( CPT_FILE );
		$this->plugin_slug        = basename( CPT_FILE, '.php' );
		$this->plugin_text_domain = $this->plugin['TextDomain'];
		$this->plugin_identifier  = md5( $this->plugin_text_domain );
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	public function adminMenu(): void {
		add_menu_page(
			__( 'Custom Elementor Popup Triggers (ver. ' . $this->plugin['Version'] . ')', $this->plugin_text_domain ),
			__( 'Custom Triggers', $this->plugin_text_domain ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'adminPage' ),
			'dashicons-grid-view',
			100
		);
	}

	public function adminPage(): void {
		load_theme_textdomain( $this->plugin_text_domain, __DIR__ . '/languages' );
		?>
        <!-- Our admin page content should all be inside .wrap -->
        <div class="wrap">

            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php $this->plugin_form_render(); ?>
        </div>
		<?php
	}

	public function plugin_form_render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = 'custom_popup_triggers_settings';

		if (
			isset( $_POST['action'] ) &&
			$_POST['action'] === $action &&
			isset( $_POST[ $action . '_nonce_field' ] ) &&
			wp_verify_nonce( $_POST[ $action . '_nonce_field' ], $action . '_nonce_action' )
		) {
			//Save API Settings
			update_option( 'enabled' . $this->plugin_identifier, sanitize_text_field( $_POST['cpt_enabled'] ?? 0 ), 'yes' );
			update_option( 'debug' . $this->plugin_identifier, sanitize_text_field( $_POST['cpt_debug'] ?? 0 ), 'yes' );
			update_option( 'popup_id' . $this->plugin_identifier, sanitize_text_field( $_POST['cpt_popup_id'] ?? '' ), 'yes' );
			update_option( 'popup_seconds' . $this->plugin_identifier, sanitize_text_field( $_POST['cpt_popup_seconds'] ?? '' ), 'yes' );
			update_option( 'exclude_from_pages' . $this->plugin_identifier, implode( ',', $_POST['cpt_exclude_from_pages'] ?? [] ), 'yes' );
		}

		$cpt_enabled            = get_option( 'enabled' . $this->plugin_identifier );
		$cpt_debug              = get_option( 'debug' . $this->plugin_identifier );
		$cpt_popup_id           = get_option( 'popup_id' . $this->plugin_identifier ) ?? 0;
		$cpt_popup_seconds      = get_option( 'popup_seconds' . $this->plugin_identifier ) ?? 0;
		$cpt_exclude_from_pages = get_option( 'exclude_from_pages' . $this->plugin_identifier ) ?? [];
		$cpt_exclude_from_pages = explode( ',', $cpt_exclude_from_pages );
		?>
        <style>
            .list_popups td {
                padding: 5px;
            }

            .list_popups tbody td {
                border-bottom: 1px solid #fff;
            }
        </style>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
        <form method="post">
			<?php wp_nonce_field( $action . '_nonce_action', $action . '_nonce_field' ); ?>
            <input type="hidden" name="action" value="<?php echo $action ?>">
            <div style="display:flex">
                <div style="float:left;min-width:200px">
                    <table class="form-table">
                        <tbody>
                        <tr class="cpt_enabled">
                            <td scope="row"><label for="cpt_enabled">Enable</label></td>
                            <td><input type="checkbox"
                                       id="cpt_enabled"
                                       name="cpt_enabled"
                                       value="1" <?php checked( $cpt_enabled, 1 ) ?>
                                       class="regular-text">
                            </td>
                        </tr>
                        <tr class="cpt_popup_id">
                            <td scope="row"><label for="cpt_popup_id">Popup ID <sup>*</sup></label><br><small>If you
                                    have multiple languages please use ID of English Popup</small></td>
                            <td><input type="text"
                                       id="cpt_popup_id"
                                       name="cpt_popup_id"
                                       style="width:150px"
                                       value="<?php echo $cpt_popup_id ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr class="cpt_popup_seconds">
                            <td scope="row"><label for="cpt_popup_seconds">Show after x Seconds</label></td>
                            <td><input type="text"
                                       id="cpt_popup_seconds"
                                       name="cpt_popup_seconds"
                                       style="width:150px"
                                       value="<?php echo $cpt_popup_seconds ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr class="cpt_exclude_from_pages">
                            <td scope="row"><label for="cpt_exclude_from_pages">Exclude from pages?</label></td>
                            <td>
                                <select
                                        id="cpt_exclude_from_pages"
                                        name="cpt_exclude_from_pages[]"
                                        class="select2"
                                        multiple="multiple"
                                >
                                    <option value="">No page selected!</option>
									<?php
									$pages = get_pages();
									foreach ( $pages as $page ) {
										$option = '<option value="' . $page->ID . '" ' . selected( in_array( $page->ID, $cpt_exclude_from_pages ) ) . '>';
										$option .= $page->post_title;
										$option .= '</option>';
										echo $option;
									}
									?>
                                </select>
                            </td>
                        </tr>
                        <tr class="cpt_debug">
                            <td scope="row"><label for="cpt_debug" style="color:darkred">Debug?</label></td>
                            <td><input type="checkbox"
                                       id="cpt_debug"
                                       name="cpt_debug"
                                       value="1" <?php checked( $cpt_debug, 1 ) ?>
                                       class="regular-text">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div style="min-width:500px;padding-left:30px">
                    <h2><?php _e( 'List Elementor Popup\'s' ); ?></h2>
                    <table class="list_popups" style="width: 100%">
                        <thead style="background-color: #aa9d88">
                        <tr>
                            <td style="width: 100px"><strong>Popup ID</strong></td>
                            <td><strong>Name</strong></td>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						$args = array(
							'post_type'              => 'elementor_library',
							'posts_per_page'         => - 1,
							'tabs_group'             => 'library',
							'elementor_library_type' => 'popup',
						);

						$popup_templates = get_posts( $args );
						foreach ( $popup_templates as $popup_template ) {
							?>
                            <tr>
                                <td><?php echo $popup_template->ID; ?></td>
                                <td><?php echo $popup_template->post_title; ?></td>
                            </tr>
							<?php
						}
						?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="clear:both"></div>
            <input type="submit"
                   class="button-primary"
                   style="margin-top:40px"
                   value="<?php echo __( "Save settings", $this->plugin_text_domain ) ?>"/>
        </form>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <script>
            jQuery("#cpt_exclude_from_pages").select2();
        </script>
		<?php
	}

}
