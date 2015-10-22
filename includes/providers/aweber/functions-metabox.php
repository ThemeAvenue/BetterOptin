<?php
/**
 * BetterOptin Provider Aweber
 *
 * @package   BetterOptin/Provider/Aweber/Settingd
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'add_meta_boxes', 'wpbo_aw_mailing_list_selector' );
/**
 * Add list selector metabox to popup edit screen.
 *
 * @since  1.0.0
 * @return null
 */
function wpbo_aw_mailing_list_selector () {
	add_meta_box( 'wpbo_aw_list', __( 'Mailing List <small>(Optional)</small>', 'wpbo' ), 'wpbo_aw_display_aweber_list', 'wpbo-popup', 'side', 'high' );
}

/**
 * Display content of list metabox.
 *
 * @since  1.0.0
 * @return null
 */
function wpbo_aw_display_aweber_list() {
	include_once( WPBO_PATH . 'includes/providers/aweber/metabox.php' );
}

add_action( 'save_post', 'wpbo_aw_save_list' );
/**
 * Save the popup custom list.
 *
 * @since  1.0.0
 * @param  integer $post_id Post ID
 */
function wpbo_aw_save_list( $post_id ) {

	if( !isset( $_POST['wpbo_display'] ) || isset( $_POST['wpbo_display'] ) && !wp_verify_nonce( $_POST['wpbo_display'], 'add_display' ) )
		return;

	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if( !isset( $_POST['post_type'] ) || isset( $_POST['post_type'] ) && 'wpbo-popup' != $_POST['post_type'] )
		return;

	if( !current_user_can( 'edit_post', $post_id ) )
		return;

	$list = isset( $_POST['wpbo_aw_list'] ) ? $_POST['wpbo_aw_list'] : '';

	update_post_meta( $post_id, 'wpbo_aw_list', $list );

}