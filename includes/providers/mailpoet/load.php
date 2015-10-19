<?php
/**
 * BetterOptin Provider MailPoet
 *
 * @package   BetterOptin/Provider/MailPoet
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( 'mail-poet' === wpbo_get_option( 'mailing_provider', '' ) ) {

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

		// Load provider files
		if ( is_admin() ) {
			require( WPBO_PATH . 'includes/providers/mailpoet/settings.php' );
			require( WPBO_PATH . 'includes/providers/mailpoet/functions-metabox.php' );
			require( WPBO_PATH . 'includes/providers/mailpoet/functions-admin.php' );
		}

		require( WPBO_PATH . 'includes/providers/mailpoet/class-provider-mailpoet.php' );

	}

}

add_filter( 'wpbo_mailing_providers', 'wpbo_provider_register_mailpoet' );
/**
 * Register the WordPress provider
 *
 * @since 2.0
 *
 * @param array $providers Existing providers
 *
 * @return array
 */
function wpbo_provider_register_mailpoet( $providers ) {

	$providers['mail-poet'] = 'MailPoet'; // The dash in the name is just here so that the final class name resolves into MailPoet (with the capital letters)

	return $providers;
}