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

/**
 * Retrieve the popup templates list.
 *
 * @since  1.0.0
 * @return array List of available templates with the associated screenshot
 */
function wpbo_get_templates_list() {

	/* Set the default templates directory */
	$directory = array(
		'path' => WPBO_PATH . 'includes/templates',
		'url'  => WPBO_URL . 'includes/templates'
	);

	/* Allow for extra directories */
	$dirs = apply_filters( 'wpbo_templates_dirs', array( $directory ) );
	$list = array();

	foreach( $dirs as $key => $dir ) {

		$exceptions = array( '.', '..' );

		if( !isset( $_GET['test_template'] ) )
			$exceptions[] = 'template-test.php';

		/* Get file paths with trailing slashes */
		$path = trailingslashit( $dir['path'] );
		$url  = trailingslashit( $dir['url'] );

		/* Scan the content */
		$templates = scandir( $path );

		foreach( $templates as $key => $template ) {

			$images = array( 'png', 'jpg', 'jpeg', 'gif' ); // Allowed images types

			/* Don't process the '.' and '..' */
			if( in_array( $template, $exceptions ) )
				continue;

			/* Get file extension */
			$ext = pathinfo( $path . $template, PATHINFO_EXTENSION );

			/* Only check the php files */
			if( 'php' != $ext )
				continue;

			/* Get template base name */
			$tpl = str_replace( ".$ext", '', $template );

			foreach( $images as $k => $type ) {

				$imgfile = $tpl . '.' . $type;

				if( file_exists( $path . $imgfile ) ) {

					/**
					 * @todo need to get image URL
					 */
					$img = $url . $imgfile;
					break;

				}

			}

			/* Add new template to the list */
			$list[$tpl] = $img;

		}
	}

	return $list;

}

add_action( 'plugins_loaded', 'wpbo_save_templates' );
/**
 * Save Customized Templates.
 *
 * @since  1.0.0
 */
function wpbo_save_templates() {

	if ( ! isset( $_GET['wpbo_popup'] ) || ! isset( $_POST['wpbo_nonce'] ) ) {
		return;
	}

	$post_id = intval( $_GET['wpbo_popup'] );

	if ( 'wpbo-popup' != get_post_type( $post_id ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['wpbo_nonce'], 'wpbo_customize_template' ) ) {
		return;
	}

	if ( isset( $_POST['taed-outerhtml'] ) ) {
		update_post_meta( $post_id, '_wpbo_template_editor', htmlentities( $_POST['taed-outerhtml'], ENT_COMPAT | ENT_HTML401, 'UTF-8' ) );
	}

	if ( isset( $_POST['taed-outerhtmlclean'] ) ) {
		update_post_meta( $post_id, '_wpbo_template_display', htmlentities( $_POST['taed-outerhtmlclean'], ENT_COMPAT | ENT_HTML401, 'UTF-8' ) );
	}

	/* Read-only redirect */
	wp_redirect( add_query_arg( array( 'post_type'  => 'wpbo-popup',
	                                   'page'       => 'wpbo-customizer',
	                                   'wpbo_popup' => $post_id,
	                                   'message'    => 'updated'
	), admin_url( 'edit.php' ) ) );

	exit;

}


add_action( 'init', 'wpbo_reset_template' );
/**
 * Reset to default template.
 *
 * Deletes all customizations from database which
 * will result in using the default template file.
 *
 * @since  1.0.0
 */
function wpbo_reset_template() {

	if ( isset( $_GET['wpbo_reset'] ) && isset( $_GET['wpbo_popup'] ) && wp_verify_nonce( $_GET['wpbo_reset'], 'reset_template' ) ) {

		delete_post_meta( $_GET['wpbo_popup'], '_wpbo_template_editor' );
		delete_post_meta( $_GET['wpbo_popup'], '_wpbo_template_display' );

		wp_redirect( add_query_arg( array(
			'wpbo_popup' => $_GET['wpbo_popup'],
			'post_type'  => 'wpbo-popup',
			'page'       => 'wpbo-customizer'
		), admin_url( 'edit.php' ) ) );

		exit;

	}

}