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
class WPBO_Analytics {

	/**
	 * Analytics database suffix.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	public static $db_suffix = 'wpbo_analytics';

	/**
	 * Analytics database table name.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	public $table_name = null;

	/**
	 * Database version.
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	public static $db_version = '1.0';

	public function __construct() {

		global $wpdb;

		/* Define the table name */
		$this->table_name = $wpdb->prefix . self::$db_suffix;

	} 
	
	/**
	 * Create analytics table.
	 *
	 * @since  1.0.0
	 * @return [type] [description]
	 */
	public static function create_table() {

		global $wpdb;

		/* Define table name */
		$table_name = $wpdb->prefix . self::$db_suffix;

		/* Prepare DB structure if not already existing */
		if( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

			$sql = "CREATE TABLE $table_name (
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
	 * @param  array   $data  Details of the data to add
	 * @param  boolean $error Allow the function to return a WP_Error object
	 * @return mixed          Data ID on success or WP_Error on failure
	 */
	public function insert_data( $data = array(), $wp_error = true ) {

		global $wpdb;

		$table_name = $this->table_name;

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
		$clean = $this->sanitize_data( $data, $wp_error );

		/* If sanitization failed return the error */
		if( is_wp_error( $clean ) ) {
			if ( $wp_error )
				return $clean;
			else
				return false;
		}

		/**
		 * Are we updating or creating?
		 */
		if( isset( $clean['data_id'] ) && false !== $clean['data_id'] && is_int( $clean['data_id'] ) ) {
			$insert = $this->update_data( $clean, $wp_error ); // @todo test the update through insert_data()
			return $insert;
		} else {

			$insert = $wpdb->insert( $table_name, $clean, array( '%s', '%s', '%d', '%s', '%s', '%s' ) );

			if( false === $insert ) {
				if ( $wp_error )
					return new WP_Error( 'insert_failed', __( 'Whoops, we could not insert the data in the database.' ) );
				else
					return false;
			} else {
				return $insert;
			}

		}

	}

	/**
	 * Remove a row.
	 *
	 * @since  1.0.0
	 * @param  boolean $data_id ID of the data to delete
	 * @return boolean          Returns true on success or false on failure
	 */
	public function remove_data( $data_id = false ) {

		if( false === $data_id || !is_int( $data_id ) )
			return false;

		global $wpdb;

		$table_name = $this->table_name;

		$delete = $wpdb->delete( $table_name, array( 'data_id' => $data_id ), array( '%d' ) );

		return $delete;
	}

	/**
	 * Update data row.
	 *
	 * @since  1.0.0
	 * @param  array   $data     Default array of data elements
	 * @param  boolean $wp_error Is the function allowed to return a WP_Error object
	 * @return [type]            [description]
	 */
	public function update_data( $data = array(), $wp_error = true ) {

		global $wpdb;

		$defaults = array();

		extract( array_merge( $defaults, $data ) );

		/**
		 * Check the popup ID (required).
		 */
		if( false === $data_id || !is_int( $data_id ) ) {
			if ( $wp_error )
				return new WP_Error( 'no_data_id', __( 'Whoops, no data ID was provided.' ) );
			else
				return false;
		}

		$table_name = $this->table_name;
		$ID         = intval( $data['data_id'] );

		if( false === $ID || !is_int( $ID ) ) {
			if( true === $wp_error )
				return new WP_Error( 'no_id', __( 'You did not pass the ID of the data to update.' ) );
			else
				return false;
		}

		/* Previous data row */
		$prev = $this->get_data( $ID );

		$data = array_merge( $prev, $data );

		/* Sanitize all data values */
		$clean = $this->sanitize_data( $data, $wp_error );

		/* Do the update */
		$update = $wpdb->update( $table_name, $clean, array( 'data_id' => $ID ) );

		if( false === $update ) {
			if( true === $wp_error )
				return new WP_Error( 'update_error', __( 'An error occured while trying to update the data.' ) );
			else
				return false;
		} else {
			return $update;
		}

	}

	/**
	 * Get data.
	 *
	 * Get the entire row for a specific data.
	 *
	 * @since  1.0.0
	 * @param  integer $data_id ID of the data to retrieve
	 * @param  string  $output  Type of data the user want to be returned
	 * @return mixed            Data of type $output
	 */
	public function get_data( $data_id = null, $output = 'ARRAY_A' ) {

		global $wpdb;

		$table_name = $this->table_name;

		if( is_null( $data_id ) || !is_int( $data_id ) )
			return false;

		$row = $wpdb->get_row( "SELECT * FROM $table_name WHERE data_id = $data_id", $output );

		return $row;

	}

	/**
	 * Check Gregorian date.
	 * 
	 * @param  string $time  Date to check
	 * @return boolean       True if date is valid
	 * @see /wp-includes/post.php
	 */
	public function check_date( $time ) {

		/**
		 * Validate the date
		 * 
		 * @see /wp-includes/post.php
		 */
		$mm         = substr( $time, 5, 2 );
		$jj         = substr( $time, 8, 2 );
		$aa         = substr( $time, 0, 4 );
		$valid_date = wp_checkdate( $mm, $jj, $aa, $time );

		return $valid_date;

	}

	/**
	 * Sanitize data row.
	 * 
	 * @param  array  $data Array of data elements to sanitize
	 * @return array        A clean array of data elements
	 */
	public function sanitize_data( $data = array(), $wp_error = true ) {

		$defaults = array( 'data_id' => false, 'time' => false, 'data_type' => false, 'popup_id' => false, 'user_agent' => false, 'referer' => false, 'ip_address' => false );

		extract( array_merge( $defaults, $data ) );

		if( empty( $time ) || '0000-00-00 00:00:00' == $time )
			$time = current_time( 'mysql' );

		/**
		 * Validate the date
		 */
		$valid_date = $this->check_date( $time );

		if ( !$valid_date ) {
			if ( $wp_error )
				return new WP_Error( 'invalid_date', __( 'Whoops, the provided date is invalid.' ) );
			else
				return false;
		}

		/**
		 * Possibly filter the IP address
		 */
		if( '1' == wpbo_get_option( 'anonymize_ip', false ) && false !== $ip_address && '0.0.0.0' !== $ip_address ) {

			$ip_breakdown    = explode( '.', $ip_address );
			$ip_breakdown[3] = '*';
			$ip_address      = implode( '.', $ip_breakdown );

		}

		/**
		 * Recreate the sanitized array of data elements
		 */
		$clean = array(
			'time'       => $time,
			'data_type'  => $data_type,
			'popup_id'   => $popup_id,
			'user_agent' => $user_agent,
			'referer'    => $referer,
			'ip_address' => $ip_address,
		);

		return $clean;

	}

	/**
	 * Get a set of datas.
	 *
	 * Retrieve a set of datas based on the user
	 * criterias. This function can return one or
	 * more row(s) of data depending on the arguments;
	 * 
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function get_datas( $args, $output = 'OBJECT' ) {

		global $wpdb;

		$table_name = $this->table_name;
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

		extract( array_merge( $defaults, $args ) );

		/**
		 * Handle the limit
		 */
		if( -1 === $limit )
			$limit = 1000;

		/**
		 * Handle data type first
		 */
		if( is_array( $data_type ) ) {

			$relation = ( isset( $data_type['relation'] ) && in_array( $data_type['relation'], array( 'IN', 'NOT IN' ) ) ) ? $data_type['relation'] : 'IN';
			$types    = array();

			foreach( $data_type['type'] as $type ) {
				array_push( $types, "'$type'" );
			}

			$types = implode( ',', $types );
			array_push( $query, "data_type $relation ($types)" );

		} elseif( '' != $data_type ) {
			if( 'any' == $data_type ) {
				array_push( $query, "data_type IN ( 'impression', 'conversion' )" );
			} else {
				array_push( $query, "data_type = '$data_type'" );
			}
		}

		/**
		 * Handle the popup_id
		 *
		 * @todo test
		 */
		if( is_array( $popup_id ) ) {

			$relation = ( isset( $popup_id['relation'] ) && in_array( $popup_id['relation'], array( 'IN', 'NOT IN' ) ) ) ? $popup_id['relation'] : 'IN';
			$popups    = array();

			foreach( $popup_id['ids'] as $popup ) {
				array_push( $popups, "$popup" );
			}

			$popups = implode( ',', $popups );
			array_push( $query, "popup_id $relation ($popups)" );

		} elseif( '' != $popup_id ) {
			array_push( $query, "popup_id = $popup_id" );
		}

		/**
		 * Handle the period.
		 */
		if( '' != $period ) {

			if( is_array( $period ) ) {

				$start = isset( $period['from'] ) ? date( "Y-m-d", $period['from'] ) : date( "Y-m-d", time() );
				$end   = isset( $period['to'] ) ? date( "Y-m-d", $period['to'] ) : date( "Y-m-d", time() );

				$start = ( true === $this->check_date( $start ) ) ? $start . ' 00:00:00' : date( "Y-m-d", time() ) . ' 00:00:00';
				$end   = ( true === $this->check_date( $end ) ) ? $end . ' 23:59:59' : date( "Y-m-d", time() ) . ' 23:59:59';

				array_push( $query, "time BETWEEN '$start' AND '$end'" );

			} else {

				/* Get datetime format */
				$date  = date( "Y-m-d", $period );
				$start = "$date 00:00:00";
				$end   = "$date 23:59:59";

				array_push( $query, "time BETWEEN '$start' AND '$end'" );

			}

		}

		/* Merge the query */
		$query = implode( ' AND ', $query );

		$rows = $wpdb->get_results( "SELECT * FROM $table_name WHERE $query LIMIT $limit", $output );

		return $rows;

	}

}

/**
 * Instance of the Analytics class.
 *
 * @since  1.0.0
 * @var    object
 */
$wpbo_analytics = new WPBO_Analytics;

/*----------------------------------------------------------------------------*
 * Helper Functions
 *----------------------------------------------------------------------------*/

/**
 * Get a set of datas.
 *
 * Get one or multiple rows from the database
 * based on the $args parameters.
 *
 * @since  1.0.0
 * @see    WPBO_Analytics::get_datas()
 * @param  array  $args   Parameters used in the SQL query
 * @param  string $output Format to use when returning the results
 * @return mixed          Results with eht $output format
 */
function wpbo_get_datas( $args = array(), $output = 'ARRAY_A' ) {

	global $wpbo_analytics;

	$datas = $wpbo_analytics->get_datas( $args, $output );

	return $datas;

}

/**
 * Insert data in the database.
 *
 * Insert a new row of data in the _wpbo_analytics table.
 *
 * @since  1.0.0
 * @see    WPBO_Analytics::insert_data()
 * @param  array   $data     Data to add
 * @param  boolean $wp_error Allow method to return a WP_Error object
 * @return mixed             ID of the data on success or $wp_error on failure
 */
function wpbo_insert_data( $data = array(), $wp_error = true ) {

	global $wpbo_analytics;

	$insert = $wpbo_analytics->insert_data( $data, $wp_error = true );

	return $insert;

}

/**
 * Today's Conversion Rate.
 *
 * Get today's conversion rate using the stats class.
 *
 * @since  1.2.2
 * @param  integer $decimals      Number of decimal to return for the conversion rate
 * @param  integer $dec_point     Separator for the decimal point
 * @param  integer $thousnads_sep Separator for the thousands
 * @return integer                Conversion rate for the day
 */
function wpbo_today_conversion( $decimals = 2, $dec_point = '.', $thousands_sep = ',' ) {

	/* Prepare the query. */
	$query = array( 'data_type' => 'any', 'limit' => -1, 'period' => strtotime( 'today') );

	/* Get the datas. */
	$datas = wpbo_get_datas( $query, 'OBJECT' );

	/* Set the count vars. */
	$total       = count( $datas );
	$conversions = 0;

	/* Check the number of conversions. */
	foreach ( $datas as $data ) {

		if ( 'conversion' == $data->data_type ) {
			++$conversions;
		}

	}

	/* Get the converison rate. */
	$rate = ( $conversions * 100 ) / $total;

	return number_format( $rate, $decimals, $dec_point, $thousands_sep );

}