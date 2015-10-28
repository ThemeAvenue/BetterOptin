<?php
/**
 * BetterOptin Installer
 *
 * @package   BetterOptin/Install
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'wp_enqueue_scripts', 'wpbo_enqueue_styles' );
/**
 * Register and enqueue public-facing style sheet.
 *
 * @since    1.0.0
 */
function wpbo_enqueue_styles() {

	if ( ! wpbo_page_has_popup() ) {
		return;
	}

	wp_enqueue_style( 'wpbo-main', WPBO_URL . 'public/assets/css/betteroptin.css', array(), WPBO_VERSION );
}


add_action( 'wp_enqueue_scripts', 'wpbo_enqueue_scripts' );
/**
 * Register and enqueues public-facing JavaScript files.
 *
 * @since    1.0.0
 */
function wpbo_enqueue_scripts() {

	if ( ! wpbo_page_has_popup() ) {
		return;
	}

	$settings = get_post_meta( wpbo_page_has_popup(), '_wpbo_settings', true );

	/**
	 * Check if we can display the credit.
	 */
	$settings['credit'] = apply_filters( 'wpbo_show_credit', true );

	/**
	 * Use booleans
	 */
	$settings['close_overlay']   = isset( $settings['close_overlay'] ) ? true : false;
	$settings['close_esc']       = isset( $settings['close_esc'] ) ? true : false;
	$settings['wiggle']          = isset( $settings['wiggle'] ) ? true : false;
	$settings['cookie_lifetime'] = isset( $settings['cookie_lifetime'] ) ? intval( $settings['cookie_lifetime'] ) : 30;

	/**
	 * Add popup ID
	 */
	$settings['popup_id'] = wpbo_page_has_popup();

	/**
	 * Define Ajax URL
	 */
	$settings['ajaxurl'] = admin_url( 'admin-ajax.php' );

	wp_enqueue_script( 'wpbo-script', WPBO_URL . 'public/assets/js/betterOptin.min.js', array( 'jquery' ), WPBO_VERSION );
	wp_localize_script( 'wpbo-script', 'wpbo', json_encode( $settings ) );

}

add_action( 'admin_enqueue_scripts', 'wpbo_enqueue_admin_styles' );
/**
 * Register and enqueue admin-specific style sheet
 *
 * @since     1.0.0
 * @return    null    Return early if no settings page is registered.
 */
function wpbo_enqueue_admin_styles() {

	global $post, $current_screen;

	if ( wpbo_is_plugin_page() ) {

		wp_enqueue_style( 'wpbo-admin', WPBO_URL . 'admin/assets/css/admin.css', array(), WPBO_VERSION );
		wp_enqueue_style( 'wpbo-admin-chosen', WPBO_URL . 'bower_components/chosen_v1.1.0/chosen.min.css', array(), WPBO_VERSION );

		/* Customizer page */
		if ( isset( $_GET['wpbo_popup'] ) ) {

			wp_enqueue_style( 'wpbo-editor', WPBO_URL . 'admin/assets/css/ta-editor.css', array(), WPBO_VERSION );
			wp_enqueue_style( 'wpbo-main', WPBO_URL . 'public/assets/css/betteroptin.css', array(), WPBO_VERSION );

		}

		/* Load colorpicker style */
		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ||
		     isset( $current_screen->action ) && isset( $current_screen->post_type ) && 'add' == $current_screen->action && 'wpbo-popup' == $current_screen->post_type ||
		     isset( $current_screen->base ) && 'wpbo-popup_page_wpbo-customizer' == $current_screen->base
		) {
			wp_enqueue_style( 'wp-color-picker' );
		}

		/* Analytics page */
		if ( isset( $_GET['page'] ) && 'wpbo-analytics' == $_GET['page'] ) {

			wp_enqueue_style( 'wpbo-dataTables', '//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.1/css/jquery.dataTables.min.css', array(), WPBO_VERSION );
			wp_enqueue_style( 'wpbo-circliful', WPBO_URL . 'bower_components/circliful/css/jquery.circliful.css', array(), WPBO_VERSION );

		}

	}

}


add_action( 'admin_enqueue_scripts', 'wpbo_enqueue_admin_scripts' );
/**
 * Register and enqueue admin-specific JavaScript.
 *
 * @since     1.0.0
 * @return    null    Return early if no settings page is registered.
 */
function wpbo_enqueue_admin_scripts() {

	global $post, $current_screen;

	if ( wpbo_is_plugin_page() ) {

		/* Required on all plugin pages */
		wp_enqueue_script( 'wpbo--admin', WPBO_URL . 'admin/assets/js/admin.js', array( 'jquery' ), WPBO_VERSION );

		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] || isset( $_GET['post_type'] ) && 'wpbo-popup' == $_GET['post_type'] ) {

			wp_enqueue_script( 'wpbo-admin-chosen', WPBO_URL . 'bower_components/chosen_v1.1.0/chosen.jquery.min.js', array( 'jquery' ), WPBO_VERSION );

		}

		/* Required only on the post edit screen */
		if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] || isset( $current_screen->action ) && isset( $current_screen->post_type ) && 'add' == $current_screen->action && 'wpbo-popup' == $current_screen->post_type ) {
			wp_enqueue_script( 'wp-color-picker' );
		}

		/* Required on the customizer page only */
		if ( isset( $_GET['wpbo_popup'] ) ) {

			wp_enqueue_media();
			wp_enqueue_script( 'wpbo-admin-script', WPBO_URL . 'admin/assets/js/ta-live-editor.js', array( 'jquery', 'wp-color-picker' ), WPBO_VERSION );
			wp_enqueue_script( 'wpbo-admin-autosize', WPBO_URL . 'bower_components/jquery-autosize/jquery.autosize.min.js', array( 'jquery' ), WPBO_VERSION );
			wp_enqueue_script( 'wpbo-admin-matchHeight', WPBO_URL . 'bower_components/matchHeight/jquery.matchHeight-min.js', array( 'jquery' ), WPBO_VERSION );

		}

		if ( isset( $_GET['page'] ) ) {

			/* Analytics page */
			if ( 'wpbo-analytics' == $_GET['page'] ) {

				wp_enqueue_script( 'wpbo-admin-dataTables', '//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.1/js/jquery.dataTables.min.js', array( 'jquery' ), WPBO_VERSION );
				wp_enqueue_script( 'wpbo-admin-flot', WPBO_URL . 'bower_components/flot/jquery.flot.js', array( 'jquery' ), WPBO_VERSION );
				wp_enqueue_script( 'wpbo-admin-flot-time', WPBO_URL . 'bower_components/flot/jquery.flot.time.js', array( 'jquery' ), WPBO_VERSION );
				wp_enqueue_script( 'wpbo-admin-flot-tooltip', WPBO_URL . 'bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js', array( 'jquery' ), WPBO_VERSION );
				wp_enqueue_script( 'wpbo-admin-circliful', WPBO_URL . 'bower_components/circliful/js/jquery.circliful.min.js', array( 'jquery' ), WPBO_VERSION );
				wp_enqueue_script( 'wpbo-admin-analytics', WPBO_URL . 'admin/assets/js/part-analytics.js', array( 'jquery' ), WPBO_VERSION );

			}

		}

	}

}