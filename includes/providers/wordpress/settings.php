<?php
/**
 * BetterOptin Provider WordPress
 *
 * @package   BetterOptin/Provider/WordPress/Settings
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpbo_plugin_settings', 'wpbo_wp_settings' );
/**
 * Addon settings.
 *
 * Add new settings to the plugin settings page.
 *
 * @since  1.0.0
 *
 * @param  array $settings Pre-existing settings
 *
 * @return array           Updated settings containing MailChimp options
 */
function wpbo_wp_settings( $settings ) {

	$roles = array();

	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/user.php' );
	}

	$editable_roles = get_editable_roles();

	if ( is_array( $editable_roles ) && count( $editable_roles ) >= 1 ) {

		foreach ( $editable_roles as $role_name => $role_info ) {
			$roles[ $role_name ] = $role_info['name'];
		}

	}

	$settings['wordpress'] = array(
		'name'    => __( 'WordPress', 'wpbo' ),
		'options' => array(
			array(
				'name'    => __( 'Default Role', 'wpbo' ),
				'id'      => 'wp_default_role',
				'type'    => 'select',
				'default' => 'betteroptin',
				'desc'    => __( 'Role attributed to all subscribers', 'wpbo' ),
				'options' => $roles
			),
			array(
				'name' => __( 'Uninstallation', 'wpbo' ),
				'type' => 'heading',
			),
			array(
				'type' => 'note',
				'desc' => sprintf( __( 'When the plugin is uninstalled (deleted from your site, not just deactivated), how shall we deal with the subscribers saved in your database? Please <a href="%s" target="_blank">read the documentation</a> for a full understanding of what you\'re doing.', 'wpbo' ), esc_url( 'http://themeavenue.net' ) )
			),
			array(
				'name'    => __( 'Delete Subscriber', 'wpbo' ),
				'id'      => 'wp_delete_users_uninstall',
				'type'    => 'checkbox',
				'default' => false,
				'desc'    => __( 'YES, DELETE subscribers from my database. Can NOT be undone.', 'wpbo' ),
			),
			array(
				'name'    => __( 'Delete Marker', 'wpbo' ),
				'id'      => 'wp_delete_users_marker',
				'type'    => 'checkbox',
				'default' => true,
				'desc'    => esc_html__( 'DELETE the markers. You will NOT be able TO differentiate the subscribers FROM the rest of your users.', 'betteroptin' ),
			),
		),
	);

	return $settings;

}