<?php
/**
 * Schema Pro Init
 *
 * @package Schema Pro
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro' ) ) {

	/**
	 * BSF_AIOSRS_Pro initial setup
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 *  Constructor
		 */
		public function __construct() {

			// Includes Required Files.
			$this->includes();
			add_action( 'admin_notices', array( $this, 'setup_wizard_notice' ) );
			add_action( 'wp_ajax_wp_schema_pro_setup_wizard_notice', array( $this, 'wp_schema_pro_setup_wizard_notice_callback' ) );
		}

		/**
		 * Setup Wizard
		 *
		 * @since 1.1.0
		 */
		public function setup_wizard_notice() {

			if ( get_transient( 'wp-schema-pro-activated' ) ) {
				$url = admin_url( 'index.php?page=aiosrs-pro-setup-wizard' );

				echo '<div class="wp-schema-pro-setup-wizard-notice notice notice-success is-dismissible">';
				echo '<p>' . __( 'Configure Schema Pro step by step. ', 'wp-schema-pro' ) . '<a href="' . esc_url( $url ) . '">' . __( 'Start setup wizard &raquo;', 'wp-schema-pro' ) . '</a></p>';
				echo '</div>';
				?>
				<script type="text/javascript">
					(function($){
						$(document).on('click', '.wp-schema-pro-setup-wizard-notice .notice-dismiss', function(){
							$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
									action 	: 'wp_schema_pro_setup_wizard_notice',
									nonce : '<?php echo wp_create_nonce( 'wp-schema-pro-setup-wizard-notice' ); ?>'
								},
							});
						});
					})(jQuery);
				</script>
				<?php
			}
		}

		/**
		 * Dismiss Notice
		 *
		 * @return void
		 */
		public function wp_schema_pro_setup_wizard_notice_callback() {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'wp-schema-pro-setup-wizard-notice' ) ) {
				wp_send_json_error( 'Invalid Nonce' );
			}

			delete_transient( 'wp-schema-pro-activated' );
			wp_send_json_success();
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {
			require_once BSF_AIOSRS_PRO_DIR . 'classes/lib/target-rule/class-bsf-target-rule-fields.php';
			require_once BSF_AIOSRS_PRO_DIR . 'classes/lib/class-bsf-custom-post-list-table.php';
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-wp-schema-pro-yoast-compatibility.php';
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-admin.php';
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-schema.php';
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-custom-fields-markup.php';

			/**
			 * Frontend.
			 */
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-schema-template.php';
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-markup.php';

			/**
			 * Posts Integration.
			 */
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-posts.php';

			/**
			 * WooCommerce Integration.
			 */
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-woocommerce.php';

			/**
			 * FAQ Auto-generation.
			 */
			require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-faq-auto.php';
		}

	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
BSF_AIOSRS_Pro::get_instance();
