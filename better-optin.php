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
		 * Instantiate and return the unique BetterOptin object
		 *
		 * @since     2.0
		 * @return object BetterOptin Unique instance of BetterOptin
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BetterOptin ) ) {

				// Instantiate
				self::$instance = new BetterOptin;
				self::$instance->setup_constants();
				self::$instance->setup_database_constants();
				self::$instance->includes();
				self::$instance->load_providers();

				if ( is_admin() ) {
					self::$instance->includes_admin();
				}

				add_action( 'plugins_loaded', array( self::$instance, 'load_plugin_textdomain' ) );
				add_action( 'admin_notices', array( self::$instance, 'license_notification' ) );

			}

			return self::$instance;

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
				require( WPBO_PATH . 'includes/install.php' );

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

			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				require( WPBO_PATH . 'includes/admin/class-titan-framework.php' );
				require( WPBO_PATH . 'includes/admin/settings/settings-general.php' );
				require( WPBO_PATH . 'includes/admin/functions-misc.php' );
				require( WPBO_PATH . 'includes/admin/functions-menu.php' );
				require( WPBO_PATH . 'includes/admin/functions-metabox.php' );
				require( WPBO_PATH . 'includes/admin/functions-list-table.php' );
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

			/**
			 * We only want to display the notice to the site admin.
			 */
			if ( ! current_user_can( 'administrator' ) ) {
				return;
			}

			$license = wpbo_get_option( 'license_key', '' );

			/**
			 * Do not show the notice if the license key has already been entered.
			 */
			if ( ! empty( $license ) ) {
				return;
			}

			$license_page = wpbo_get_settings_page_link(); ?>

			<div class="updated error">
				<p><?php printf( __( 'You haven&#039;t entered your BetterOptin license key. This means that you will not get automatic updates and you will not get technical support. <a %s>Click here to enter your license key</a>.', 'betteroptin' ), "href='$license_page'" ); ?></p>
			</div>

		<?php }

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