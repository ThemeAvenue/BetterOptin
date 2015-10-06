<?php
/**
 * BetterOptin Menus
 *
 * @package   BetterOptin/Menus
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'admin_menu', 'wpbo_add_plugin_settings_page' );
/**
 * Add Plugin Menu Items.
 *
 * @since  1.0.0
 */
function wpbo_add_plugin_settings_page() {

	global $_registered_pages;

	/* Register customizer page without adding it to the menu */
	$_registered_pages['wpbo-popup_page_wpbo-customizer'] = true;
	add_action( 'wpbo-popup_page_wpbo-customizer', 'wpbo_display_popup_customizer' );

	add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Analytics', 'wpbo' ), __( 'Analytics', 'wpbo' ), 'administrator', 'wpbo-analytics', 'wpbo_display_popup_analytics' );
	add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Addons', 'wpbo' ), __( 'Addons', 'wpbo' ), 'administrator', 'wpbo-addons', 'wpbo_display_popup_addons' );
	add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'About', 'wpbo' ), __( 'About', 'wpbo' ), 'administrator', 'wpbo-about', 'wpbo_display_popup_about' );
	add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Documentation', 'wpbo' ), __( 'Documentation', 'wpbo' ), 'administrator', 'wpbo-doc', 'wpbo_display_popup_doc' );

}

/**
 * Display Customizer Page Content.
 *
 * @since  1.0.0
 */
function wpbo_display_popup_customizer() {
	require_once( WPBO_PATH . 'includes/admin/views/customizer.php' );
}

/**
 * Display Relationships.
 *
 * @since  1.0.0
 */
function wpbo_display_popup_relationships() {
	require_once( WPBO_PATH . 'includes/admin/views/relationships.php' );
}

/**
 * Display About Page.
 *
 * @since  1.0.0
 */
function wpbo_display_popup_about() {
	require_once( WPBO_PATH . 'includes/admin/views/about.php' );
}

/**
 * Display About Page.
 *
 * @since  1.0.0
 */
function wpbo_display_popup_doc() {
	require_once( WPBO_PATH . 'includes/admin/views/documentation.php' );
}

/**
 * Display Analytics Page.
 *
 * @since  1.0.0
 */
function wpbo_display_popup_analytics() {
	require_once( WPBO_PATH . 'includes/admin/views/analytics.php' );
}

/**
 * Display Addons Page.
 *
 * @since  1.0.0
 */
function wpbo_display_popup_addons() {
	require_once( WPBO_PATH . 'includes/admin/views/addons.php' );
}