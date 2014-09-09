<?php
/**
 * Get plugin options.
 *
 * Get a plugin option or popup post meta.
 *
 * @since  1.0.0
 * @param  string   $option  ID of the required option
 * @param  mixed    $default Default value to return if option doesn't exist
 * @param  integer  $post_id Post ID (for retrieving post metas)
 * @return mixed             Value
 */
function wpbo_get_option( $option, $default = false, $post_id = null ) {

	/**
	 * Post Meta
	 */
	if( !is_null( $post_id ) ) {

		$settings = get_post_meta( $post_id, '_wpbo_settings', true );

		if( is_array( $settings ) && isset( $settings[$option] ) ) {

			$value = $settings[$option];

		} else {
			$value = $default;
		}

	}

	/**
	 * General options
	 */
	else {
	
		$options = maybe_unserialize( get_option( 'wpbo_options' ) );
		$value   = isset( $options[$option] ) ? $options[$option] : $default;

	}

	return apply_filters( 'wpbo_get_option' . $option, $value );

}

/**
 * Fallback for boolval used for PHP version
 * older than 5.0.0
 */
if( !function_exists( 'boolval' ) ) {
    /**
     * Get the boolean value of a variable
     *
     * @param mixed The scalar value being converted to a boolean.
     * @return boolean The boolean value of var.
     */
    function boolval( $var ) {
        return !! $var;
    }
}

/**
 * Dismiss a popup.
 *
 * Set a cookie to prevent a specific popup from showing up
 * on the site. This function was made for other plugins to
 * have an easy way to hide a popup if needed.
 *
 * @since  1.0.1
 * @param  integer $popup_id        ID of the popup to dismiss
 * @param  integer $cookie_lifetime Lifetime of the cookie in days
 * @return boolean                  Result of the cookie insertion
 */
function wpbo_dismiss_popup( $popup_id = false, $cookie_lifetime = 30 ) {

	if( false === $popup_id )
		return false;

	/* Set the cookie */
	return setcookie( 'wpbo_' . $popup_id, strtotime( date( 'Y-m-d H:i:s' ) ), time()+60*60*$cookie_lifetime, '/' );

}