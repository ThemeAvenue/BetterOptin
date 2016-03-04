<?php
/**
 * Better Optin.
 *
 * @package   BetterOptin
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 *
 * @wordpress-plugin
 * Plugin Name:       BetterOptin
 * Plugin URI:        https://betteropt.in/
 * Description:       BetterOptin helps you convert your visitors in subscribers and fill up your mailing lists.
 * Version:           2.0.0
 * Author:            ThemeAvenue
 * Author URI:        https://themeavenue.net
 * Text Domain:       betteroptin
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'BetterOptin' ) ):

	/**
	 * Main BetterOptin class
	 *
	 * This class is the one and only instance of the plugin. It is used
	 * to load the core and all its components.
	 *
	 * @since 2.0
	 */
	final class BetterOptin {

		/**
		 * @var BetterOptin Holds the unique instance of BetterOptin
		 * @since 2.0
		 */
		private static $instance;

		/**
		 * Possible error message.
		 *
		 * @since 2.0
		 * @var null|WP_Error
		 */
		protected $error = null;

		/**
		 * Instantiate and return the unique BetterOptin object
		 *
		 * @since     2.0
		 * @return object BetterOptin Unique instance of BetterOptin
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BetterOptin ) ) {

				// Instantiate
				self::$instance = new BetterOptin;
				self::$instance->init();


			}

			return self::$instance;

		}

		/**
		 * Instantiate the plugin
		 *
		 * @since 2.0
		 * @return void
		 */
		private function init() {

			self::$instance->setup_constants();

			// Make sure the WordPress version is recent enough
			if ( ! self::$instance->are_dependencies_loaded() ) {
				self::$instance->add_error( wp_kses_post( sprintf( __( 'BetterOptin cannot load because its dependencies are missing. If you don&#039;t know what this means, please purchase the pro version on %s', 'betteroptin' ), '<a href="https://betteropt.in/?utm_source=plugin&utm_medium=dependencies_nag&utm_campaign=upsell" target="_blank">https://betteropt.in</a>' ) ) );
			}

			// If we have any error, don't load the plugin
			if ( is_a( self::$instance->error, 'WP_Error' ) ) {
				add_action( 'admin_notices', array( self::$instance, 'display_error' ), 10, 0 );
				return;
			}

			self::$instance->setup_database_constants();
			self::$instance->includes();
			self::$instance->load_providers();

			if ( is_admin() ) {
				self::$instance->includes_admin();
			}

			add_action( 'plugins_loaded', array( self::$instance, 'load_plugin_textdomain' ) );
			add_action( 'init', array( self::$instance, 'license_notification' ) );
			add_action( 'init', array( self::$instance, 'check_provider_ready' ) );
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 2.0
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'betteroptin' ), '2.0' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since 2.0
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'betteroptin' ), '2.0' );
		}

		/**
		 * Setup all plugin constants
		 *
		 * @since 2.0
		 * @return void
		 */
		private function setup_constants() {
			define( 'WPBO_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
			define( 'WPBO_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			define( 'WPBO_BASENAME', plugin_basename( __FILE__ ) );
			define( 'WPBO_PLUGIN_FILE', __FILE__ );
			define( 'WPBO_VERSION', '2.0.0' );
			define( 'WPBO_DB_VERSION', '1' );
		}

		private function are_dependencies_loaded() {
			return is_dir( WPBO_PATH . 'vendor' );
		}

		/**
		 * Add error.
		 *
		 * Add a new error to the WP_Error object
		 * and create the object if it doesn't exist yet.
		 *
		 * @since  2.0
		 *
		 * @param string $message Error message to add
		 *
		 * @return void
		 */
		private function add_error( $message ) {

			if ( ! is_object( $this->error ) || ! is_a( $this->error, 'WP_Error' ) ) {
				$this->error = new WP_Error();
			}

			$this->error->add( 'addon_error', $message );

		}

		/**
		 * Display error.
		 *
		 * Get all the error messages and display them
		 * in the admin notices.
		 *
		 * @since  2.0
		 * @return void
		 */
		public function display_error() {
			if ( ! is_a( $this->error, 'WP_Error' ) ) {
				return;
			}
			$message = self::$instance->error->get_error_messages(); ?>

			<div class="error">
				<p>
					<?php
					if ( count( $message ) > 1 ) {
						echo '<ul>';
						foreach ( $message as $msg ) {
							echo "<li>$msg</li>";
						}
						echo '</li>';
					} else {
						echo $message[0];
					}
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Setup the custom database table constants
		 *
		 * @since 2.0
		 * @return void
		 */
		private function setup_database_constants() {

			global $wpdb;

			define( 'wpbo_analytics_table_name', 'wpbo_analytics' );
			define( 'wpbo_failsafe_table_name', 'wpbo_failsafe' );
			define( 'wpbo_analytics_table', $wpdb->prefix . wpbo_analytics_table_name );
			define( 'wpbo_failsafe_table', $wpdb->prefix . wpbo_failsafe_table_name );

		}

		/**
		 * Include all files used sitewide
		 *
		 * @since 2.0
		 * @return void
		 */
		private function includes() {

			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

				require( WPBO_PATH . 'includes/scripts.php' );
				require( WPBO_PATH . 'includes/shortcode.php' );
				require( WPBO_PATH . 'includes/functions-templating.php' );
				require( WPBO_PATH . 'includes/functions-dummy.php' );

			}

			require( WPBO_PATH . 'includes/class-popup.php' );
			require( WPBO_PATH . 'includes/functions-post-type.php' );
			require( WPBO_PATH . 'includes/functions-analytics.php' );
			require( WPBO_PATH . 'includes/functions-failsafe.php' );
			require( WPBO_PATH . 'includes/functions-popup.php' );
			require( WPBO_PATH . 'includes/functions-misc.php' );
			require( WPBO_PATH . 'includes/functions-ajax.php' );

		}

		/**
		 * Include all files used in admin only
		 *
		 * @since 2.0
		 * @return void
		 */
		private function includes_admin() {

			require( WPBO_PATH . 'includes/admin/functions-misc.php' );
			require( WPBO_PATH . 'vendor/julien731/wp-dismissible-notices-handler/handler.php' );

			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				require( WPBO_PATH . 'includes/admin/class-titan-framework.php' );
				require( WPBO_PATH . 'includes/admin/settings/settings-general.php' );
				require( WPBO_PATH . 'includes/admin/functions-menu.php' );
				require( WPBO_PATH . 'includes/admin/functions-metabox.php' );
				require( WPBO_PATH . 'includes/admin/functions-list-table.php' );
				require( WPBO_PATH . 'includes/install.php' );
			}

		}

		/**
		 * Load all the providers from the providers directory
		 *
		 * @since 2.0
		 * @return void
		 */
		private function load_providers() {
			require( WPBO_PATH . 'includes/providers/wordpress/load.php' );
			require( WPBO_PATH . 'includes/providers/mailchimp/load.php' );
			require( WPBO_PATH . 'includes/providers/mailpoet/load.php' );
			require( WPBO_PATH . 'includes/providers/aweber/load.php' );
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			apply_filters( 'plugin_locale', get_locale(), 'betteroptin' );

			load_plugin_textdomain( 'betteroptin', false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

		}

		/**
		 * Display an admin notice if license key is missing
		 *
		 * @since 2.0
		 * @return void
		 */
		public function license_notification() {

			if ( ! function_exists( 'DNH' ) ) {
				return;
			}

			$license = wpbo_get_option( 'license_key', '' );

			/**
			 * Do not show the notice if the license key has already been entered.
			 */
			if ( ! empty( $license ) ) {
				return;
			}

			$license_page = wpbo_get_settings_page_link();

			dnh_register_notice( 'wpbo_no_license', 'error', sprintf( __( 'You haven&#039;t entered your BetterOptin license key. This means that you will not get automatic updates and you will not get technical support. <a %s>Click here to enter your license key</a>.', 'betteroptin' ), "href='$license_page'" ), array( 'cap' => 'administrator' ) );

		}

		/**
		 * Make sure a provider is selected for the lead collection
		 *
		 * @since 2.0
		 * @return void
		 */
		public function check_provider_ready() {

			if ( ! function_exists( 'DNH' ) ) {
				return;
			}

			if ( false !== wpbo_is_provider_ready() ) {
				return;
			}

			$license_page = wpbo_get_settings_page_link();

			dnh_register_notice( 'wpbo_no_provider', 'error', sprintf( __( 'You haven&#039;t selected your provider for catching leads. <strong>BetterOptin will not work until you do so!</strong> <a %s>Click here to select your provider</a>.', 'betteroptin' ), "href='$license_page'" ) );

		}

	}

endif;

/**
 * The main function responsible for returning the unique BetterOptin instance
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 2.0
 * @return object BetterOptin
 */
function BO() {
	return BetterOptin::instance();
}

// Get BetterOptin Running
BO();