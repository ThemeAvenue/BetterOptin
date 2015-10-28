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

class WPBO_Provider_WordPress {

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
		$user = self::register( $data );

		/* Insertion is successful */
		if ( ! is_wp_error( $user ) ) {

			$result = true;

			/* Add a marker in user meta */
			add_user_meta( $user, 'wpbo_subscription', 'yes', true );

		}

		return $result;

	}

	/**
	 * Subscribe the visitor to a list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data Form data
	 *
	 * @return int|WP_Error Result
	 */
	protected static function register( $data ) {

		$password = wp_generate_password();
		$role     = isset( $options['wp_default_role'] ) ? $options['wp_default_role'] : 'betteroptin';
		$email    = $data['email'];

		/* Extra verification to avoid giving admin cap to subscribers. */
		if ( 'administrator' == $role ) {
			$role = 'subscriber';
		}

		$args = array(
			'user_email'   => $email,
			'user_login'   => $email,
			'first_name'   => isset( $data['first_name'] ) ? $data['first_name'] : $data['name'],
			'display_name' => isset( $data['first_name'] ) ? $data['first_name'] : $email,
			'user_pass'    => md5( $password ),
			'role'         => $role
		);

		$user = wp_insert_user( $args );

		return $user;

	}

}