<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   BetterOptin
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete the custom table
 */
global $wpdb;
$table = $wpdb->prefix . 'wpbo_analytics';
$wpdb->query( "DROP TABLE IF EXISTS $table" );

/**
 * Get options before deletion
 */
$options = maybe_unserialize( get_option( 'wpbo_options' ) );

/* Delete default options */
delete_option( 'wpbo_options' );
delete_option( 'wpbo_fonts' );

/* Delete transients */
delete_transient( 'wpbo_fonts' );
delete_transient( 'wpbo_documentation' );

/* Delete database version */
delete_option( 'wpbo_db_version' );

/**
 * Delete relationships
 */
delete_option( 'wpbo_popup_relationships' );

/**
 * Delete all popups and their metas.
 */
$args = array(
	'post_type'      => 'wpbo-popup',
	'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
	'posts_per_page' => -1,
);

$popups = new WP_Query( $args );

if( count( $popups->posts ) >= 1 ) {

	foreach( $popups->posts as $popup )
		wp_delete_post( $popup->ID, true );

}

/**
 * Deal with the users now
 */
if( isset( $options['wp_delete_users_uninstall'] ) && '1' == $options['wp_delete_users_uninstall'] ) {

	$reassign   = 1; // The user that all posts will fall back to, other wise they will be deleted
	$user_query = new WP_User_Query( array( 'meta_key' => 'wpbo_subscription', 'meta_value' => 'yes' ) );

	/**
	 * Delete the subscribers from DB
	 */
	if( !empty( $user_query->results ) ) {
		foreach( $user_query->results as $user ) {
			wp_delete_user( $user->ID, $reassign );
		}
	}

} else {

	$role       = 'subscriber'; // The new role to assign to subscribers
	$user_query = new WP_User_Query( array( 'meta_key' => 'wpbo_subscription', 'meta_value' => 'yes' ) );

	if( !empty( $user_query->results ) ) {

		foreach( $user_query->results as $user ) {

			/* Update user role */
			wp_update_user( array( 'ID' => $user->ID, 'role' => $role ) );

			/* Possibly delete the marker */
			if( isset( $options['wp_delete_users_marker'] ) && '1' == $options['wp_delete_users_marker'] )
				delete_user_meta( $user->ID, 'wpbo_subscription', 'yes' );

		}
	}

}

/**
 * Delete custom role
 */
remove_role( 'betteroptin' );