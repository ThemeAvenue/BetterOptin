<?php
/**
 * BetterOptin Misc Functions
 *
 * @package   BetterOptin/Misc
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
 * Get plugin options.
 *
 * Get a plugin option or popup post meta.
 *
 * @since  1.0.0
 *
 * @param  string  $option  ID of the required option
 * @param  mixed   $default Default value to return if option doesn't exist
 * @param  integer $post_id Post ID (for retrieving post metas)
 *
 * @return mixed             Value
 */
function wpbo_get_option( $option, $default = false, $post_id = null ) {

	/**
	 * Post Meta
	 */
	if ( ! is_null( $post_id ) ) {

		$settings = get_post_meta( $post_id, '_wpbo_settings', true );

		if ( is_array( $settings ) && isset( $settings[ $option ] ) ) {

			$value = $settings[ $option ];

		} else {
			$value = $default;
		}

	} /**
	 * General options
	 */
	else {

		$options = maybe_unserialize( get_option( 'wpbo_options' ) );
		$value   = isset( $options[ $option ] ) ? $options[ $option ] : $default;

	}

	return apply_filters( 'wpbo_get_option' . $option, $value );

}

add_action( 'admin_bar_menu', 'wpas_admin_bar_conversion_rate', 999 );
/**
 * Today's Conversion Rate.
 *
 * Adds today's conversion rate in the admin bar with
 * a direct link to the stats page.
 *
 * @since  1.2.2
 * @param  object $wp_admin_bar The global admin bar object
 * @return void
 */
function wpas_admin_bar_conversion_rate( $wp_admin_bar ) {

	/* Get today's conversion rate. */
	$rate = wpbo_today_conversion();

	/* Set the node parameters. */
	$args = array(
		'id'    => 'wpbo_today_conversion',
		'title' => sprintf( __( 'Today\'s Conversion: %s', 'wpbo' ), "$rate%" ),
		'href'  => admin_url( 'edit.php?popup=all&period=today&post_type=wpbo-popup&page=wpbo-analytics' ),
		'meta'  => array( 'class' => 'wpbo-today-conversion' )
	);

	/* Add the new node. */
	$wp_admin_bar->add_node( $args );

}

/**
 * Check if the current page belongs to the plugin
 *
 * @return bool
 */
function wpbo_is_plugin_page() {

	global $post;

	$slugs = array(
		'wpbo-customizer',
		'wpbo-relationships',
		'wpbo-about',
		'wpbo-addons',
		'wpbo-analytics',
		'edit.php?post_type=wpbo-popup-settings'
	);

	if ( isset( $post ) && is_object( $post ) && isset( $post->post_type ) && 'wpbo-popup' == $post->post_type ) {

		return true;

	} elseif ( isset( $_GET['page'] ) && in_array( $_GET['page'], $slugs ) ) {

		return true;

	} else {

		return false;

	}

}

/**
 * Get font stack.
 *
 * @since  1.0.0
 * @return array List of all available fonts
 */
function wpbo_get_font_stack() {

	/* Try to get body from the transient */
	$body = get_transient( 'wpbo_fonts' );

	if( false === $body ) {

		/* Prepare the HTTP request */
		$route    = 'http://www.kimonolabs.com/api/8qckyf28?';
		$api_key  = '34f710899fb2424aeb213c881ff10109';
		$endpoint = $route . http_build_query( array( 'apikey' => $api_key ) );
		$response = wp_remote_get( $endpoint );
		$body     = wp_remote_retrieve_body( $response );

		/* Get response from the request if it is valid */
		if( !is_wp_error( $response ) && '' != $body ) {

			/**
			 * Set the cache
			 */
			set_transient( 'wpbo_fonts', $body, 60*60*60 );
			update_option( 'wpbo_fonts', $body );

		}

		/* Otherwise get it from the options, even if deprecated */
		else {
			$body = get_option( 'wpbo_fonts', false );
		}

	}

	/* Decode the JSON */
	$body = json_decode( $body, TRUE );

	if( !is_array( $body ) )
		return false;

	/* Return fonts only */
	return $body['results']['collection1'];

}

/**
 * Return zero
 *
 * The function just returns 0 and is used for array_map.
 * This function is required for PHP < 5.3 as anonymous functions
 * are not yet supported.
 *
 * @since  1.0.1
 * @see    Better_Optin_Admin::get_graph_data()
 * @param  mixed   $item Array item to reset
 * @return integer       Zero
 */
function _wpbo_return_zero( $item ) {
	return 0;
}

function wpbo_float_format( $array ) {

	$new = array();

	/* Reorder the array */
	ksort( $array );

	/** Transform the array in a readable format for Float */
	foreach ( $array as $key => $value ) {
		array_push( $new, array( $key * 1000, $value ) ); // Timestamp must be in miliseconds
	}

	return $new;

}

/**
 * Prepare the hist array.
 *
 * The function takes an array of datas and then,
 * based on the time scale, gets the number of hits
 * in a specific timeframe (eg. number of hits per hour).
 *
 * @since  1.0.0
 *
 * @param  array  $array  An array of data
 * @param  string $format A date format (as used in date())
 *
 * @return array          An array sorted by time and hits in a format compatible with Float for the graph
 */
function wpbo_array_merge_combine( $array, $format ) {

	$parsed = array();
	$new    = array();

	/* Count the number of hits per timeframe */
	foreach ( $array as $object ) {

		$date = strtotime( date( $format, strtotime( $object->time ) ) );

		if ( ! in_array( $date, $parsed ) ) {
			array_push( $parsed, $date );
			$new[ $date ] = 1;
		} else {
			++ $new[ $date ];
		}

	}

	return $new;

}

/**
 * Check Gregorian date.
 *
 * @param  string $time Date to check
 *
 * @return boolean       True if date is valid
 * @see /wp-includes/post.php
 */
function wpbo_check_date( $time ) {

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