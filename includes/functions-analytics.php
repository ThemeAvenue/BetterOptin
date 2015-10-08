<?php
/**
 * Better Optin Analytics.
 *
 * BetterOptin Analytics API helping users interact
 * with the custom table where all popup related data
 * is recorded (impressions and conversions).
 *
 * @package   Better_Optin
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

/**
 * Create analytics table.
 *
 * @since  1.0.0
 * @return void
 */
function wpbo_create_table() {

	global $wpdb;

	/* Prepare DB structure if not already existing */
	if ( $wpdb->get_var( "show tables like 'wpbo_analytics_table'" ) != wpbo_analytics_table ) {

		$sql = "CREATE TABLE wpbo_analytics_table (
				data_id mediumint(9) NOT NULL AUTO_INCREMENT,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				data_type VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
				popup_id bigint(20) NOT NULL,
				user_agent VARCHAR(128) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
				referer VARCHAR(256) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
				ip_address VARCHAR(128) DEFAULT '0.0.0.0' COLLATE utf8_general_ci NOT NULL,
				UNIQUE KEY data_id (data_id)
				);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

}

/**
 * Insert new data.
 *
 * Add a new row of data in the analytics table.
 *
 * @since  1.0.0
 *
 * @param  array   $data     Details of the data to add
 * @param  boolean $wp_error Allow the function to return a WP_Error object
 *
 * @return mixed          Data ID on success or WP_Error on failure
 */
function wpbo_db_insert_data( $data = array(), $wp_error = true ) {

	global $wpdb;

	$table_name = wpbo_analytics_table;

	$defaults = array(
		'data_id'    => false,
		'time'       => '',
		'data_type'  => apply_filters( 'wpbo_default_data_type', 'impression' ),
		'popup_id'   => false,
		'user_agent' => '',
		'referer'    => '',
		'ip_address' => '0.0.0.0',
	);

	$data = array_merge( $defaults, $data );

	/* Sanitize all data values */
	$clean = wpbo_db_sanitize_data( $data, $wp_error );

	/* If sanitization failed return the error */
	if ( is_wp_error( $clean ) ) {
		if ( $wp_error ) {
			return $clean;
		} else {
			return false;
		}
	}

	/**
	 * Are we updating or creating?
	 */
	if ( isset( $clean['data_id'] ) && false !== $clean['data_id'] && is_int( $clean['data_id'] ) ) {
		$insert = wpbo_db_update_data( $clean, $wp_error ); // @todo test the update through insert_data()
		return $insert;
	} else {

		$insert = $wpdb->insert( $table_name, $clean, array( '%s', '%s', '%d', '%s', '%s', '%s' ) );

		if ( false === $insert ) {
			if ( $wp_error ) {
				return new WP_Error( 'insert_failed', __( 'Whoops, we could not insert the data in the database.' ) );
			} else {
				return false;
			}
		} else {
			return $insert;
		}

	}

}

/**
 * Remove a row.
 *
 * @since  1.0.0
 *
 * @param  boolean $data_id ID of the data to delete
 *
 * @return boolean          Returns true on success or false on failure
 */
function wpbo_db_remove_data( $data_id = false ) {

	if ( false === $data_id || ! is_int( $data_id ) ) {
		return false;
	}

	global $wpdb;

	$table_name = wpbo_analytics_table;

	$delete = $wpdb->delete( $table_name, array( 'data_id' => $data_id ), array( '%d' ) );

	return $delete;
}

/**
 * Update data row.
 *
 * @since  1.0.0
 *
 * @param  array   $data     Default array of data elements
 * @param  boolean $wp_error Is the function allowed to return a WP_Error object
 *
 * @return string
 */
function wpbo_db_update_data( $data = array(), $wp_error = true ) {

	global $wpdb;

	$defaults = array(
		'data_id' => '',
	);

	$data = array_merge( $defaults, $data );

	/**
	 * Check the popup ID (required).
	 */
	if ( empty( $data['data_id'] ) || ! is_int( $data['data_id'] ) ) {
		if ( $wp_error ) {
			return new WP_Error( 'no_data_id', __( 'Whoops, no data ID was provided.' ) );
		} else {
			return false;
		}
	}

	$table_name = wpbo_analytics_table;
	$ID         = (int) $data['data_id'];

	if ( false === $ID || ! is_int( $ID ) ) {
		if ( true === $wp_error ) {
			return new WP_Error( 'no_id', __( 'You did not pass the ID of the data to update.' ) );
		} else {
			return false;
		}
	}

	/* Previous data row */
	$prev = wpbo_db_get_data( $ID );

	$data = array_merge( $prev, $data );

	/* Sanitize all data values */
	$clean = wpbo_db_sanitize_data( $data, $wp_error );

	/* Do the update */
	$update = $wpdb->update( $table_name, $clean, array( 'data_id' => $ID ) );

	if ( false === $update ) {
		if ( true === $wp_error ) {
			return new WP_Error( 'update_error', __( 'An error occured while trying to update the data.' ) );
		} else {
			return false;
		}
	} else {
		return $update;
	}

}

/**
 * Sanitize data row.
 *
 * @param  array $data     Array of data elements to sanitize
 * @param bool   $wp_error Whether or not to return a WP_Error object in case of problem
 *
 * @return array        A clean array of data elements
 */
function wpbo_db_sanitize_data( $data = array(), $wp_error = true ) {

	$defaults = array( 'data_id'    => false,
	                   'time'       => false,
	                   'data_type'  => false,
	                   'popup_id'   => false,
	                   'user_agent' => false,
	                   'referer'    => false,
	                   'ip_address' => false
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

	/**
	 * Possibly filter the IP address
	 */
	if ( '1' == wpbo_get_option( 'anonymize_ip', false ) && false !== $data['ip_address'] && '0.0.0.0' !== $data['ip_address'] ) {

		$ip_breakdown       = explode( '.', $data['ip_address'] );
		$ip_breakdown[3]    = '*';
		$data['ip_address'] = implode( '.', $ip_breakdown );

	}

	/**
	 * Recreate the sanitized array of data elements
	 */
	$clean = array(
		'time'       => $data['time'],
		'data_type'  => $data['data_type'],
		'popup_id'   => $data['popup_id'],
		'user_agent' => $data['user_agent'],
		'referer'    => $data['referer'],
		'ip_address' => $data['ip_address'],
	);

	return $clean;

}

/**
 * Get data.
 *
 * Get the entire row for a specific data.
 *
 * @since  1.0.0
 *
 * @param  integer $data_id ID of the data to retrieve
 * @param  string  $output  Type of data the user want to be returned
 *
 * @return mixed            Data of type $output
 */
function wpbo_db_get_data( $data_id = null, $output = 'ARRAY_A' ) {

	global $wpdb;

	if ( is_null( $data_id ) || ! is_int( $data_id ) ) {
		return false;
	}

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM %s WHERE data_id = %s", wpbo_analytics_table, $data_id ), $output );

	return $row;

}

/**
 * Get a set of datas.
 *
 * Retrieve a set of datas based on the user
 * criterias. This function can return one or
 * more row(s) of data depending on the arguments;
 *
 * @param  array $args   Arguments
 * @param string $output Desired output format
 *
 * @return mixed
 */
function wpbo_db_get_datas( $args, $output = 'OBJECT' ) {

	global $wpdb;

	$table_name = wpbo_analytics_table;
	$query      = array();

	$defaults = array(
		'data_type'  => 'any',
		'user_agent' => '',
		'referer'    => '',
		'popup_id'   => '',
		'date'       => array(),
		'limit'      => 5,
		'period'     => ''
	);

	$args = array_merge( $defaults, $args );

	/**
	 * Handle the limit
	 */
	if ( - 1 === $args['limit'] ) {
		$args['limit'] = 1000;
	}

	/**
	 * Handle data type first
	 */
	if ( is_array( $args['data_type'] ) ) {

		$relation = ( isset( $args['data_type']['relation'] ) && in_array( $args['data_type']['relation'], array(
				'IN',
				'NOT IN'
			) ) ) ? $args['data_type']['relation'] : 'IN';
		$types    = array();

		foreach ( $args['data_type']['type'] as $type ) {
			array_push( $types, "'$type'" );
		}

		$types = implode( ',', $types );
		array_push( $query, "data_type $relation ($types)" );

	} elseif ( '' != $args['data_type'] ) {
		if ( 'any' == $args['data_type'] ) {
			array_push( $query, "data_type IN ( 'impression', 'conversion' )" );
		} else {
			array_push( $query, "data_type = '{$args['data_type']}'" );
		}
	}

	/**
	 * Handle the popup_id
	 *
	 * @todo test
	 */
	if ( is_array( $args['popup_id'] ) ) {

		$relation = ( isset( $args['popup_id']['relation'] ) && in_array( $args['popup_id']['relation'], array(
				'IN',
				'NOT IN'
			) ) ) ? $args['popup_id']['relation'] : 'IN';
		$popups   = array();

		foreach ( $args['popup_id']['ids'] as $popup ) {
			array_push( $popups, "$popup" );
		}

		$popups = implode( ',', $popups );
		array_push( $query, "popup_id $relation ($popups)" );

	} elseif ( '' != $args['popup_id'] ) {
		array_push( $query, "popup_id = {$args['popup_id']}" );
	}

	/**
	 * Handle the period.
	 */
	if ( '' != $args['period'] ) {

		if ( is_array( $args['period'] ) ) {

			$start = isset( $args['period']['from'] ) ? date( "Y-m-d", $args['period']['from'] ) : date( "Y-m-d", time() );
			$end   = isset( $args['period']['to'] ) ? date( "Y-m-d", $args['period']['to'] ) : date( "Y-m-d", time() );

			$start = ( true === wpbo_check_date( $start ) ) ? $start . ' 00:00:00' : date( "Y-m-d", time() ) . ' 00:00:00';
			$end   = ( true === wpbo_check_date( $end ) ) ? $end . ' 23:59:59' : date( "Y-m-d", time() ) . ' 23:59:59';

			array_push( $query, "time BETWEEN '$start' AND '$end'" );

		} else {

			/* Get datetime format */
			$date  = date( "Y-m-d", $args['period'] );
			$start = "$date 00:00:00";
			$end   = "$date 23:59:59";

			array_push( $query, "time BETWEEN '$start' AND '$end'" );

		}

	}

	/* Merge the query */
	$limit = (int) $args['limit'];
	$query = implode( ' AND ', $query );
	$rows  = $wpdb->get_results( "SELECT * FROM $table_name WHERE $query LIMIT $limit", $output );

	return $rows;

}

/*----------------------------------------------------------------------------*
 * Helper Functions
 *----------------------------------------------------------------------------*/

/**
 * Today's Conversion Rate.
 *
 * Get today's conversion rate using the stats class.
 *
 * @since  1.2.2
 *
 * @param  integer $decimals      Number of decimal to return for the conversion rate
 * @param  string  $dec_point     Separator for the decimal point
 * @param  string  $thousands_sep Separator for the thousands
 *
 * @return integer                Conversion rate for the day
 */
function wpbo_today_conversion( $decimals = 2, $dec_point = '.', $thousands_sep = ',' ) {

	/* Prepare the query. */
	$query = array( 'data_type' => 'any', 'limit' => - 1, 'period' => strtotime( 'today' ) );

	/* Get the datas. */
	$datas = wpbo_db_get_datas( $query, 'OBJECT' );

	/* Set the count vars. */
	$impressions = 0;
	$conversions = 0;

	/* Check the number of conversions. */
	foreach ( $datas as $data ) {

		/* Increment conversions */
		if ( 'conversion' == $data->data_type ) {
			++ $conversions;
		}

		/* Increment impressions */
		if ( 'impression' == $data->data_type ) {
			++ $impressions;
		}

	}

	/* Get the conversion rate. */
	$rate = ( 0 === $conversions || 0 === $impressions ) ? 0 : ( $conversions * 100 ) / $impressions;

	return number_format( $rate, $decimals, $dec_point, $thousands_sep );

}

/**
 * Fills an entire period with specific time points
 *
 * Takes an array of data (either impressions or conversions) and populates all the hits
 * on a specific period of time. If the data array doesn't contain data for one or more
 * time points, then the empty points are set to 0.
 *
 * @param array  $data        Impressions or conversions data
 * @param int    $min         Timestamp at the beginning for the timeframe
 * @param int    $max         Timestamp at the end of the timeframe
 * @param string $increment   Increment delay (eg. 1 hour, 1 day...)
 * @param string $date_format Date format for the given timeframe
 *
 * @return array
 */
function wpbo_fill_hits_period( $data, $min, $max, $increment, $date_format ) {

	$timeframe = array();

	for ( $date = $min; $date <= $max; $date = strtotime( date( $date_format, $date ) . " + $increment" ) ) {
		$timeframe[ $date ] = array_key_exists( $date, $data ) ? $data[ $date ] : 0;
	}

	return wpbo_float_format( $timeframe );

}

/**
 * Format our timeframe array for jQuery Flot
 *
 * Flot uses a very specific format where the data must be an array of arrays.
 * Also, all timestamps must be in milliseconds. This functions does all the formatting.
 *
 * @param array $array Data in a predefined timeframe
 *
 * @return array array Data formatted for Flot
 */
function wpbo_float_format( $array ) {

	$new = array();
	
	foreach ( $array as $timestamp => $hits ) {
		array_push( $new, array( $timestamp * 1000, $hits ) ); // Timestamp must be in miliseconds
	}

	return $new;

}