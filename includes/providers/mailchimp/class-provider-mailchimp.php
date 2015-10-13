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

class WPBO_Provider_MailChimp {

	/**
	 * Trigger form submission.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data Sanitized form data
	 *
	 * @return null
	 */
	public static function submit( $data ) {

		$result = false;

		/* Subscribe the new user */
		$user = WPBO_MC()->submit( $data );

		/* Insertion is successful */
		if ( ! is_wp_error( $user ) ) {
			$result = true;

		}

		return $result;

	}

}