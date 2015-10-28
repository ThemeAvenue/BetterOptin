<?php
/**
 * BetterOptin Misc Admin Functions
 *
 * @package   BetterOptin/Failsafe
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
 * Create failsafe table.
 *
 * @since  2.0
 * @return void
 */
function wpbo_failsafe_create_table() {

	global $wpdb;

	$table = wpbo_failsafe_table;

	/* Prepare DB structure if not already existing */
	if ( $wpdb->get_var( "show tables like '$table'" ) != $table ) {

		$sql = "CREATE TABLE $table (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				conversion_id mediumint(9) NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				first_name VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
				last_name VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
				email VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
				provider VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
				status VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
				UNIQUE KEY ID (ID)
				);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

}

/**
 * Insert new subscriber.
 *
 * Add a new row of data in the failsafe table.
 *
 * @since  2.0
 *
 * @param  array   $data     Details of the subscriber
 * @param  boolean $wp_error Allow the function to return a WP_Error object
 *
 * @return mixed          Subscriber ID on success or WP_Error on failure
 */
function wpbo_failsafe_add_subscriber( $data = array(), $wp_error = true ) {

	global $wpdb;

	$table_name = wpbo_failsafe_table;

	$defaults = array(
		'ID'            => false,
		'conversion_id' => 0,
		'time'          => '',
		'first_name'    => '',
		'last_name'     => '',
		'email'         => '',
		'provider'      => '',
		'status'        => '',
	);

	$data = array_merge( $defaults, $data );

	if ( empty( $data['time'] ) || '0000-00-00 00:00:00' == $data['time'] ) {
		$data['time'] = current_time( 'mysql' );
	}

	/**
	 * Validate the date
	 */
	$valid_date = wpbo_check_date( $data['time'] );

	if ( ! $valid_date ) {
		if ( $wp_error ) {
			return new WP_Error( 'invalid_date', __( 'Whoops, the provided date is invalid.' ) );
		} else {
			return false;
		}
	}

	// Make sure we have a provider
	if ( empty( $data['provider'] ) ) {
		$provider   = str_replace( ' ', '', ucwords( str_replace( array( '-', '_' ), ' ', sanitize_text_field( wpbo_get_option( 'mailing_provider', '' ) ) ) ) );
		$data['provider'] = $provider;
	}

	// Set the status as failed by default
	if ( empty( $data['status'] ) ) {
		$data['status'] = 'failed';
	}

	/* Sanitize all data values */
	$data = array_map( 'sanitize_text_field', $data );

	$insert = $wpdb->insert( $table_name, $data, array( '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' ) );

	if ( false === $insert ) {
		if ( $wp_error ) {
			return new WP_Error( 'insert_failed', __( 'Whoops, we could not insert the data in the database.' ) );
		} else {
			return false;
		}
	} else {
		return $wpdb->insert_id;
	}

}