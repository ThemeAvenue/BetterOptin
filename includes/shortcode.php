<?php
add_shortcode( 'wpbo_popup', 'wpbo_trigger_popup' );
/**
 * Manually triggers a popup.
 *
 * @since  1.0.1
 *
 * @param  array $atts Shortcode attributes
 *
 * @return string      HTML link
 */
function wpbo_trigger_popup( $atts = array() ) {

	$defaults = array(
		'popup_id'      => wpbo_page_has_popup(),
		'type'          => 'button',
		'label'         => __( 'Show Popup', 'betteroptin' ),
		'bypass_cookie' => true,
		'btn_class'     => ''
	);

	$atts     = shortcode_atts( $defaults, $atts );
	$popup_id = (int) $atts['popup_id'];

	/* No popup ID? Bye bye... */
	if ( false === $atts['popup_id'] ) {
		return false;
	}

	/* Do NOT bypass the cookie */
	if ( isset( $_COOKIE["wpbo_$popup_id"] ) && false === (bool) $atts['bypass_cookie'] ) {
		return false;
	}

	if ( 'button' == $atts['type'] ) {
		$sc = "<button class='wpbo-trigger {$atts['btn_class']}'>{$atts['label']}</button>";
	} else {
		$sc = "<a href='#' class='wpbo-trigger {$atts['btn_class']}'>{$atts['label']}</a>";
	}

	return $sc;

}