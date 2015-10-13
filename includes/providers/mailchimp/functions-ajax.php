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

add_action( 'wp_ajax_mc_get_groups', 'wpbo_mc_display_groups' );
/**
 * Display a list of groups.
 *
 * If groups are available for the specified list then we display a list
 * with checkboxes.
 *
 * @since  1.1.0
 * @return string List markup
 */
function wpbo_mc_display_groups() {

	/* Make sure we have a list ID to check */
	if ( ! isset( $_POST['mc_list_id'] ) ) {
		echo '<div class="form-invalid" style="padding: 1em;">' . __( 'An error occurred during the request. The list ID is missing.', 'betteroptin' ) . '</div>';
		wp_die();
	}

	$list_id = sanitize_key( $_POST['mc_list_id'] );
	$post_id = (int) $_POST['mc_post_id'];

	/* Get the groups */
	$groups = WPBO_MC()->get_groups( $list_id );

	if ( is_wp_error( $groups ) ) {

		/**
		 * @var WP_Error $groups
		 */

		echo '<div class="form-invalid" style="padding: 1em;">' . $groups->get_error_message() . '</div>';
		wp_die();
	}

	if ( is_array( $groups ) && ! empty( $groups ) ) {

		foreach ( $groups as $group ) {

			$show_groups   = new WPMC_MailChimp_Groups( $group['id'], $group['groups'], $post_id );
			$group_name    = $group['name'];
			$group_options = $group['form_field'];

			echo "<p>$group_name</p>";

			switch ( $group_options ) {

				case 'checkboxes':
					$show_groups->show_group_type_checkboxes();
					break;

				case 'radio':
					$show_groups->show_group_type_radio();
					break;

				case 'dropdown':
					$show_groups->show_group_type_dropdown();
					break;

				case 'hidden':
					$show_groups->show_group_type_checkboxes();
					break;

			}

		}

		wp_die();

	} else {
		echo '<div class="form-invalid" style="padding: 1em;">' . __( 'This list does not have any groups.', 'betteroptin' ) . '</div>';
		wp_die();
	}

}