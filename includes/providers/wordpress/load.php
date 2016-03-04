<?php
/**
 * BetterOptin Provider WordPress
 *
 * @package   BetterOptin/Provider/WordPress
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get the current provider
$provider = wpbo_get_option( 'mailing_provider', '' );

// Default to WP provider settings if no provider is set (used during install)
if ( 'word-press' === $provider || empty( $provider ) ) {

	// Load provider files
	require( WPBO_PATH . 'includes/providers/wordpress/settings.php' );
	require( WPBO_PATH . 'includes/providers/wordpress/class-provider-wordpress.php' );

	// Hook provider actions
	add_action( 'admin_menu', 'wpbo_wp_add_leads_menu', 9 );

}

add_filter( 'wpbo_mailing_providers', 'wpbo_provider_register_wordpress' );
/**
 * Register the WordPress provider
 *
 * @since 2.0
 *
 * @param array $providers Existing providers
 *
 * @return array
 */
function wpbo_provider_register_wordpress( $providers ) {

	$providers['word-press'] = 'WordPress';

	return $providers;
}

/**
 * Add link to leads.
 *
 * Add a direct link to the list of leads
 * collected by BetterOptin.
 *
 * @since    1.2.1
 */
function wpbo_wp_add_leads_menu() {

	$role = wpbo_get_option( 'wp_default_role' );
	$page = add_query_arg( array( 'role' => $role ), 'users.php' );

	add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Leads', 'wpbo' ), __( 'Leads', 'wpbo' ), 'administrator', $page );

}