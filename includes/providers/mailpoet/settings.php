<?php
/**
 * BetterOptin Provider MailPoet
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

add_filter( 'wpbo_plugin_settings', 'wpbo_mp_settings' );
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
function wpbo_mp_settings( $settings ) {

	$lists          = wpbo_mp_get_mailpoet_lists();
	$lists_list[''] = esc_html_x( 'Please select...', 'Select a MailPoet mailing list',  'betteroptin' );

	foreach ( $lists as $list ) {
		$lists_list[ $list['list_id'] ] = $list['name'];
	}

	$list_id = array(
		'name'    => __( 'List', 'betteroptin' ),
		'id'      => 'mp_list_id',
		'type'    => 'select',
		'options' => $lists_list,
		'default' => ''
	);

	$settings['mailpoet'] = array(
		'name'    => __( 'MailPoet', 'betteroptin' ),
		'options' => array(
			$list_id
		)
	);

	return $settings;

}