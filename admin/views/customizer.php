<?php
$popup_id = isset( $_GET['wpbo_popup'] ) ? intval( $_GET['wpbo_popup'] ) : false;
$popup    = Better_Optin::get_popup_markup( $popup_id );
?>
<div class="wrap">

	<?php
	require_once( WPBO_PATH . 'admin/views/customizer-controls.php' );

	if( false === $popup_id ) {

		// Ask which popup to customize
		require_once( WPBO_PATH . 'admin/views/customizer-template-selector.php' );

	} elseif( false === $popup ) {

		// Template does not exist. Please edit popup.
		echo '<p class="wpbo-warning">';
		$link = $popup_id ? add_query_arg( array( 'post' => $popup_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) : add_query_arg( array( 'post_type' => 'wpbo-popup' ), admin_url( 'edit.php' ) );
		printf( __( 'There is no template selected for this popup. Please <a href="%s">edit the popup settings</a>.', 'wpbo' ), $link );
		echo '</p>';

	} else {

		$options  = get_post_meta( $popup_id, '_wpbo_settings', true );
		$bg_color = isset( $options['overlay_color'] ) ? $options['overlay_color'] : '#000';
		$opacity  = isset( $options['overlay_opacity'] ) ? $options['overlay_opacity'] : '0.5';

		printf( '<div class="wpbo"><div class="taed-admin-overlay" style="background-color: %s; opacity: %s;"></div><div class="taed-webfontload">Please be patient<br><img src="%s" alt="%s"></div>%s</div>', $bg_color, $opacity, WPBO_URL .'admin/assets/images/ajax-loader.gif', __( 'Loading', 'wpbo' ), $popup );

		?><a href="<?php echo $cancel; ?>" class="button-secondary wpbo-back-btn">&larr; <?php _e( 'Back to popup settings', 'wpbo' ); ?></a><?php

	}
	?>

</div>

<?php
if( false === Better_Optin_Admin::is_tour_completed() )
	include_once( WPBO_PATH . 'admin/views/customizer-tour.php' );
?>