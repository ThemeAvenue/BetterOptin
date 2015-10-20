<?php
/**
 * BetterOptin Metabox
 *
 * @package   BetterOptin/Metabox
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'add_meta_boxes', 'wpbo_register_metabox_steps' );
/**
 * Popup Design Step.
 *
 * This popup requires additional checks so
 * we use a completely custom one.
 *
 * @since  1.0.0
 */
function wpbo_register_metabox_steps() {
	add_meta_box( 'wpbo_step1_template', __( 'Step 1: Choose a Template', 'betteroptin' ), 'wpbo_display_metabox_step1', 'wpbo-popup', 'normal', 'default' );
	add_meta_box( 'wpbo_step2_settings', __( 'Step 2: Edit Settings', 'betteroptin' ), 'wpbo_display_metabox_step2', 'wpbo-popup', 'normal', 'default' );
	add_meta_box( 'wpbo_step3_display', __( 'Step 3: Choose where to Display', 'betteroptin' ), 'wpbo_display_metabox_step3', 'wpbo-popup', 'normal', 'default' );
	add_meta_box( 'wpbo_step4_design', __( 'Step 4: Customize the Design', 'betteroptin' ), 'wpbo_display_metabox_step4', 'wpbo-popup', 'normal', 'default' );
	add_meta_box( 'wpbo_step5_checklist', __( 'Checklist', 'betteroptin' ), 'wpbo_display_metabox_step5', 'wpbo-popup', 'side', 'high' );
}

/**
 * Display the content of step 1 metabox.
 *
 * @since  1.0.0
 */
function wpbo_display_metabox_step1() {
	include_once( WPBO_PATH . 'includes/admin/views/metaboxes/template.php' );
}

/**
 * Display the content of step 2 metabox.
 *
 * @since  1.0.0
 */
function wpbo_display_metabox_step2() {
	include_once( WPBO_PATH . 'includes/admin/views/metaboxes/settings.php' );
}

/**
 * Display the content of step 3 metabox.
 *
 * @since  1.0.0
 */
function wpbo_display_metabox_step3() {
	include_once( WPBO_PATH . 'includes/admin/views/metaboxes/display.php' );
}

/**
 * Display the content of step 3 metabox.
 *
 * @since  1.0.0
 */
function wpbo_display_metabox_step4() {
	include_once( WPBO_PATH . 'includes/admin/views/metaboxes/design.php' );
}

/**
 * Display the content of step 3 metabox.
 *
 * @since  1.0.0
 */
function wpbo_display_metabox_step5() {
	include_once( WPBO_PATH . 'includes/admin/views/metaboxes/checklist.php' );
}

add_action( 'save_post', 'wpbo_save_custom_fields' );
/**
 * Save custom fields.
 *
 * Save the complex custom fields that can't be handled
 * through TItan Framework. This includes step 3: where
 * the popup should be displayed.
 *
 * @param int $post_id Current post ID
 *
 * @since  1.0.0
 */
function wpbo_save_custom_fields( $post_id ) {

	if ( ! isset( $_POST['wpbo_display'] ) || isset( $_POST['wpbo_display'] ) && ! wp_verify_nonce( $_POST['wpbo_display'], 'add_display' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/**
	 * STEP 1: Template
	 */
	if ( isset( $_POST['wpbo_template'] ) ) {

		$previous_template = get_post_meta( $post_id, 'wpbo_template', true );
		update_post_meta( $post_id, 'wpbo_template', $_POST['wpbo_template'], $previous_template );

		/* Delete possible customizations */
		if ( $previous_template != $_POST['wpbo_template'] ) {
			delete_post_meta( $post_id, '_wpbo_template_editor' );
			delete_post_meta( $post_id, '_wpbo_template_display' );
		}

	}

	/**
	 * STEP 2: Settings
	 */
	$settings = array();
	$step2    = array(
		'close_overlay',
		'close_esc',
		'cookie_lifetime',
		'animation',
		'close_button',
		'overlay_color',
		'overlay_opacity',
		'wiggle',
		'return_url',
	);

	if ( isset( $_POST['wpbo_settings'] ) ) {

		foreach ( $step2 as $option ) {

			if ( isset( $_POST['wpbo_settings'][ $option ] ) ) {

				$settings[ $option ] = $_POST['wpbo_settings'][ $option ];

			}

		}

		update_post_meta( $post_id, '_wpbo_settings', $settings );

	}

	/**
	 * STEP 3: Display
	 */

	/**
	 * Display everywhere.
	 *
	 * This is the simplest case: just display the popup
	 * on every single page that is loaded.
	 */
	if ( isset( $_POST['wpbo_display_all'] ) ) {
		update_post_meta( $post_id, '_wpbo_display_all', 'yes' );
	} else {
		update_post_meta( $post_id, '_wpbo_display_all', 'no' );
	}

	/* Get available public post types */
	$post_types = get_post_types( array( 'public' => true ) );
	$except     = array( 'attachment', 'wpbo-popup' );

	/* Get popup / posts relationships */
	$relationships = get_option( 'wpbo_popup_relationships', array() );

	/**
	 * Handle each post individually.
	 */
	foreach ( $post_types as $key => $pt ) {

		/* Exclude specific post types */
		if ( in_array( $key, $except ) ) {
			continue;
		}

		/* Current value */
		$current = get_post_meta( $post_id, '_wpbo_display_' . $pt, true );

		/* Set $current at the correct format if needed */
		if ( ! is_array( $current ) ) {
			$current = array();
		}

		if ( isset( $_POST[ 'wpbo_display_' . $pt ] ) ) {

			$display = $_POST[ 'wpbo_display_' . $pt ];

			if ( isset( $_POST[ 'wpbo_display_' . $pt . '_all' ] ) ) {
				$display = 'all';
			}

			/**
			 * Create the relationships
			 */
			if ( is_array( $display ) ) {

				foreach ( $display as $key => $post ) {

					/**
					 * Check if a relationship already exists for this post.
					 * If there is one, we remove it from the post meta itself
					 * as relationships are also stored in the post metas (so that
					 * we can populate the fields).
					 */
					if ( isset( $relationships[ $post ] ) && $post_id != $relationships[ $post ] ) {

						$old = $edit = get_post_meta( $relationships[ $post ], '_wpbo_display_' . $pt, true );

						if ( is_array( $edit ) && ( $relation_key = array_search( $post, $edit ) ) !== false ) {

							unset( $edit[ $relation_key ] );

							update_post_meta( $relationships[ $post ], '_wpbo_display_' . $pt, $edit, $old );
						}

					}

					/* Add the new relationship */
					$relationships[ $post ] = $post_id;
				}

				/**
				 * Clean previous relationships that might have been removed.
				 */
				$diff = array_diff( $current, $display );

				foreach ( $diff as $dkey => $dval ) {
					if ( isset( $relationships[ $dval ] ) ) {
						unset( $relationships[ $dval ] );
					}
				}

				/**
				 * Update the relationships
				 */
				update_option( 'wpbo_popup_relationships', $relationships );

			}

			if ( maybe_serialize( $current ) != maybe_serialize( $display ) ) {
				update_post_meta( $post_id, '_wpbo_display_' . $pt, $display );
			}

		} elseif ( ! isset( $_POST[ 'wpbo_display_' . $pt ] ) ) {

			$prev = get_post_meta( $post_id, '_wpbo_display_' . $pt, true );

			if ( isset( $_POST[ 'wpbo_display_' . $pt . '_all' ] ) && 'all' != $current ) {
				update_post_meta( $post_id, '_wpbo_display_' . $pt, 'all' );
			} elseif ( ! isset( $_POST[ 'wpbo_display_' . $pt . '_all' ] ) && '' != $current ) {
				delete_post_meta( $post_id, '_wpbo_display_' . $pt );
			}

			/**
			 * Remove the relationships
			 */
			if ( is_array( $prev ) ) {

				foreach ( $prev as $pid ) {

					if ( isset( $relationships[ $pid ] ) ) {
						unset( $relationships[ $pid ] );
					}

				}

				update_option( 'wpbo_popup_relationships', $relationships );

			}

		}

	}

	/* Redirect to customizer */
	if ( isset( $_POST['save_customize'] ) ) {

		$customizer = add_query_arg( array( 'post_type'  => 'wpbo-popup',
		                                    'page'       => 'wpbo-customizer',
		                                    'wpbo_popup' => $post_id
		), admin_url( 'edit.php' ) );

		wp_redirect( $customizer );

		exit;

	}

}