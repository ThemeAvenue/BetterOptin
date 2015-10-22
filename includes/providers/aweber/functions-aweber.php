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

/**
 * Get Aweber lists
 *
 * Helper function to get Aweber subscribers lists
 *
 * @since 2.0
 * @return array
 */
function wpbo_aw_get_lists() {

	$aweber = new WPBO_Aweber();

	if ( $aweber->is_error() ) {
		return array();
	}

	return $aweber->get_lists();

}

/**
 * Check if Aweber settings are correct.
 *
 * @since  1.0.0
 * @return boolean True if Aweber integration is ready to work
 */
function wpbo_is_aweber_ready() {

	$tokens = maybe_unserialize( get_option( 'wpbo_aweber_tokens' ) );

	if ( ! is_array( $tokens ) ) {
		return false;
	}

	$access_token  = isset( $tokens[0] ) ? trim( $tokens[0] ) : '';
	$access_secret = isset( $tokens[1] ) ? trim( $tokens[1] ) : '';

	if ( empty( $access_token ) || empty( $access_secret ) ) {
		return false;
	}

	$auth_code = trim( wpbo_get_option( 'aw_auth_code', '' ) );

	if ( empty( $auth_code ) ) {
		return false;
	}

	$list_id = wpbo_get_option( 'aw_list_id', '' );

	if ( empty( $list_id ) ) {
		return false;
	}

	return true;

}