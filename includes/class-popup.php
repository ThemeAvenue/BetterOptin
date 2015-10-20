<?php
/**
 * BetterOptin Popup Class
 *
 * @package   BetterOptin/Popup Class
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPBO_Popup {

	/**
	 * ID of the popup to work with
	 *
	 * @var int
	 * @since 2.0
	 */
	private $popup_id;

	private $settings;

	public function __construct( $popup_id = 0 ) {

		if ( self::popup_exists( $popup_id ) ) {
			$this->popup_id = (int) $popup_id;
			$this->get_settings();
		}

	}

	/**
	 * Check if a popup exists
	 *
	 * @since 2.0
	 *
	 * @param $popup_id
	 *
	 * @return bool
	 */
	public static function popup_exists( $popup_id ) {

		$post_type = get_post_type( $popup_id );

		return 'wpbo-popup' === $post_type ? true : false;

	}

	/**
	 * Check if the popup is currently active
	 *
	 * @since 2.0
	 * @return bool
	 */
	protected function is_popup_active() {

	}

	/**
	 * Get the popup settings
	 *
	 * @since 2.0
	 * @return void
	 */
	private function get_settings() {

		$this->settings = get_post_meta( $this->popup_id, '_wpbo_settings', true );

		if ( ! is_array( $this->settings ) ) {
			$this->settings = (array) $this->settings;
		}

	}

	/**
	 * Get popup option
	 *
	 * @since 2.0
	 *
	 * @param string $option  Popup option to get the value for
	 * @param mixed  $default Default option to return if the value doesn't exist
	 *
	 * @return mixed
	 */
	public function option( $option, $default = '' ) {
		return array_key_exists( $option, $this->settings ) ? $this->settings[ $option ] : $default;
	}

	/**
	 * Get field names allowed in popups
	 *
	 * @since 2.0
	 * @return array
	 */
	public function get_fields() {

		$fields = apply_filters( 'wpbo_wp_allowed_fields', array(
			'first_name' => 'sanitize_text_field',
			'last_name'  => 'sanitize_text_field',
			'email'      => 'sanitize_email',
			'wpbo_email' => 'sanitize_email',
			'wpbo_id'    => 'intval',
			'post_id'    => 'intval',
		) );

		return $fields;

	}

	/**
	 * Get the popup template
	 *
	 * @since 2.0
	 * @return string
	 */
	public function get_template() {

		$template = get_post_meta( $this->popup_id, 'wpbo_template', true );

		if ( empty( $template ) ) {
			return '';
		}

		$file     = $template . '.php';
		$filepath = WPBO_PATH . 'templates/' . $file;
		$output   = '';

		if ( file_exists( $filepath ) ) {

			/* Turn on buffering */
			ob_start();

			require( $filepath );

			/* Get the buffered content into a var */
			$output = ob_get_contents();

			/* Clean buffer */
			ob_end_clean();

		}

		return $output;

	}

	/**
	 * Get a popup markup.
	 *
	 * Retrieve the markup for a specific popup. Check if the popup
	 * was customized first, otherwise just load the default HTML file.
	 *
	 * @since  1.0.0
	 * @return string            HTML markup of the popup to display
	 */
	public function get_markup() {

		/* Check if the template was customized */
		if ( '' != ( $customized = get_post_meta( $this->popup_id, '_wpbo_template_display', true ) ) ) {

			if ( is_admin() ) {
				$output = html_entity_decode( get_post_meta( $this->popup_id, '_wpbo_template_editor', true ), ENT_COMPAT | ENT_HTML401, 'UTF-8' );
			} else {
				$output = html_entity_decode( $customized, ENT_COMPAT | ENT_HTML401, 'UTF-8' );
			}
		} else {
			$output = $this->get_template();
		}

		if ( ! is_admin() ) {

			global $post;

			/**
			 * Get the return URL and filter it
			 *
			 * @since  1.0.0
			 */
			$return_url = apply_filters( 'wpbo_return_url', $this->get_return_url(), $this->popup_id, $post->ID );

			/* Add the form */
			$output = sprintf( "<form role='form' class='optform' id='%s' action='%s' method='post'>\r\n", 'wpbo-popup-' . $this->popup_id, get_permalink( $post->ID ) ) . $output . "\r\n";

			/* Add all hidden fields */
			$output .= "\t" . wp_nonce_field( 'subscribe', 'wpbo_nonce', false, false ) . "\r\n";
			$output .= sprintf( "\t<input type='hidden' name='wpbo_id' id='wpbo_id' value='%s'>\r\n", $this->popup_id );
			$output .= sprintf( "\t<input type='hidden' name='post_id' id='post_id' value='%s'>\r\n", $post->ID );
			$output .= sprintf( "\t<input type='hidden' name='return_url' id='return_url' value='%s'>\r\n", $return_url );

			/**
			 * Fires right before the form is closed
			 *
			 * @since  1.0.0
			 * @var    int $popup_id ID of the popup to be triggered
			 * @var    int $post_id  ID of the post being viewed
			 */
			do_action( 'wpbo_popup_markup_after', $this->popup_id, $post->ID );

			/* Close the form */
			$output .= '</form>';

		}

		return $output;

	}

	/**
	 * Get popup return URL
	 *
	 * @since 2.0
	 * @return string
	 */
	private function get_return_url() {

		$returl = $this->option( 'return_url', '' );

		if ( empty( $returl ) ) {
			$returl = wpbo_get_option( 'return_url', home_url() );
		}

		return esc_url( $returl );

	}

	/**
	 * Get the rendered popup HTML markup
	 *
	 * @since  1.0.0
	 * @return string
	 */
	private function get_popup() {

		$output   = false;

		if ( false === $this->popup_id ) {
			return '';
		}

		/**
		 * wpbo_popup_output hook
		 *
		 * @since  1.0.0
		 */
		$output = apply_filters( 'wpbo_popup_output', $this->get_markup(), $this->popup_id );

		if ( false === $output ) {
			$output = "<!-- No template selected for popup #$this->popup_id -->";
		}

		/**
		 * wpbo_before_popup_form hook
		 *
		 * @since  1.0.0
		 */
		do_action( 'wpbo_before_popup_form', $this->popup_id );

		/* Echo the popup */
		$output = '<div class="wpbo wpbo-popup-' . $this->popup_id . '">' . $output . '</div>';

		/**
		 * wpbo_after_popup_form hook
		 *
		 * @since  1.0.0
		 */
		do_action( 'wpbo_after_popup_form', $this->popup_id );

		return $output;

	}

	public function popup() {
		echo $this->get_popup();
	}

	/**
	 * Get the number of impressions for this popup
	 *
	 * @since 2.0
	 * @return int
	 */
	public function get_impressions() {
		return (int) get_post_meta( $this->popup_id, 'wpbo_impressions', true );
	}

	/**
	 * Record popup impression.
	 *
	 * @since  1.0.0
	 * @return int|WP_Error
	 */
	public function new_impression() {

		/* Log the impression */
		$log = wpbo_db_insert_data( array(
			'popup_id'   => $this->popup_id,
			'data_type'  => 'impression',
			'ip_address' => wpbo_get_ip_address(),
			'referer'    => esc_url( $_SERVER['HTTP_REFERER'] ),
			'user_agent' => $_SERVER['HTTP_USER_AGENT']
		), true );

		return $log;

	}

	/**
	 * Log a new popup conversion
	 *
	 * @since 2.0
	 * @return int|WP_Error
	 */
	public function new_conversion() {

		$log = wpbo_db_insert_data( array(
			'popup_id'   => $this->popup_id,
			'data_type'  => 'conversion',
			'ip_address' => wpbo_get_ip_address(),
			'referer'    => esc_url( $_SERVER['HTTP_REFERER'] ),
			'user_agent' => $_SERVER['HTTP_USER_AGENT']
		), false );

		return $log;

	}

	/**
	 * Clean the post.
	 *
	 * Filter the post data and only keep
	 * values that are actually supported
	 * by the API.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data Data to sanitize
	 *
	 * @return array Clean list of merge fields
	 */
	protected function get_clean_fields( $data = array() ) {

		if ( empty( $data ) && ! empty( $_POST ) ) {
			$data = $_POST;
		}

		$fields = $this->get_fields();

		$clean = array();

		foreach ( $fields as $field => $sanitize ) {

			if ( ! function_exists( $sanitize ) ) {
				$sanitize = 'sanitize_text_field';
			}

			if ( isset( $data[ $field ] ) ) {
				$clean[ $field ] = call_user_func( $sanitize, $data[ $field ] );
			}

		}

		return $clean;

	}

	/**
	 * Trigger form submission.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function submit() {

		if ( ! wpbo_is_provider_ready() ) {
			return;
		}

		$data   = $this->get_clean_fields();
		$result = call_user_func( array( wpbo_get_provider_class(), 'submit' ), $data );

		if ( true === $result ) {

			// Dismiss the popup
			wpbo_dismiss_popup( $this->popup_id );

			// Log the conversion
			$this->new_conversion();

			// Redirect
			wp_redirect( $this->get_return_url() );
			exit;

		} else {
			/**
			 * Redirect error
			 */
		}

	}

	/**
	 * Shows a confirmation alert.
	 *
	 * This is only used if the used didn't set a custom
	 * thank you page.
	 *
	 * @since  1.0.0
	 */
	public function submission_confirmation_fallback() { ?>

		<script type="text/javascript">if(window.location.search.indexOf("wpbo_submit=done")>-1){alert("<?php esc_html_e( 'You have successfully registered!', 'betteroptin' ); ?>")}if(window.location.search.indexOf("wpbo_submit=fail")>-1){alert("<?php _e( 'Fail. Please try again.', 'wpbo' ); ?>")}</script>

	<?php }

}