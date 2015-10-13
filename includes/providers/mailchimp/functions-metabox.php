<?php
/**
 * BetterOptin Provider MailChimp
 *
 * @package   BetterOptin/Provider/MailChimp
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'save_post', 'wpbo_mc_save_list' );
/**
 * Save the popup custom list.
 *
 * @since  1.0.0
 * @param  integer $post_id Post ID
 */
function wpbo_mc_save_list( $post_id ) {

	if ( ! isset( $_POST['wpbo_display'] ) || isset( $_POST['wpbo_display'] ) && ! wp_verify_nonce( $_POST['wpbo_display'], 'add_display' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['post_type'] ) || isset( $_POST['post_type'] ) && 'wpbo-popup' != $_POST['post_type'] ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$list = isset( $_POST['wpbo_mc_list'] ) ? $_POST['wpbo_mc_list'] : false;

	/* Save custom list ID */
	if ( false === $list ) {
		delete_post_meta( $post_id, 'wpbo_mc_list' );
	} else {
		update_post_meta( $post_id, 'wpbo_mc_list', $list );
	}

	/* Save the list groups */
	if ( isset( $_POST['wpbo_mc_list_groups'] ) ) {
		update_post_meta( $post_id, 'wpbo_mc_list_groups', $_POST['wpbo_mc_list_groups'] );
	} else {
		delete_post_meta( $post_id, 'wpbo_mc_list_groups' );
	}

}

add_action( 'add_meta_boxes', 'wpbo_mc_mailing_list_selector' );
/**
 * Add list selector metabox to popup edit screen.
 *
 * @since  1.0.0
 * @return null
 */
function wpbo_mc_mailing_list_selector () {
	add_meta_box( 'wpbo_mc_list', __( 'Mailing List <small>(Optional)</small>', 'wpbo_mc' ), 'wpbo_mc_display_mc_list', 'wpbo-popup', 'side', 'high' );
}

/**
 * Display content of list metabox.
 *
 * @since  1.0.0
 * @return null
 */
function wpbo_mc_display_mc_list() {
	require( WPBO_PATH . 'includes/providers/mailchimp/views/metabox-list.php' );
}