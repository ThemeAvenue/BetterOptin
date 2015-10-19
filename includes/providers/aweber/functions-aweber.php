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