<?php
/**
 * BetterOptin Provider MailChimp
 *
 * @package   BetterOptin/Provider/MailChimp/Settings
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpbo_plugin_settings', 'wpbo_mc_settings' );
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
function wpbo_mc_settings( $settings ) {
	
	$lists = WPBO_MC()->get_lists();

	if ( false === $lists ) {

		$list_id = array(
			'name'    => __( 'List ID', 'betteroptin' ),
			'id'      => 'mc_list_id',
			'type'    => 'text',
			'default' => '',
			'desc'    => sprintf( __( 'Input your API key and save if you want to see a dropdown of all your lists. If you don\'t know how to get your API key please <a href="%s" target="_blank">read this documentation</a>.', 'betteroptin' ), esc_url( 'http://eepurl.com/im9k' ) )
		);

	} else {

		if ( isset( $lists['data'] ) && is_array( $lists['data'] ) ) {

			$opts[''] = __( 'Please select...', 'betteroptin' );

			foreach ( $lists['data'] as $key => $list ) {
				$opts[ $list['id'] ] = $list['name'];
			}

			$list_id = array(
				'name'    => __( 'List ID', 'betteroptin' ),
				'id'      => 'mc_list_id',
				'type'    => 'select',
				'options' => $opts,
				'default' => ''
			);

		} else {

			$list_id = array(
				'name'    => __( 'List ID', 'betteroptin' ),
				'id'      => 'mc_list_id',
				'type'    => 'text',
				'default' => '',
				'desc'    => __( 'Input your API key and save if you want to see a dropdown of all your lists.', 'betteroptin' ),
				'default' => ''
			);

		}

	}

	$settings['mailchimp'] = array(
		'name'    => __( 'MailChimp', 'betteroptin' ),
		'options' => array(
			array(
				'type' => 'note',
				'desc' => __( 'First of all, please input your MailChimp API key. Then hit the blue &laquo;Save Changes&raquo; button at the bottom of the screen. On page refresh, the <code>List ID</code> field will turn into a select dropdown showing all your MailChimp lists. If something goes wrong and you don\'t see the select dropdown, you can always input the list ID manually.', 'wpbo' )
			),
			array(
				'name'    => __( 'API Key', 'betteroptin' ),
				'id'      => 'mc_api_key',
				'type'    => 'text',
				'default' => '',
				'desc'    => sprintf( __( 'If you don\'t know how to get your API key please <a href="%s" target="_blank">read this documentation</a>.', 'wpmc' ), esc_url( 'http://eepurl.com/im9k' ) )
			),
			$list_id,
			array(
				'name'    => __( 'Double-Optin', 'betteroptin' ),
				'id'      => 'mc_double_optin',
				'type'    => 'checkbox',
				'default' => true,
				'desc'    => __( 'MailChimp asks for a subscription confirmation. It is advised NOT to disable it.', 'betteroptin' )
			),
			array(
				'name'    => __( 'Update Existing', 'betteroptin' ),
				'id'      => 'mc_update_existing',
				'type'    => 'checkbox',
				'default' => true,
				'desc'    => __( 'Update contacts if already subscribed.', 'betteroptin' )
			),
			array(
				'name'    => __( 'Welcome E-Mail', 'betteroptin' ),
				'id'      => 'mc_welcome',
				'type'    => 'checkbox',
				'default' => true,
				'desc'    => __( 'Send a welcome e-mail after subscription.', 'betteroptin' )
			),
		)
	);

	return $settings;

}