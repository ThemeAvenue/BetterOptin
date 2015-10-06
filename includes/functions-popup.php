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
 * @return mixed Popup ID if a popup is available, false otherwise
 */
function wpbo_is_popup_available() {

	global $post;

	/**
	 * First of all let's check if the user is an admin
	 * and if popups are hidden for admins.
	 */
	if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {

		$admin = (bool) wpbo_get_option( 'hide_admins', false );

		if ( true === $admin ) {
			return false;
		}

	}

	/* Try to avoid all the calculation with the use of sessions */
	if ( isset( $_SESSION['wpbo'][ $post->ID ] ) ) {

		$popup  = $_SESSION['wpbo'][ $post->ID ];
		$status = get_post_status( $popup );

		/* Make sure the popup hasn't been disabled while browsing */
		if ( 'publish' != $status ) {
			unset( $_SESSION['wpbo'][ $post->ID ] );

			return false;
		}
	} else {

		$relationships = get_option( 'wpbo_popup_relationships', array() );
		$popup         = false;
		$check         = false;
		$post_type     = $post->post_type;
		$query_args    = array(
			'post_type'              => 'wpbo-popup',
			'post_status'            => 'publish',
			'order'                  => 'DESC',
			'orderby'                => 'date',
			'posts_per_page'         => 1,
			'no_found_rows'          => false,
			'cache_results'          => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);

		/**
		 * There is a popup set for this specific page
		 */
		if ( is_array( $relationships ) && array_key_exists( $post->ID, $relationships ) && 'publish' == get_post_status( $relationships[ $post->ID ] ) ) {
			$popup = $relationships[ $post->ID ];
		} /**
		 * Let's check for more global popups
		 */
		else {

			/**
			 * Check if there is a popup to display for this type
			 */
			$query_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => '_wpbo_display_' . $post_type,
					'value'   => 'all',
					'type'    => 'CHAR',
					'compare' => '='
				),
				array(
					'key'     => '_wpbo_display_all',
					'value'   => 'no',
					'type'    => 'CHAR',
					'compare' => '='
				)
			);

			$check = new WP_Query( $query_args );

			if ( isset( $check->post ) ) {
				$popup = $check->post->ID;
			} /**
			 * Check if there is a popup to display everywhere
			 */
			else {

				$query_args['meta_query'] = array(
					array(
						'key'     => '_wpbo_display_all',
						'value'   => 'yes',
						'type'    => 'CHAR',
						'compare' => '='
					)
				);

				$check = new WP_Query( $query_args );

				if ( isset( $check->post ) ) {
					$popup = $check->post->ID;
				}

			}

		}

	}

	/* Store popup ID in session to avoid calculating again on page refresh */
	if ( ! isset( $_SESSION['wpbo'] ) ) {
		$_SESSION['wpbo'] = array();
	}

	$_SESSION['wpbo'][ $post->ID ] = $popup;

	/**
	 * Shall the popup be displayed for this visitor?
	 */
	if ( false !== $popup ) {

		if ( isset( $_COOKIE["wpbo_$popup"] ) && ! has_shortcode( $post->post_content, 'wpbo_popup' ) ) {
			return false;
		}

	}

	return $popup;

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
			<p><?php _e( 'This popup is still in draft mode and is <strong>not visible on the site</strong>. Don\'t forget to publish it when you\'re ready.', 'wpbo' ); ?></p>
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

	/* Set the cookie */

	return setcookie( 'wpbo_' . $popup_id, strtotime( date( 'Y-m-d H:i:s' ) ), time() + 60 * 60 * $cookie_lifetime, '/' );

}