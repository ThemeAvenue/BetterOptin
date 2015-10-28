<?php
/**
 * BetterOptin Popup
 *
 * @package   BetterOptin/Popup
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
 * Check if a popup is available for the current page.
 *
 * The function checks, in this order, if a popup is available for:
 * - The current post
 * - The current post type
 * - The whole site
 *
 * @since  1.0.0
 *
 * @param int $post_id Optional post ID to check for popup availability
 *
 * @return bool|int Popup ID if a popup is available, false otherwise
 */
function wpbo_page_has_popup( $post_id = 0 ) {

	/**
	 * Checks in order:
	 *
	 * 1. Front-page
	 * 2. Homepage
	 * 3. Archives
	 *  3.1 Search
	 *  3.2 Post type
	 * 4. 404
	 * 5. Singular
	 */

	$post_id = wpbo_get_post_id( $post_id );

	/**
	 * First of all let's check if the user is an admin
	 * and if popups are hidden for admins.
	 */
	if ( is_user_logged_in() && current_user_can( 'administrator' ) && true === (bool) wpbo_get_option( 'hide_admins', false ) ) {
		return false;
	}

	// Try to get the popup from the cache
	$popup_id = wpbo_get_cached_popup( $post_id );

	if ( false === $popup_id ) {
		$popup_id = wpbo_get_popup( $post_id );
	}

	// Cache popup ID to avoid calculating again on page refresh */
	if ( false !== $popup_id ) {
		wpbo_cache_popup( $popup_id, $post_id );
	}

	return $popup_id;

}

/**
 * Get the active popup for a given post, if any
 *
 * @since 2.0
 *
 * @param int $post_id Post ID
 *
 * @return bool|int
 */
function wpbo_get_popup( $post_id = 0 ) {

	$post_id  = wpbo_get_post_id( $post_id );
	$popup_id = false;

	if ( ! $post_id ) {
		return false;
	}

	// Get the global popups / posts relationships
	$relationships = get_option( 'wpbo_popup_relationships', array() );

	// Get current post type
	$post_type = get_post_type( $post_id );

	/**
	 * If this post ID is found in the relationships then it means it has a popup attached.
	 */
	if ( is_array( $relationships ) && array_key_exists( $post_id, $relationships ) && 'publish' == get_post_status( $relationships[ $post_id ] ) ) {
		return (int) $relationships[ $post_id ];
	} /**
	 * Let's check for more global popups
	 */
	else {

		// Check if there is a popup for the current post type first of all
		$popup_id = wpbo_get_post_type_popup( $post_type );

		if ( false === $popup_id ) {
			$popup_id = wpbo_get_sitewide_popup();
		}

	}

	return $popup_id;

}

/**
 * Get the popup for a given post type ONLY
 *
 * This means that we're looking for popups that are specifically set for the post type
 * and NOT set globally.
 *
 * @since 2.0
 *
 * @param string $post_type
 *
 * @return bool
 */
function wpbo_get_post_type_popup( $post_type = '' ) {

	if ( empty( $post_type ) ) {
		return false;
	}

	if ( ! post_type_exists( $post_type ) ) {
		return false;
	}

	$query_args = array(
		'post_type'              => 'wpbo-popup',
		'post_status'            => 'publish',
		'order'                  => 'DESC',
		'orderby'                => 'date',
		'posts_per_page'         => 1,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'meta_query'             => array(
			'relation' => 'AND',
			array(
				'key'     => '_wpbo_display_' . $post_type,
				'value'   => 'all',
				'type'    => 'CHAR',
				'compare' => '='
			),
			array(
				'key'     => '_wpbo_display_all', // We want to exclude popups set globally
				'value'   => 'no',
				'type'    => 'CHAR',
				'compare' => '='
			)
		),
	);

	$query = new WP_Query( $query_args );

	if ( isset( $query->post ) ) {
		return $query->post->ID;
	}

	return false;

}

/**
 * Get site-wide popup
 *
 * @since 2.0
 * @return bool|int
 */
function wpbo_get_sitewide_popup() {

	$query_args = array(
		'post_type'              => 'wpbo-popup',
		'post_status'            => 'publish',
		'order'                  => 'DESC',
		'orderby'                => 'date',
		'posts_per_page'         => 1,
		'no_found_rows'          => true,
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'meta_query'             => array(
			array(
				'key'     => '_wpbo_display_all',
				'value'   => 'yes',
				'type'    => 'CHAR',
				'compare' => '='
			)
		),
	);

	$query = new WP_Query( $query_args );

	if ( isset( $query->post ) ) {
		return $query->post->ID;
	}

	return false;

}

/**
 * Get the cached popup for a specific page
 *
 * @since 2.0
 *
 * @param int $post_id Post ID
 *
 * @return bool|int
 */
function wpbo_get_cached_popup( $post_id = 0 ) {

	$post_id  = wpbo_get_post_id( $post_id );
	$popup_id = false;

	if ( ! $post_id ) {
		return false;
	}

	if ( isset( $_SESSION['wpbo'][ $post_id ] ) ) {

		$popup_id = (int) $_SESSION['wpbo'][ $post_id ];

		if ( ! WPBO_Popup::popup_exists( $popup_id ) ) {
			return false;
		}

		$status = get_post_status( $popup_id );

		/* Make sure the popup hasn't been disabled while browsing */
		if ( 'publish' != $status ) {

			unset( $_SESSION['wpbo'][ $post_id ] );

			return false;

		}

	}

	return $popup_id;

}

/**
 * Cache the ID of an available popup for a specific post
 *
 * @since 2.0
 *
 * @param int $popup_id ID of the popup ot cache
 * @param int $post_id  ID of the post where it's available
 *
 * @return void
 */
function wpbo_cache_popup( $popup_id, $post_id ) {
	$_SESSION['wpbo'][ $post_id ] = $popup_id;
}

add_action( 'before_delete_post', 'wpbo_delete_post_relationships' );
/**
 * Delete Post Relationships.
 *
 * Delete all relationships when a popup
 * is deleted by the user.
 *
 * @since  1.0.0
 *
 * @param  bool $post_id ID of the post to be deleted
 */
function wpbo_delete_post_relationships( $post_id ) {

	$relationships = $new = get_option( 'wpbo_popup_relationships', array() );
	$post_types    = get_post_types( array( 'public' => true ) );
	$except        = array( 'attachment', 'wpbo-popup' );

	/* Iterate through all allowed post types */
	foreach ( $post_types as $key => $pt ) {

		if ( in_array( $key, $except ) ) {
			continue;
		}

		/* Get relationships for this post type */
		$display = get_post_meta( $post_id, '_wpbo_display_' . $pt, true );

		if ( is_array( $display ) ) {

			foreach ( $display as $key => $post ) {

				/* Remove deprecated relations */
				if ( isset( $new[ $post ] ) ) {
					unset( $new[ $post ] );
				}

			}

		}

	}

	/* Update relationships if needed only */
	if ( serialize( $relationships ) !== serialize( $new ) ) {
		update_option( 'wpbo_popup_relationships', $new );
	}

}

add_action( 'admin_notices', 'wpbo_unpublished_popup_notice' );
/**
 * Unpublished notice.
 *
 * Warn the user when the popup is not published yet.
 *
 * @since  1.0.0
 */
function wpbo_unpublished_popup_notice() {

	global $typenow, $post;

	if( 'wpbo-popup' == $typenow && isset( $_GET['post'] ) && isset( $post ) && 'draft' == $post->post_status ): ?>

		<div class="error">
			<p><?php _e( 'This popup is still in draft mode and is <strong>not visible on the site</strong>. Don\'t forget to publish it when you\'re ready.', 'betteroptin' ); ?></p>
		</div>

	<?php endif;

}

add_filter( 'wp_insert_post_data', 'wpbo_save_before_publish', 99, 2 );
/**
 * Prevent from publishing new popups.
 *
 * When a new popup is created, it is saved as a draft
 * in order to avoid publishing a non customized popup.
 *
 * @since  1.0.0
 *
 * @param  array $data    Sanitized post data
 * @param  array $postarr Raw post data
 *
 * @return array          Updated $data
 */
function wpbo_save_before_publish( $data, $postarr ) {

	if ( 'wpbo-popup' == $postarr['post_type'] && isset( $postarr['original_post_status'] ) ) {

		if ( 'auto-draft' == $postarr['original_post_status'] ) {
			$data['post_status'] = 'draft';
		}

		if ( 'draft' == $postarr['original_post_status'] ) {

			$customized = get_post_meta( $postarr['ID'], '_wpbo_template_display', true );

			if ( '' == $customized ) {
				$data['post_status'] = 'draft';
			}

		}

	}

	return apply_filters( 'wpbo_publish_button_action', $data, $postarr );

}

/**
 * Dismiss a popup.
 *
 * Set a cookie to prevent a specific popup from showing up
 * on the site. This function was made for other plugins to
 * have an easy way to hide a popup if needed.
 *
 * @since  1.0.1
 *
 * @param  integer $popup_id        ID of the popup to dismiss
 * @param  integer $cookie_lifetime Lifetime of the cookie in days
 *
 * @return boolean                  Result of the cookie insertion
 */
function wpbo_dismiss_popup( $popup_id = 0, $cookie_lifetime = 30 ) {

	if ( 0 === $popup_id ) {
		return false;
	}

	$cookie_lifetime = apply_filters( 'wpbo_cookie_lifetime', $cookie_lifetime, $popup_id );

	/* Set the cookie */

	return setcookie( 'wpbo_' . $popup_id, strtotime( date( 'Y-m-d H:i:s' ) ), time() + 60 * 60 * $cookie_lifetime, '/' );

}

/**
 * Check if a popup has been dismissed by the visitor
 *
 * @since 2.0
 *
 * @param int $popup_id Popup ID
 *
 * @return bool
 */
function wpbo_is_popup_dismissed( $popup_id ) {

	if ( ! WPBO_Popup::popup_exists( $popup_id ) ) {
		return false;
	}

	if ( isset( $_COOKIE["wpbo_$popup_id"] ) ) {
		return true;
	}

	return false;

}

add_action( 'wp_footer', 'wpbo_maybe_load_popup' );
/**
 * Check if the current page has a popup and if the current visitor hasn't dismissed it already
 *
 * @since 2.0
 * @return void
 */
function wpbo_maybe_load_popup() {

	// If the provider is not ready we don't display the popup at all
	if ( ! wpbo_is_provider_ready() ) {
		return;
	}

	$popup_id = wpbo_page_has_popup();
	$post_id  = wpbo_get_post_id();

	if ( false === $popup_id ) {
		return;
	}

	if ( wpbo_is_popup_dismissed( $popup_id ) ) {

		if ( false === $post_id ) {
			return;
		}

		$post = get_post( $post_id );

		if ( is_null( $post ) ) {
			return;
		}

		/**
		 * Because the popups can be triggered by a button (generated by a shortcode), we want to load the popup markup
		 * EVEN IF it has been dismissed when the trigger button is present in the post content.
		 */
		if ( ! has_shortcode( $post->post_content, 'wpbo_popup' ) ) {
			return;
		}

	}

	$popup = new WPBO_Popup( $popup_id );

	$popup->popup();

}

add_action( 'plugins_loaded', 'wpbo_maybe_submit' );
/**
 * Maybe submit a popup
 *
 * If a popup has been submitted we process the submission
 * and then redirect the user based on the submission result.
 *
 * @since 2.0
 * @return bool|void
 */
function wpbo_maybe_submit() {

	if ( ! isset( $_POST['wpbo_nonce'] ) || ! wp_verify_nonce( $_POST['wpbo_nonce'], 'subscribe' ) ) {
		return;
	}

	if ( ! isset( $_POST['wpbo_email'] ) || ! isset( $_POST['wpbo_id'] ) ) {
		return;
	}

	$popup_id = filter_input( INPUT_POST, 'wpbo_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! WPBO_Popup::popup_exists( $popup_id ) ) {
		return;
	}

	$popup = new WPBO_Popup( $popup_id );
	$popup->submit();

}

/**
 * Get all allowed form fields
 *
 * @since 2.0
 * @return array
 */
function wpbo_get_form_fields() {

	$fields = apply_filters( 'wpbo_form_fields', array(
		'email'      => array(
			'form_name'         => 'wpbo_email',
			'sanitize_callback' => 'sanitize_email'
		),
		'first_name' => array(
			'form_name'         => 'wpbo_first_name',
			'sanitize_callback' => 'sanitize_text_field'
		),
		'last_name'  => array(
			'form_name'         => 'wpbo_first_name',
			'sanitize_callback' => 'sanitize_text_field'
		),
		'name'       => array(
			'form_name'         => 'wpbo_name',
			'sanitize_callback' => 'sanitize_text_field'
		),
		'wpbo_id'    => array(
			'form_name'         => 'wpbo_id',
			'sanitize_callback' => 'intval'
		),
		'post_id'    => array(
			'form_name'         => 'post_id',
			'sanitize_callback' => 'intval'
		),
	) );

	return $fields;

}