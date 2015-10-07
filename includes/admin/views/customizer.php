<?php
$popup_id = filter_input( INPUT_GET, 'wpbo_popup', FILTER_SANITIZE_NUMBER_INT );
$popup    = new WPBO_Popup( $popup_id );
$template = $popup->get_markup();
?>
	<div class="wrap">

		<?php
		require_once( WPBO_PATH . 'includes/admin/views/customizer-controls.php' );

		if ( empty( $popup_id ) ) {

			// Ask which popup to customize
			require_once( WPBO_PATH . 'includes/admin/views/customizer-template-selector.php' );

		} elseif ( empty( $template ) ) {

			$link    = empty( $popup_id ) ? add_query_arg( array( 'post_type' => 'wpbo-popup' ), admin_url( 'edit.php' ) ) : add_query_arg( array( 'post'   => $popup_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
			$message = sprintf( __( 'There is no template selected for this popup. Please <a href="%s">edit the popup settings</a>.', 'betteroptin' ), esc_url( $link ) );

			printf( '<p class="wpbo-warning">%s</p>', $message );

		} else {

			$bg_color = $popup->option( 'overlay_color', '#000' );
			$opacity  = $popup->option( 'overlay_opacity', '0.5' );

			printf( '<div class="wpbo"><div class="taed-admin-overlay" style="background-color: %s; opacity: %s;"></div><div class="taed-webfontload">%s<br><img src="%s" alt="%s"></div>%s</div>', $bg_color, $opacity, esc_attr_x( 'Please be patient', 'Popup template is loading', 'betteroptin' ), WPBO_URL . 'admin/assets/images/ajax-loader.gif', esc_html__( 'Loading', 'betteroptin' ), $template );

			?><a href="<?php echo $cancel; ?>"
			     class="button-secondary wpbo-back-btn">&larr; <?php esc_html_e( 'Back to popup settings', 'betteroptin' ); ?></a><?php

		}
		?>

	</div>

<?php
if ( false === wpbo_is_tour_completed() ) {
	include_once( WPBO_PATH . 'includes/admin/views/customizer-tour.php' );
}