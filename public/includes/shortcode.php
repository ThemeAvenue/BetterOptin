<?php
add_shortcode( 'wpbo_popup', 'wpbo_trigger_popup' );
/**
 * Manually triggers a popup.
 *
 * @since  1.0.1
 * @param  array $atts Shortcode attributes
 * @return string      HTML link
 */
function wpbo_trigger_popup( $atts = array() ) {

	$defaults = array(
		'popup_id'      => Better_Optin::is_popup_available(),
		'type'          => 'button',
		'label'         => __( 'Show Popup', 'wpbo' ),
		'bypass_cookie' => true,
		'btn_class'     => ''
	);

	extract( shortcode_atts( $defaults, $atts ) );

	/* No popup ID? Bye bye... */
	if( false === $popup_id )
		return false;

	/* Do NOT bypass the cookie */
	if( isset( $_COOKIE["wpbo_$popup_id"] ) && false === boolval( $bypass_cookie ) )
		return false;

	if( 'button' == $type )
		$sc = "<button class='wpbo-trigger $btn_class'>$label</button>";

	else
		$sc = "<a href='#' class='wpbo-trigger $btn_class'>$label</a>";		

	return $sc;

}