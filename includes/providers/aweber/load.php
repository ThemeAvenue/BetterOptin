<?php
/**
 * BetterOptin Provider Aweber
 *
 * @package   BetterOptin/Provider/Aweber
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( 'aweber' === wpbo_get_option( 'mailing_provider', '' ) ) {

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

		// Load provider files
		if ( is_admin() ) {
			require( WPBO_PATH . 'includes/providers/aweber/settings.php' );
			require( WPBO_PATH . 'includes/providers/aweber/functions-metabox.php' );
			require( WPBO_PATH . 'includes/providers/aweber/functions-admin.php' );
			require( WPBO_PATH . 'includes/providers/aweber/functions-aweber.php' );
			require( WPBO_PATH . 'includes/providers/aweber/class-aweber.php' );
		}

		require( WPBO_PATH . 'includes/providers/aweber/class-provider-aweber.php' );

	}

}

add_filter( 'wpbo_mailing_providers', 'wpbo_provider_register_aweber' );
/**
 * Register the WordPress provider
 *
 * @since 2.0
 *
 * @param array $providers Existing providers
 *
 * @return array
 */
function wpbo_provider_register_aweber( $providers ) {

	$providers['aweber'] = 'Aweber';

	return $providers;
}