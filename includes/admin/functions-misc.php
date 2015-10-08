<?php
/**
 * BetterOptin Misc Admin Functions
 *
 * @package   BetterOptin/Misc Admin
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'admin_init', 'wpbo_disable_autosave' );
/**
 * Disable auto-save for this post type.
 *
 * Autosave is causing issues when user clicks the "Customize" button
 * directly in the template selection metabox.
 *
 * Moreover, in our case, auto-save will only affect the popup title
 * which is not critical.
 *
 * @since  1.0.0
 * @return null
 */
function wpbo_disable_autosave() {

	if ( isset( $_GET['post_type'] ) && 'wpbo-popup' == $_GET['post_type'] || isset( $_GET['post'] ) && 'wpbo-popup' == get_post_type( intval( $_GET['post'] ) ) ) {
		wp_deregister_script( 'autosave' );
	}

}

add_filter( 'plugin_action_links_' . WPBO_BASENAME, 'wpbo_add_action_links' );
/**
 * Add settings action link to the plugins page.
 *
 * @since    1.0.0
 */
function wpbo_add_action_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'edit.php?post_type=wpbo-popup-settings' ), admin_url( 'edit.php' ) ) . '">' . __( 'Settings', 'betteroptin' ) . '</a>'
		),
		$links
	);

}


add_filter( 'post_row_actions', 'wpbo_row_action', 10, 2 );
/**
 * Add link to action row.
 *
 * Add a direct link to customizer in the post
 * action row.
 *
 * @param  array $actions List of available actions
 * @param  opject $post   Post currently parsed
 * @return array          List of actions containing the customizer link
 */
function wpbo_row_action( $actions, $post ) {

	/* Only add the link for our post type */
	if( 'wpbo-popup' != $post->post_type )
		return $actions;

	/* Only add the link if a template is set */
	if( '' != get_post_meta( $post->ID, 'wpbo_template', true ) ) {

		$actions['wpbo_customize'] = sprintf( '<a href="%s" class="google_link">%s</a>', add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-customizer', 'wpbo_popup' => $post->ID ), admin_url( 'edit.php' ) ), __( 'Customize', 'wpbo' ) );

	}

	$actions['wpbo_analytics'] = sprintf( '<a href="%s" class="google_link">%s</a>', add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-analytics', 'popup' => $post->ID, 'period' => 'today' ), admin_url( 'edit.php' ) ), __( 'Stats', 'wpbo' ) );

	return $actions;
}

add_filter( 'gettext', 'wpbo_change_publish_button_label', 10, 2 );
/**
 * Change publish button label.
 *
 * Change Publish button label to Save on new popups
 * as the button action is now save as draft instead of publish.
 *
 * @since  1.0.0
 * @see    Better_Optin_Admin::save_before_publish()
 *
 * @param  string $translation Current text string
 * @param  string $text        String translation
 *
 * @return string              New label
 */
function wpbo_change_publish_button_label( $translation, $text ) {

	global $typenow;

	if ( 'wpbo-popup' == $typenow ) {
		if ( ( ! isset( $_GET['post'] ) || isset( $_GET['post'] ) && '' == get_post_meta( intval( $_GET['post'] ), '_wpbo_template_display', true ) ) && 'Publish' == $text ) {
			$translation = __( 'Save', 'wpbo' );
		}
	}

	return apply_filters( 'wpbo_publish_button_label', $translation, $text );

}

add_filter( 'admin_footer_text', 'wpbo_copyright', 10, 2 );
/**
 * Add copyright.
 *
 * Add a copyright text at the bottom of all plugin pages.
 *
 * @since  1.0.0
 *
 * @param  string $text WordPress footer text
 *
 * @return string
 */
function wpbo_copyright( $text ) {

	if ( ! wpbo_is_plugin_page() ) {
		return $text;
	}

	return sprintf( __( '<a href="%s" target="_blank">BetterOptin</a> version %s developed by <a href="%s" target="_blank">ThemeAvenue</a>.', 'wpbo' ), esc_url( 'http://betteropt.in' ), WPBO_VERSION, esc_url( 'http://themeavenue.net?utm_source=plugin&utm_medium=footer_link&utm_campaign=BetterOptin' ) );

}

add_action( 'plugins_loaded', 'wpbo_remote_notices', 11 );
/**
 * Enable Remote Dashboard Notifications
 *
 * @since 1.0.0
 */
function wpbo_remote_notices() {

	/* Load RDN class */
	if ( ! class_exists( 'TAV_Remote_Notification_Client' ) ) {
		require_once( WPBO_PATH . 'includes/admin/class-remote-notification.php' );
	}

	/* Instantiate the class */
	new TAV_Remote_Notification_Client( 5, '278afa858b56d071', 'http://api.themeavenue.net?post_type=notification' );

}

/**
 * Check if Tour was Completed
 *
 * Check the user dismissed pointers and verify if
 * the tour was already completed (or dismissed).
 *
 * @since  1.0.0
 * @return boolean True if completed, false otherwise
 */
function wpbo_is_tour_completed() {

	$user_id = get_current_user_id();

	/* Make sure we have a user */
	if ( 0 === $user_id ) {
		return false;
	}

	/* Get dismissed pointers */
	$dismissed = get_user_meta( $user_id, 'dismissed_wp_pointers', true );
	$pointers  = explode( ',', $dismissed );

	if ( in_array( 'wpbo_tour', $pointers ) ) {
		return true;
	} else {
		return false;
	}

}