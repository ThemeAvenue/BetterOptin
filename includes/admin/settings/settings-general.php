<?php
/**
 * BetterOptin Settings
 *
 * @package   BetterOptin/General Settings
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpbo_plugin_settings', 'wpbo_settings_general', 9 );
/**
 * Add general settings.
 *
 * @since  1.0.0
 * @param  array $settings Pre-existing settings
 * @return array           Updated plugin settings
 */
function wpbo_settings_general( $settings ) {

	$providers = apply_filters( 'wpbo_mailing_providers', array() );

	$settings['general'] = array(
		'name'    => __( 'General', 'betteroptin' ),
		'options' => array(
			array(
				'type'    => 'heading',
				'name'    => __( 'Licensing', 'betteroptin' ),
			),
			array(
				'name'    => __( 'License Key', 'betteroptin' ),
				'id'      => 'license_key',
				'type'    => 'edd-license',
				'desc'    => sprintf( esc_html__( 'If you don&#039;t have one, you can get one at %s', 'betteroptin' ), '<a href="https://betteropt.in/?utm_source=plugin&utm_medium=license_nag&utm_campaign=upsell" target="_blank">https://betteropt.in</a>' ),
				'default' => '',
				'server'    => esc_url( 'https://betteropt.in' ),
				'item_name' => 'BetterOptin',
				'item_id'   => 81877,
				'file'      => WPBO_PLUGIN_FILE
			),
			array(
				'type'    => 'heading',
				'name'    => __( 'Settings', 'betteroptin' ),
			),
			array(
				'name'    => __( 'E-Mailing Provider', 'betteroptin' ),
				'id'      => 'mailing_provider',
				'type'    => 'select',
				'options' => $providers,
				'desc'    => __( 'Which e-mailing provider do you use?', 'betteroptin' ),
				'default' => 'wordpress'
			),
			array(
				'name'    => __( 'Return URL', 'betteroptin' ),
				'id'      => 'return_url',
				'type'    => 'text',
				'desc'    => __( 'Where should the user be redirected after subscribing? This can be overwritten in each popup.', 'betteroptin' ),
				'default' => home_url()
			),
			array(
				'name'    => __( 'Anonymize IPs', 'betteroptin' ),
				'id'      => 'anonymize_ip',
				'type'    => 'checkbox',
				'desc'    => __( 'Delete the last byte(s) of stored IP addresses? This will remove the last digits of saved IP addresses to protect users privacy.', 'betteroptin' ),
				'default' => false
			),
			array(
				'name'    => __( 'Hide for Admins', 'betteroptin' ),
				'id'      => 'hide_admins',
				'type'    => 'checkbox',
				'desc'    => __( 'Hide the popups for admins? No popup will ever show up for site administrators.', 'betteroptin' ),
				'default' => true
			),
		)
	);

	return $settings;

}