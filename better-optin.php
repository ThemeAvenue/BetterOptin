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
 * Plugin URI:        http://betteropt.in/
 * Description:       BetterOptin helps you convert your visitors in subscribers and fill up your mailing lists.
 * Version:           1.2.2
 * Author:            ThemeAvenue
 * Author URI:        http://themeavenue.net
 * Text Domain:       better-optin
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Define all the plugin constants */
define( 'WPBO_URL', plugin_dir_url( __FILE__ ) );
define( 'WPBO_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPBO_BASENAME', plugin_basename(__FILE__) );

require_once( plugin_dir_path( __FILE__ ) . 'includes/extras.php' );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-better-optin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-analytics.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/shortcode.php' );

/* Load submission class */
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-submission.php' );
$wpbo_submit = new WPBO_Submit;

/*----------------------------------------------------------------------------*
 * Load Addons
 *----------------------------------------------------------------------------*/
add_action( 'plugins_loaded', array( 'Better_Optin', 'load_addons' ) );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Better_Optin', 'activate' ) );

add_action( 'plugins_loaded', array( 'Better_Optin', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/class-titan-framework.php' );
	$wpbo_titan = new WPBO_Titan();

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-better-optin-admin.php' );
	add_action( 'plugins_loaded', array( 'Better_Optin_Admin', 'get_instance' ) );

}

/*----------------------------------------------------------------------------*
 * Generate Dummy Content
 *----------------------------------------------------------------------------*/
require_once( plugin_dir_path( __FILE__ ) . 'includes/dummy-content.php' );

if( isset( $_GET['wpbo_dummy'] ) ) {
	wpbo_add_dummy();
}

/**
 * Register default settings.
 *
 * Settings need to be registered sitewide
 * otherwise the values get deleted.
 */
add_filter( 'wpbo_plugin_settings', array( 'Better_Optin_Admin', 'settings' ), 9 );