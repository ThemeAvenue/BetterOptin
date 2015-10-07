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
		'name'    => __( 'General', 'wpbo' ),
		'options' => array(
			array(
				'name'    => __( 'E-Mailing Provider', 'wpbo' ),
				'id'      => 'mailing_provider',
				'type'    => 'select',
				'options' => $providers,
				'desc'    => __( 'Which e-mailing provider do you use?', 'wpbo' ),
				'default' => 'wordpress'
			),
			array(
				'name'    => __( 'Return URL', 'wpbo' ),
				'id'      => 'return_url',
				'type'    => 'text',
				'desc'    => __( 'Where should the user be redirected after subscribing?', 'wpbo' ),
				'default' => home_url()
			),
			array(
				'name'    => __( 'Anonymize IPs', 'wpbo' ),
				'id'      => 'anonymize_ip',
				'type'    => 'checkbox',
				'desc'    => __( 'Delete the last byte(s) of stored IP addresses? This will remove the last digits of saved IP addresses to protect users privacy.', 'wpbo' ),
				'default' => false
			),
			array(
				'name'    => __( 'Hide for Admins', 'wpbo' ),
				'id'      => 'hide_admins',
				'type'    => 'checkbox',
				'desc'    => __( 'Hide the popups for admins? No popup will ever show up for site administrators.', 'wpbo' ),
				'default' => true
			),
			array(
				'name'    => __( 'Show Credit', 'wpbo' ),
				'id'      => 'show_credit',
				'type'    => 'checkbox',
				'desc'    => __( 'Display a credit link at the bottom of the popups. <strong>Thanks for supporting the plugin</strong>.', 'wpbo' ),
				'default' => true
			)
		)
	);

	return $settings;

}