<?php
/**
 * BetterOptin Ajax
 *
 * @package   BetterOptin/Ajax
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'wp_ajax_wpbo_check_page_availability', 'wpbo_check_page_availability' );
/**
 * Check page availability.
 *
 * When the user adds a new page where the current popup should be triggered,
 * we check if the page is not already used by another popup.
 *
 * As we can't have 2 popups on one page, we only want to keep one popup per page. Hence,
 * we tell the user that the page he just selected
 *
 * @return string
 */
function wpbo_check_page_availability() {

	$current_id = isset( $_POST['current_id'] ) ? $_POST['current_id'] : false; // The current post ID (popup currently being edited)
	$selected   = isset( $_POST['selected_all'] ) ? explode( ',', $_POST['selected_all'] ) : array(); // All selected items
	$messages   = '0'; // Default string to return

	if ( is_array( $selected ) && count( $selected ) > 0 && false !== $current_id ) {

		$relationships = get_option( 'wpbo_popup_relationships', array() );

		foreach ( $selected as $post_id ) {

			if ( array_key_exists( $post_id, $relationships ) && $current_id != $relationships[ $post_id ] ) {

				/* Page details */
				$post  = get_post( $post_id );
				$title = $post->post_title;

				/* Popup details */
				$popup  = get_post( $relationships[ $post_id ] );
				$ptitle = $popup->post_title;
				$plink  = add_query_arg( array( 'post' => $popup->ID, 'action' => 'edit' ), admin_url( 'post.php' ) );

				$msg = '<p>';
				$msg .= sprintf( __( 'The page %s (#%s) is already used by the popup <a href="%s" target="_blank">%s</a> (#%s).', 'wpbo' ), "<strong><em>$title</em></strong>", $post_id, $plink, $ptitle, $popup->ID );
				$msg .= '</p>';

				/* Convert $messages into an array when there is at least one warning message to save */
				if ( ! is_array( $messages ) ) {
					$messages = array();
				}

				array_push( $messages, $msg );
			}

		}

	}

	/* Convert the possible messages to string before we return it */
	if ( is_array( $messages ) ) {

		/* Explain what's going to happen next */
		array_push( $messages, '<p><em>' . __( 'TIP: If you keep the conflicting page(s) selected, they will be removed from the other popup(s).', 'wpbo' ) . '</em></p>' );

		/* Convert to string */
		$messages = implode( '', $messages );
	}

	echo $messages;
	die();

}

add_action( 'wp_ajax_wpbo_get_graph_data', 'wpbo_get_graph_data' );
/**
 * Retrieve data to feed the graph.
 *
 * @since  1.0.0
 * @return string Records encoded in JSON
 */
function wpbo_get_graph_data() {

	$query     = array( 'data_type' => 'any', 'limit' => - 1 );
	$timeframe = unserialize( stripslashes( $_POST['wpbo_analytics_time'] ) );
	$popup     = isset( $_POST['wpbo_analytics_popup'] ) ? $_POST['wpbo_analytics_popup'] : 'all';
	$period    = isset( $_POST['wpbo_analytics_period'] ) ? $_POST['wpbo_analytics_period'] : 'today';

	/* Set the period */
	$query['period'] = $timeframe;

	/* Select the popup */
	if ( 'all' != $popup ) {
		$query['popup_id'] = intval( $popup );
	}

	/* Separate impressions and conversions */
	$query_i              = $query;
	$query_i['data_type'] = 'impression';

	$query_c              = $query;
	$query_c['data_type'] = 'conversion';

	/* Get the datas */
	$impressions = wpbo_get_datas( $query_i, 'OBJECT' );
	$conversions = wpbo_get_datas( $query_c, 'OBJECT' );

	/* Set the scale */
	$scale = date( 'Y-m-d' );

	switch ( $period ):

		case 'today':
			$scale       = 'Y-m-d H:00:00';
			$timeformat  = '%d/%b';
			$minticksize = array( 1, 'hour' );
			$min         = strtotime( date( 'Y-m-d 00:00:00' ) ) * 1000;
			$max         = strtotime( date( 'Y-m-d 23:59:59' ) ) * 1000;
			break;

		case 'this_week':
			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%a';
			$minticksize = array( 1, 'day' );
			$min         = strtotime( 'last monday' ) * 1000;
			$max         = strtotime( 'next sunday' ) * 1000;
			break;

		case 'last_week':
			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%a';
			$minticksize = array( 1, 'day' );
			$min         = strtotime( 'last monday -7 days' ) * 1000;
			$max         = strtotime( 'next sunday -7 days' ) * 1000;
			break;

		case 'this_month':
			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%a';
			$minticksize = array( 1, 'day' );
			$min         = strtotime( 'first day of this month' ) * 1000;
			$max         = strtotime( 'last day of this month' ) * 1000;
			break;

		case 'last_month':
			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%a';
			$minticksize = array( 1, 'day' );
			$min         = strtotime( 'first day of last month' ) * 1000;
			$max         = strtotime( 'last day of last month' ) * 1000;
			break;

		case 'this_quarter':

			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%b';
			$minticksize = array( 1, 'month' );
			$quarters    = array( 1, 4, 7, 10 );
			$month       = intval( date( 'm' ) );

			if ( in_array( $month, $quarters ) ) {
				$current = date( 'Y-m-d', time() );
			} else {

				/* Get first month of this quarter */
				while ( ! in_array( $month, $quarters ) ) {
					$month = $month - 1;
				}

				$current = date( 'Y' ) . '-' . $month . '-' . '01';

			}

			$current = strtotime( $current );
			$min     = strtotime( 'first day of this month', $current ) * 1000;
			$max     = strtotime( 'last day of this month', strtotime( '+2 months', $current ) ) * 1000;

			break;

		case 'last_quarter':

			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%b';
			$minticksize = array( 1, 'month' );
			$quarters    = array( 1, 4, 7, 10 );
			$month       = intval( date( 'm' ) ) - 3;
			$rewind      = false;

			if ( in_array( $month, $quarters ) ) {
				$current = date( 'Y-m-d', time() );
			} else {

				/* Get first month of this quarter */
				while ( ! in_array( $month, $quarters ) ) {

					$month = $month - 1;

					/* Rewind to last year after we passed January */
					if ( 0 === $month ) {
						$month = 12;
					}
				}

				$current = date( 'Y' ) . '-' . $month . '-' . '01';

			}

			/* Set the theorical current date */
			$current = false === $rewind ? strtotime( $current ) : strtotime( '-1 year', $current );
			$min     = strtotime( 'first day of this month', $current ) * 1000;
			$max     = strtotime( 'last day of this month', strtotime( '+2 months', $current ) ) * 1000;

			break;

		case 'this_year':
			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%b';
			$minticksize = array( 1, 'month' );
			$min         = strtotime( 'first day of January', time() ) * 1000;
			$max         = strtotime( 'last day of December', time() ) * 1000;
			break;

		case 'last_year':
			$scale       = 'Y-m-d 00:00:00';
			$timeformat  = '%b';
			$minticksize = array( 1, 'month' );
			$min         = strtotime( 'first day of January last year', time() ) * 1000;
			$max         = strtotime( 'last day of December last year', time() ) * 1000;
			break;

	endswitch;

	/* Propare global array */
	$datas = array(
		'impressionsData' => array(
			'label' => __( 'Impressions', 'wpbo' ),
			'id'    => 'impressions',
			'data'  => array()
		),
		'conversionsData' => array(
			'label' => __( 'Conversions', 'wpbo' ),
			'id'    => 'conversions',
			'data'  => array()
		),
		'scale'           => array(
			'minTickSize' => $minticksize,
			'timeformat'  => $timeformat
		),
		'min'             => $min,
		'max'             => $max
	);

	/* Get the count on the scaled timestamp */
	$imp_array = wpbo_array_merge_combine( $impressions, $scale );
	$con_array = wpbo_array_merge_combine( $conversions, $scale );

	/**
	 * Fill the blanks!
	 *
	 * Both impressions and conversions array need to have the same number of entries
	 * (same number of timestamps) for the graph to work properly.
	 *
	 * We alternatively merge the impressions and conversions array. The only added keys
	 * must have a value of 0.
	 */
	$tmp_arr_imp = array_flip( array_keys( $imp_array ) );
	$tmp_arr_con = array_flip( array_keys( $con_array ) );

	/* Set all counts to 0 */
	$tmp_arr_imp = array_map( array( 'Better_Optin_Admin', '_wpbo_return_zero' ), $tmp_arr_imp );
	$tmp_arr_con = array_map( array( 'Better_Optin_Admin', '_wpbo_return_zero' ), $tmp_arr_con );

	/* Add missing values in both impressions and conversions arrays */
	$imp_array = $imp_array + $tmp_arr_con;
	$con_array = $con_array + $tmp_arr_imp;

	/* Convert the arrays to a format that Float can read. */
	$imp_array = wpbo_float_format( $imp_array );
	$con_array = wpbo_float_format( $con_array );

	/* Add the hits to datas array */
	$datas['impressionsData']['data'] = $imp_array;
	$datas['conversionsData']['data'] = $con_array;

	/* Return results to script */
	print_r( json_encode( $datas ) );
	die();

}

add_action( 'wp_ajax_wpbo_tour_completed', 'wpbo_tour_completed' );
/**
 * Dismiss Customizer Tour
 *
 * Mark the tour as completed in the user profile
 * if the tour is actually completed or if the user
 * closes the popup window.
 *
 * @since  1.0.0
 * @return integer/boolean Row ID on successful update, false on failure
 */
function wpbo_tour_completed() {

	$user_id = get_current_user_id();

	/* Make sure we have a user */
	if ( 0 === $user_id ) {
		return false;
	}

	/* Get dismissed pointers */
	$dismissed = get_user_meta( $user_id, 'dismissed_wp_pointers', true );
	$pointers  = explode( ',', $dismissed );

	/* Add ours */
	if ( ! in_array( 'wpbo_tour', $pointers ) ) {
		array_push( $pointers, 'wpbo_tour' );
	}

	/* Update the dismissed pointers for this user */
	$update = update_user_meta( $user_id, 'dismissed_wp_pointers', implode( ',', $pointers ), $dismissed );

	echo $update;
	die;

}

add_action( 'wp_ajax_wpbo_get_doc', 'wpbo_get_documentation' );
/**
 * Get plugin documentation.
 *
 * Use the JSON API to get the doc from
 * http://support.themeavenue.net
 *
 * @since  1.0.0
 * @return string Documentation page content
 */
function wpbo_get_documentation() {

	$doc = get_transient( 'wpbo_documentation' );

	if ( false === $doc ) {

		$post_id  = 15151;
		$route    = 'http://support.themeavenue.net/wp-json/posts/';
		$response = wp_remote_get( $route . $post_id );

		if ( 200 === $response['response']['code'] ) {

			$doc = wp_remote_retrieve_body( $response );
			$doc = json_decode( $doc );
			$doc = $doc->content;
			set_transient( 'wpbo_documentation', $doc, 60 * 60 * 72 );

		}

	}

	if ( false === $doc ) {
		printf( __( 'Oops! We were unable to fetche the documentation from our support site. Please <a href="%s" target="_blank">click here to see the doc on our site</a>.', 'wpbo' ), esc_url( 'http://support.themeavenue.net/plugins/betteroptin/getting-started/' ) );
	} else {
		echo $doc;
	}

	die;

}

add_action( 'wp_ajax_wpbo_new_impression', 'wpbo_new_impression' );
add_action( 'wp_ajax_nopriv_wpbo_new_impression', 'wpbo_new_impression' );
/**
 * Record popup impression.
 *
 * @since  1.0.0
 *
 * @param int $popup_id ID of the popup to increment
 *
 * @return integer Total number of impressions
 */
function wpbo_new_impression( $popup_id = 0 ) {

	if ( 0 === $popup_id ) {
		if ( isset( $_POST['popup_id'] ) ) {
			$popup_id = filter_input( INPUT_POST, 'popup_id', FILTER_SANITIZE_NUMBER_INT );
		}
	}

	if ( ! WPBO_Popup::popup_exists( $popup_id ) ) {
		echo 0;
		die();
	}

	$popup = new WPBO_Popup( $popup_id );

	echo $popup->new_impression();
	die();

}