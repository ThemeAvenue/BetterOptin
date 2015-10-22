<?php
/**
 * BetterOptin Provider AWeber
 *
 * @package   BetterOptin/Provider/AWeber
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPBO_Provider_Aweber {

	/**
	 * Trigger form submission.
	 *
	 * Add a last couple of checks, set the redirects and
	 * finally subscribe the visitor to the Aweber list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data Form post data
	 *
	 * @return bool
	 */
	public static function submit( $data ) {

		if ( ! wpbo_is_aweber_ready() ) {
			return false;
		}

		$aweber = new WPBO_Aweber();

		return $aweber->subscribe( $data );

	}

}