<?php
/**
 * BetterOptin Post Type
 *
 * @package   BetterOptin/Post Type
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'init', 'wpbo_register_post_type' );
/**
 * Register the popups post type
 *
 * @since 1.0.0
 */
function wpbo_register_post_type() {

	/* Set menu icon */
	$icon = 'dashicons-share-alt2';

	$labels = array(
		'name'               => _x( 'Popups', 'post type general name', 'betteroptin' ),
		'singular_name'      => _x( 'Popup', 'post type singular name', 'betteroptin' ),
		'menu_name'          => _x( 'Popups', 'admin menu', 'betteroptin' ),
		'name_admin_bar'     => _x( 'Popup', 'add new on admin bar', 'betteroptin' ),
		'add_new'            => _x( 'Add New', 'book', 'betteroptin' ),
		'add_new_item'       => __( 'Add New Popup', 'betteroptin' ),
		'new_item'           => __( 'New Popup', 'betteroptin' ),
		'edit_item'          => __( 'Edit Popup', 'betteroptin' ),
		'view_item'          => __( 'View Popup', 'betteroptin' ),
		'all_items'          => __( 'All Popups', 'betteroptin' ),
		'search_items'       => __( 'Search Popups', 'betteroptin' ),
		'parent_item_colon'  => __( 'Parent Popups:', 'betteroptin' ),
		'not_found'          => __( 'No popup found.', 'betteroptin' ),
		'not_found_in_trash' => __( 'No popups found in Trash.', 'betteroptin' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => false,
		'show_in_admin_bar' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'popups' ),
		'capability_type'   => 'post',
		'has_archive'       => false,
		'hierarchical'      => false,
		'menu_position'     => null,
		'menu_icon'         => $icon,
		'supports'          => array( 'title' )
	);

	register_post_type( 'wpbo-popup', $args );

}


add_filter( 'post_updated_messages', 'popup_updated_messages' );
/**
 * Popup update messages.
 *
 * See /wp-admin/edit-form-advanced.php
 *
 * @since  1.2.2
 *
 * @param  array $messages Existing post update messages.
 *
 * @return array           Amended post update messages with new CPT update messages.
 */
function popup_updated_messages( $messages ) {

	$post = get_post();

	$messages['wpbo-popup'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Popup updated.', 'betteroptin' ),
		2  => __( 'Custom field updated.', 'betteroptin' ),
		3  => __( 'Custom field deleted.', 'betteroptin' ),
		4  => __( 'Popup updated.', 'betteroptin' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Popup restored to revision from %s', 'betteroptin' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Popup published.', 'betteroptin' ),
		7  => __( 'Popup saved.', 'betteroptin' ),
		8  => __( 'Popup submitted.', 'betteroptin' ),
		9  => sprintf(
			__( 'Popup scheduled for: <strong>%1$s</strong>.', 'betteroptin' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'betteroptin' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Popup draft updated.', 'betteroptin' )
	);

	return $messages;
}