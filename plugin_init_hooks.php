<?php

namespace FoxApp\CustomPopupTrigger;

class PluginInitHooks {
	public $plugin;
	public $plugin_slug;
	public $plugin_text_domain;
	public $plugin_identifier;

	public function __construct() {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$this->plugin             = get_plugin_data( CPT_FILE );
		$this->plugin_slug        = basename( CPT_FILE, '.php' );
		$this->plugin_text_domain = $this->plugin['TextDomain'];
		$this->plugin_identifier  = md5( $this->plugin_text_domain );

		add_action( 'wp_footer', [ $this, 'inject_hooks' ], 10, 5 );
	}

	public function inject_hooks() {
		$cpt_enabled = (bool) get_option( 'enabled' . $this->plugin_identifier );
		$cpt_debug   = (bool) get_option( 'debug' . $this->plugin_identifier );
		if ( ! $cpt_enabled ) {
			return;
		}

		$cpt_popup_id      = get_option( 'popup_id' . $this->plugin_identifier );
		$cpt_popup_seconds = (int) get_option( 'popup_seconds' . $this->plugin_identifier ) * 1000;

		$popup_id = pll_get_post( $cpt_popup_id, pll_current_language() );

		if ( ! $popup_id ) {
			$popup_id = $cpt_popup_id;
		}
        //Register Popup to current page
		\ElementorPro\Modules\Popup\Module::add_popup_to_location( $popup_id );
		?>
        <script>
            jQuery(document).ready(function () {
                jQuery(window).on('elementor/frontend/init', function () { //wait for elementor to load
					<?php if($cpt_debug){ ?> console.log('elementor/frontend/init'); <?php } ?>
                    elementorFrontend.on('components:init', function () { //wait for elementor pro to load
						<?php if($cpt_debug){ ?> console.log('components:init'); <?php } ?>

                        let cptRegisteredPopup = setInterval(() => {
                            if(typeof(elementorFrontend.documentsManager.documents[<?php echo $popup_id;?>]) !== 'undefined'){
                                if( jQuery('.dialog-widget').is(':visible') ){
                                    console.log('Conflicted with another popup');
                                    return;
                                }
                                elementorFrontend.documentsManager.documents[<?php echo $popup_id;?>].showModal();
                            }
                        }, <?php echo $cpt_popup_seconds; ?>);

                        function cptStartShowPopup() {
                            cptRegisteredPopup = setInterval(() => {
                                if(typeof(elementorFrontend.documentsManager.documents[<?php echo $popup_id;?>]) !== 'undefined'){
                                    if( jQuery('.dialog-widget').is(':visible') ){
                                        console.log('Conflicted with another popup');
                                        return;
                                    }
                                    elementorFrontend.documentsManager.documents[<?php echo $popup_id;?>].showModal();
                                }
                            }, <?php echo $cpt_popup_seconds; ?>);
                        }

                        window.addEventListener('elementor/popup/show', (event) => {
                            const id = event.detail.id;
                            const instance = event.detail.instance;

                            /******* START MAGIC CODE *******/
                            const popups = document.querySelectorAll('.elementor-popup-modal');
                            for (let i = 0; i < popups.length; i++) {
                                if( i === 0 && popups.length > 1) { popups[i].style.display = 'none'; }
                            }
                            /******* END MAGIC CODE *******/

                            if (id === <?php echo $popup_id;?> ) {
								<?php if($cpt_debug){ ?> console.log('elementor/popup/show', id); <?php } ?>
                                clearInterval(cptRegisteredPopup);
                            }
                        });

                        window.addEventListener('elementor/popup/hide', (event) => {
                            const id = event.detail.id;
                            const instance = event.detail.instance;
                            if (id === <?php echo $popup_id;?> ) {
								<?php if($cpt_debug){ ?> console.log('elementor/popup/hide', id); <?php } ?>
                                cptStartShowPopup();
                            }
                        });
                    });
                });
            });
        </script>
		<?php

		return;
	}
}