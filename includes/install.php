<?php
/**
 * BetterOptin Installer
 *
 * @package   BetterOptin/Install
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook( WPBO_PLUGIN_FILE, 'wpbo_activate' );
add_action( 'wpmu_new_blog', 'wpbo_activate_new_site' );

/**
 * Fired when the plugin is activated.
 *
 * @since    1.0.0
 *
 * @param    boolean    $network_wide    True if WPMU superadmin uses
 *                                       "Network Activate" action, false if
 *                                       WPMU is disabled or plugin is
 *                                       activated on an individual blog.
 */
function wpbo_activate( $network_wide ) {

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {

		if ( $network_wide  ) {

			// Get all blog ids
			$blog_ids = wpbo_get_blog_ids();

			foreach ( $blog_ids as $blog_id ) {

				switch_to_blog( $blog_id );
				wpbo_single_activate();
			}

			restore_current_blog();

		} else {
			wpbo_single_activate();
		}

	} else {
		wpbo_single_activate();
	}

}

/**
 * Get all blog ids of blogs in the current network that are:
 * - not archived
 * - not spam
 * - not deleted
 *
 * @since    1.0.0
 *
 * @return   array|false    The blog ids, false if no matches.
 */
function wpbo_get_blog_ids() {

	global $wpdb;

	// get an array of blog ids
	$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

	return $wpdb->get_col( $sql );

}

/**
 * Fired for each blog when the plugin is activated.
 *
 * @since    1.0.0
 */
function wpbo_single_activate() {

	/* Add new role */
	$subscriber = get_role( 'subscriber' );
	add_role( 'betteroptin', 'BetterOptin', $subscriber->capabilities );

	/* Create database table */
	wpbo_create_table();

	/* Write database version */
	update_option( 'wpbo_db_version', WPBO_Analytics::$db_version );

	/**
	 * Add an option in DB to know when the plugin has just been activated.
	 *
	 * @link http://stackoverflow.com/questions/7738953/is-there-a-way-to-determine-if-a-wordpress-plugin-is-just-installed/13927297#13927297
	 */
	add_option( 'wpbo_just_activated', true );

}

/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since    1.0.0
 *
 * @param    int    $blog_id    ID of the new blog.
 */
function wpbo_activate_new_site( $blog_id ) {

	if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
		return;
	}

	switch_to_blog( $blog_id );
	wpbo_single_activate();
	restore_current_blog();

}

/**
 * Redirect the user to the about page if the plugin was just installed
 *
 * @return void
 */
function wpbo_maybe_redirect_about() {

	if ( ! is_admin() ) {
		return;
	}

	$activated = get_option( 'wpbo_just_activated', false );

	/**
	 * First thing we check if the plugin has just been activated.
	 * If so, we take the user to the about page and delete the
	 * option we used for the check.
	 */
	if ( $activated ) {

		/* Delete the option */
		delete_option( 'wpbo_just_activated' );

		/* Redirect to about page */
		wp_redirect( add_query_arg( array(
			'post_type' => 'wpbo-popup',
			'page'      => 'wpbo-about'
		), admin_url( 'edit.php' ) ) );

		/* Don't do anything else */
		exit;

	}

}