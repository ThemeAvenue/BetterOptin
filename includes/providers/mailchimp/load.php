<?php
/**
 * BetterOptin Provider MailChimp
 *
 * @package   BetterOptin/Provider/MailChimp
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( 'mail-chimp' === wpbo_get_option( 'mailing_provider', '' ) ) {

	require( WPBO_PATH . 'includes/providers/mailchimp/functions-ajax.php' );
	require( WPBO_PATH . 'includes/providers/mailchimp/class-mailchimp.php' );
	require( WPBO_PATH . 'includes/providers/mailchimp/class-mailchimp-groups.php' );

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

		// Load provider files
		if ( is_admin() ) {
			require( WPBO_PATH . 'includes/providers/mailchimp/settings.php' );
			require( WPBO_PATH . 'includes/providers/mailchimp/functions-metabox.php' );
			require( WPBO_PATH . 'includes/providers/mailchimp/functions-admin.php' );
		}

		require( WPBO_PATH . 'includes/providers/mailchimp/class-provider-mailchimp.php' );

	}

	// Instantiate the MailChimp provider
	WPBO_MC();

}

add_filter( 'wpbo_mailing_providers', 'wpbo_provider_register_mailchimp' );
/**
 * Register the WordPress provider
 *
 * @since 2.0
 *
 * @param array $providers Existing providers
 *
 * @return array
 */
function wpbo_provider_register_mailchimp( $providers ) {

	$providers['mail-chimp'] = 'MailChimp'; // The dash in the name is just here so that the final class name resolves into MailChimp (with the capital letters)

	return $providers;
}

/**
 * Function that holds the entire MailChimp integration
 *
 * @return WPBO_MailChimp The one and only instance of WPBO_MailChimp
 * @since 2.0
 */
function WPBO_MC() {
	return WPBO_MailChimp::instance();
}