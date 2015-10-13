<?php
/**
 * BetterOptin Provider MailChimp
 *
 * @package   BetterOptin/Provider/MailChimp
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'admin_notices', 'wpbo_mc_settings_warning' );
/**
 * Incomplete settings warning.
 *
 * Warn user if settings are not correct.
 *
 * @since  1.0.0
 */
function wpbo_mc_settings_warning() {

	if ( ! WPBO_MC()->is_mailchimp_ready() ): ?>

		<div class="error">
			<p><?php printf( __( 'MailChimp integration is not correctly setup. Your popups won\'t send subscribers anywhere! <a href="%s">Click here to see the settings</a>.', 'betteroptin' ), esc_url( add_query_arg( array(
					'post_type' => 'wpbo-popup',
					'page'      => 'edit.php?post_type=wpbo-popup-settings&tab=mailchimp'
				), admin_url( 'edit.php' ) ) ) ); ?></p>
		</div>

	<?php endif;

}

add_filter( 'wpbo_checklist', 'wpbo_mc_add_step' );
/**
 * Add new step.
 *
 * Add a step in the checklist during popup creation process
 * in order to was the user if the settings are not correct.
 *
 * @since  1.0.0
 *
 * @param  array $checklist Current checklist
 *
 * @return array            Updated checklist
 */
function wpbo_mc_add_step( $checklist ) {

	if ( ! wpbo_is_popup_edit_screen() ) {
		return $checklist;
	}

	$ready = WPBO_MC()->is_mailchimp_ready() ? true : false;
	$new   = array();
	$step  = array(
		'label'   => __( 'Setup MailChimp', 'betteroptin' ),
		'check'   => $ready,
		'against' => true,
		'compare' => 'EQUAL'
	);

	foreach ( $checklist as $id => $check ) {

		if ( 'published' == $id ) {
			$new['mailchimp'] = $step;
		}

		$new[ $id ] = $check;

	}

	return $new;

}

add_filter( 'wpbo_publish_button_action', 'wpbo_mc_prevent_publishing', 10, 2 );
/**
 * Prevent from publishing popup if MailChimp is not ready.
 *
 * @since  1.0.0
 *
 * @param  array $data    Sanitized data
 * @param  array $postarr Raw data
 *
 * @return array          Sanitized data with updated status
 */
function wpbo_mc_prevent_publishing( $data, $postarr ) {

	if ( ! wpbo_is_popup_edit_screen() ) {
		return $data;
	}

	if ( 'wpbo-popup' == $postarr['post_type'] && 'publish' == $data['post_status'] ) {

		if ( ! $this->is_mailchimp_ready() ) {
			$data['post_status'] = 'draft';
		}

	}

	return $data;

}

add_filter( 'wpbo_publish_button_label', 'wpbo_mc_publish_button_label', 10, 2 );
/**
 * Change the save button label if MailChimp integration is not yet ready
 *
 * @since 1.0
 *
 * @param $translation
 * @param $text
 *
 * @return string|void
 */
function wpbo_mc_publish_button_label( $translation, $text ) {

	if ( ! wpbo_is_popup_edit_screen() ) {
		return $translation;
	}

	global $typenow;

	if ( 'wpbo-popup' == $typenow ) {

		if ( isset( $_GET['post'] ) && 'Publish' == $text && ! WPBO_MC()->is_mailchimp_ready() ) {
			$translation = __( 'Save', 'betteroptin' );
		}

	}

	return $translation;
}