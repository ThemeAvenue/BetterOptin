<?php
/**
 * BetterOptin Popup
 *
 * @package   BetterOptin/Popup
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_filter( 'manage_wpbo-popup_posts_columns', 'wpbo_relationships_column', 10, 2 );
/**
 * Add relationship column.
 *
 * Add a relationships column in the popup
 * list screen.
 *
 * @param  array $columns Currently available columns
 *
 * @return array          Columns containing the relationships
 */
function wpbo_relationships_column( $columns ) {

	$new = array();

	foreach ( $columns as $key => $label ) {

		$new[ $key ] = $label;

		if ( 'title' == $key ) {
			$new['relationship'] = esc_html__( 'Appears On', 'betteroptin' );
			$new['template']     = esc_html__( 'Template', 'betteroptin' );
			$new['returl']       = esc_html__( 'Return URL', 'betteroptin' );
		}

	}

	return $new;

}


add_action( 'manage_wpbo-popup_posts_custom_column', 'wpbo_relationships_column_content', 10, 2 );
/**
 * Relationship content.
 *
 * Get the relationships for all popups and display it
 * in the relationships custom column.
 *
 * @since  1.0.0
 *
 * @param  array   $column  Current column ID
 * @param  integer $post_id Current post ID
 */
function wpbo_relationships_column_content( $column, $post_id ) {

	switch ( $column ) {

		case 'relationship':

			/**
			 * First we check if it is "display everywhere".
			 */
			if ( 'yes' == get_post_meta( $post_id, '_wpbo_display_all', true ) ) {
				esc_html_e( 'Everywhere', 'betteroptin' );

				return;
			}

			/**
			 * Second we check if it displays everywhere for a specific post type.
			 */
			$post_types = get_post_types( array( 'public' => true ) );
			$except     = array( 'attachment', 'wpbo-popup' );
			$pts        = array();

			foreach ( $post_types as $key => $pt ) {

				if ( in_array( $key, $except ) ) {
					continue;
				}

				if ( 'all' == get_post_meta( $post_id, '_wpbo_display_' . $pt, true ) ) {
					array_push( $pts, sprintf( esc_html__( 'All %s', 'betteroptin' ), ucwords( $pt ) ) );
				}

			}

			if ( count( $pts ) > 0 ) {
				echo implode( ', ', $pts );

				return;
			}

			/**
			 * Third we check the individual relationships.
			 */
			$relationships = get_option( 'wpbo_popup_relationships', array() );
			$reverse       = array();
			$list          = array();

			/**
			 * Switch keys and values without erasing duplicate values
			 * (which is why array_flip() would not work).
			 */
			foreach ( $relationships as $page => $popup ) {

				if ( ! isset( $reverse[ $popup ] ) ) {
					$reverse[ $popup ] = array();
				}

				array_push( $reverse[ $popup ], $page );

			}

			/* No relationships at all */
			if ( ! array_key_exists( $post_id, $reverse ) ) {
				echo '-';

				return;
			}

			/**
			 * Print all the relationships in a table.
			 */
			foreach ( $reverse[ $post_id ] as $key => $page ) {

				$page  = get_post( $page );
				$link  = add_query_arg( array( 'post' => $page->ID, 'action' => 'edit' ), admin_url( 'post.php' ) );
				$title = $page->post_title;

				array_push( $list, "<a href='$link' class='wpbo-tag'>$title</a>" );

			}

			if ( count( $list ) > 0 ) {
				echo implode( ' ', $list );
			}

			break;

		case 'template':

			$template = get_post_meta( $post_id, 'wpbo_template', true );

			if ( ! empty( $template ) ) {
				printf( '<code>%s</code>', $template );
			}

			break;

		case 'returl':

			$returl = wpbo_get_return_url( $post_id );

			if ( ! empty( $returl ) ) {
				printf( '<a href="%1$s" target="_blank">%1$s</a>', esc_url( $returl ) );
			}

			break;

	}

}