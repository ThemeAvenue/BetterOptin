<?php
/**
 * BetterOptin Provider MailPoet
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

/**
 * Check if MailChimp settings are correct.
 *
 * @since  1.0.0
 * @return boolean True if MailPoet integration is ready to work
 */
function wpbo_is_mailpoet_ready() {

	$list_id = wpbo_get_option( 'mp_list_id', '' );

	if ( empty( $list_id ) ) {
		return false;
	}

	return true;

}

add_action( 'admin_notices', 'wpbo_mp_settings_warning' );
/**
 * User warning.
 *
 * Warn user if settings are not correct.
 *
 * @since  1.0.0
 */
function wpbo_mp_settings_warning() {

	if ( ! wpbo_is_mailpoet_ready() ): ?>

		<div class="error">
			<p><?php printf( __( 'Please select a list for your new subscribers, otherwise your popups won\'t send subscribers anywhere! <a href="%s">Click here to see the settings</a>.', 'betteroptin' ), esc_url( add_query_arg( array(
					'post_type' => 'wpbo-popup',
					'page'      => 'edit.php?post_type=wpbo-popup-settings&tab=mailpoet'
				), admin_url( 'edit.php' ) ) ) ); ?></p>
		</div>

	<?php endif;

}

add_filter( 'wpbo_checklist', 'wpbo_mp_add_step' );
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
function wpbo_mp_add_step( $checklist ) {

	$ready = wpbo_is_mailpoet_ready() ? true : false;
	$new   = array();
	$step  = array(
		'label'   => __( 'Setup MailPoet', 'betteroptin' ),
		'check'   => $ready,
		'against' => true,
		'compare' => 'EQUAL'
	);

	foreach ( $checklist as $id => $check ) {

		if ( 'published' == $id ) {
			$new['mailpoet'] = $step;
		}

		$new[ $id ] = $check;

	}

	return $new;

}

add_filter( 'wpbo_publish_button_action', 'wpbo_mp_prevent_publishing', 10, 2 );
/**
 * Prevent from publishing popup if MailPoet is not ready.
 *
 * @since  1.0.0
 *
 * @param  array $data    Sanitized data
 * @param  array $postarr Raw data
 *
 * @return array          Sanitized data with updated status
 */
function wpbo_mp_prevent_publishing( $data, $postarr ) {

	if ( 'wpbo-popup' == $postarr['post_type'] && 'publish' == $data['post_status'] ) {

		if ( ! wpbo_is_mailpoet_ready() ) {
			$data['post_status'] = 'draft';
		}

	}

	return $data;

}

add_filter( 'wpbo_publish_button_label', 'wpbo_mp_publish_button_label', 10, 2 );
/**
 * Change the "Publish" button label
 *
 * @param $translation
 * @param $text
 *
 * @return string
 */
function wpbo_mp_publish_button_label( $translation, $text ) {

	global $typenow;

	if ( 'wpbo-popup' == $typenow ) {

		if ( isset( $_GET['post'] ) && 'Publish' == $text && ! wpbo_is_mailpoet_ready() ) {
			$translation = __( 'Save', 'betteroptin' );
		}

	}

	return $translation;
}

/**
 * Get user lists.
 *
 * @since  1.0.0
 * @return array Array of available lists for this account
 */
function wpbo_mp_get_mailpoet_lists() {

	if ( ! class_exists( 'WYSIJA' ) ) {
		return array();
	}

	$model_list     = WYSIJA::get( 'list', 'model' );
	$mailpoet_lists = $model_list->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );

	return $mailpoet_lists;

}