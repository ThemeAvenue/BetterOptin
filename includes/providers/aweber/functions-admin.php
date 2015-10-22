<?php
/**
 * BetterOptin Provider Aweber
 *
 * @package   BetterOptin/Provider/Aweber
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'wpbo_checklist', 'wpbo_aw_add_step' );
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
function wpbo_aw_add_step( $checklist ) {

	$new   = array();
	$step  = array(
		'label'   => __( 'Setup Aweber', 'wpbo' ),
		'check'   => wpbo_is_aweber_ready(),
		'against' => true,
		'compare' => 'EQUAL'
	);

	foreach ( $checklist as $id => $check ) {

		if ( 'published' == $id ) {
			$new['aweber'] = $step;
		}

		$new[ $id ] = $check;

	}

	return $new;

}

add_filter( 'wpbo_publish_button_action', 'wpbo_aw_prevent_publishing', 10, 2 );
/**
 * Prevent from publishing popup if Aweber is not ready.
 *
 * @since  1.0.0
 *
 * @param  array $data    Sanitized data
 * @param  array $postarr Raw data
 *
 * @return array          Sanitized data with updated status
 */
function wpbo_aw_prevent_publishing( $data, $postarr ) {

	if ( 'wpbo-popup' == $postarr['post_type'] && 'publish' == $data['post_status'] ) {

		if ( ! wpbo_is_aweber_ready() ) {
			$data['post_status'] = 'draft';
		}

	}

	return $data;

}

add_filter( 'wpbo_publish_button_label', 'wpbo_publish_button_label', 10, 2 );
/**
 * Change publish button label
 *
 * @since 1.0
 *
 * @param string $translation
 * @param string $text
 *
 * @return string
 */
function wpbo_publish_button_label( $translation, $text ) {

	global $typenow;

	if ( 'wpbo-popup' == $typenow ) {

		if ( isset( $_GET['post'] ) && 'Publish' == $text && ! wpbo_is_aweber_ready() ) {
			$translation = __( 'Save', 'wpbo' );
		}

	}

	return $translation;

}

add_action( 'admin_notices', 'wpbo_aw_settings_warning' );
/**
 * User warning.
 *
 * Warn user if settings are not correct.
 *
 * @since  1.0.0
 */
function wpbo_aw_settings_warning() {

	if ( ! wpbo_is_aweber_ready() ): ?>

		<div class="error">
			<p><?php printf( __( 'Aweber integration is not correctly setup. Your popups won\'t send subscribers anywhere! <a href="%s">Click here to see the settings</a>.', 'wpbo' ), esc_url( add_query_arg( array(
					'post_type' => 'wpbo-popup',
					'page'      => 'edit.php?post_type=wpbo-popup-settings&tab=aweber'
				), admin_url( 'edit.php' ) ) ) ); ?></p>
		</div>

	<?php endif;

}