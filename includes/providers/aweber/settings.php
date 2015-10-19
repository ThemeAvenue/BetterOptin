<?php
/**
 * BetterOptin Provider Aweber
 *
 * @package   BetterOptin/Provider/Aweber/Settings
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpbo_plugin_settings', 'wpbo_aw_settings' );
/**
 * Addon settings.
 *
 * Add new settings to the plugin settings page.
 *
 * @since  1.0.0
 * @param  array $settings Pre-existing settings
 * @return array           Updated settings containing MailChimp options
 */
function wpbo_aw_settings( $settings ) {

	require( WPBO_PATH . 'includes/providers/aweber/class-titan-aweber.php' );

	/* Avoid calling the Aweber API when not on its settings page */
	if ( ! isset( $_GET['tab'] ) || 'aweber' != $_GET['tab'] ) {
		$lists = array();
	} else {
		$lists = wpbo_aw_get_lists();
	}

	if( !is_array( $lists ) || empty( $lists ) ) {

		$list_id = array(
			'name'    => __( 'List ID', 'wpbo' ),
			'id'      => 'aw_list_id',
			'type'    => 'text',
			'default' => '',
			'desc'    => __( 'Input your authorization code and save if you want to see a dropdown of all your lists. If you already entered your authorization code and the dropdown did not appear pleas refresh the page.', 'wpbo' )
		);

	} else {

		if( is_array( $lists ) ) {

			$opts[''] = __( 'Please select...', 'wpbo' );

			foreach( $lists as $key => $list ) {
				$opts[$list['id']] = $list['name'];
			}

			$list_id = array(
				'name'    => __( 'List ID', 'wpbo' ),
				'id'      => 'aw_list_id',
				'type'    => 'select',
				'options' => $opts,
				'default' => ''
			);

		} else {

			$list_id = array(
				'name'    => __( 'List ID', 'wpbo' ),
				'id'      => 'aw_list_id',
				'type'    => 'text',
				'default' => '',
				'desc'    => __( 'Input your API key and save if you want to see a dropdown of all your lists.', 'wpbo' ),
				'default' => ''
			);

		}

	}

	$settings['aweber'] = array(
		'name'    => __( 'AWeber', 'wpbo' ),
		'options' => array(
			array(
				'name'    => __( 'Authorization Code', 'wpbo' ),
				'id'      => 'aw_auth_code',
				'type'    => 'aweber',
				'desc'    => sprintf( __( 'AWeber requires you to authenticate this plugin with your account for security reasons. If you are not sure how to do it, please <a href="#" target="_blank">watch our video tutorial</a>.', '' ), esc_url( 'http://youtu.be/c448gBbWlkg' ) ),
				'default' => ''
			),
			$list_id
		)
	);

	return $settings;

}