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
 * @param  string $option  ID of the required option
 * @param  mixed  $default Default value to return if option doesn't exist
 *
 * @return mixed             Value
 */
function wpbo_get_option( $option, $default = false ) {

	$options = maybe_unserialize( get_option( 'wpbo_options' ) );
	$value   = isset( $options[ $option ] ) ? $options[ $option ] : $default;

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
		'title' => sprintf( __( 'Today\'s Conversion: %s', 'betteroptin' ), "$rate%" ),
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

	if ( false === $body ) {

		/* Prepare the HTTP request */
		$route    = 'http://www.kimonolabs.com/api/8qckyf28?';
		$api_key  = '34f710899fb2424aeb213c881ff10109';
		$endpoint = $route . http_build_query( array( 'apikey' => $api_key ) );
		$response = wp_remote_get( $endpoint );
		$body     = wp_remote_retrieve_body( $response );

		/* Get response from the request if it is valid */
		if ( ! is_wp_error( $response ) && '' != $body ) {

			/**
			 * Set the cache
			 */
			set_transient( 'wpbo_fonts', $body, 60 * 60 * 60 );
			update_option( 'wpbo_fonts', $body );

		} /* Otherwise get it from the options, even if deprecated */
		else {
			$body = get_option( 'wpbo_fonts', false );
		}

	}

	/* Decode the JSON */
	$body = json_decode( $body, true );

	if ( ! is_array( $body ) || ! isset( $body['results'] ) || ! isset( $body['results']['collection1'] ) ) {
		return false;
	}

	return $body['results']['collection1'];

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

/**
 * Get visitor IP address.
 *
 * @since  1.0.0
 * @return string IP address
 * @see    http://stackoverflow.com/a/15699314
 */
function wpbo_get_ip_address() {

	$env = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	);

	foreach ( $env as $key ) {

		if ( array_key_exists( $key, $_SERVER ) === true ) {

			foreach ( explode( ',', $_SERVER[ $key ] ) as $IPaddress ) {

				$IPaddress = trim( $IPaddress ); // Just to be safe

				if ( filter_var( $IPaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
					return $IPaddress;
				}
			}
		}
	}

	return 'unknown';

}

/**
 * Get a post ID
 *
 * If the post ID is given we just return it, otherwise we try to get it from the global $post
 *
 * @since 2.0
 *
 * @param bool|int $post_id Post ID
 *
 * @return bool|int
 */
function wpbo_get_post_id( $post_id = false ) {

	if ( $post_id ) {

		$post = get_post( $post_id );

		if ( is_null( $post ) ) {
			return false;
		}

	} else {

		global $post;

		if ( isset( $post ) && is_object( $post ) && is_a( $post, 'WP_Post' ) ) {
			$post_id = $post->ID;
		}

	}

	return $post_id;

}

/**
 * Get the standardized provider class name
 *
 * @since 2.0
 * @return string
 */
function wpbo_get_provider_class() {

	$provider = wpbo_get_option( 'mailing_provider', '' );

	if ( empty( $provider ) ) {
		return '';
	}

	$provider   = str_replace( ' ', '', ucwords( str_replace( array( '-', '_' ), ' ', sanitize_text_field( $provider ) ) ) );
	$class_name = 'WPBO_Provider_' . $provider;

	return $class_name;

}

/**
 * Check if the mailing provider is loaded and ready
 *
 * @since 2.0
 * @return bool
 */
function wpbo_is_provider_ready() {

	$class_name = wpbo_get_provider_class();

	if ( empty( $class_name ) ) {
		return false;
	}

	if ( ! class_exists( $class_name ) ) {
		return false;
	}

	if ( ! method_exists( $class_name, 'submit' ) ) {
		return false;
	}

	return true;

}

/**
 * Check if the current admin screen is a popup edit screen
 *
 * @since 2.0
 * @return bool
 */
function wpbo_is_popup_edit_screen() {

	global $pagenow;

	if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] || 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'wpbo-popup' == $_GET['post_type'] ) {
		return true;
	}

	return false;

}