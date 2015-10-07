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
	 * @param  integer $popup_id ID of the required popup
	 * @return string            HTML markup of the popup to display
	 */
	public function get_markup() {

		/* Check if the template was customized */
		if( '' != ( $customized = get_post_meta( $this->popup_id, '_wpbo_template_display', true ) ) ) {

			if( is_admin() ) {
				$output = html_entity_decode( get_post_meta( $this->popup_id, '_wpbo_template_editor', true ), ENT_COMPAT | ENT_HTML401, 'UTF-8' );
			} else {
				$output = html_entity_decode( $customized, ENT_COMPAT | ENT_HTML401, 'UTF-8' );
			}
		}

		/* Otherwise use the default template */
		else {
			$output = $this->get_template();
		}

		if( !is_admin() ) {

			global $post;

			/* Get return URL */
			if( '' != ( $custom_url = wpbo_get_option( 'return_url', '', $this->popup_id ) ) ) {
				$return_url = $custom_url;
			} elseif( ( '' != ( $default_url = wpbo_get_option( 'return_url', '' ) ) ) ) {
				$return_url = $default_url;
			} else {
				$return_url = false;
			}

			/**
			 * wpbo_return_url hook
			 *
			 * @since  1.0.0
			 */
			$return_url = apply_filters( 'wpbo_return_url', $return_url, $this->popup_id, $post->ID );

			/* Add the form */
			$output = sprintf( '<form role="form" class="optform" id="%s" action="%s" method="post">', 'wpbo-popup-' . $this->popup_id, get_permalink( $post->ID ) ) . $output;

			/* Add all hidden fields */
			$output .= wp_nonce_field( 'subscribe', 'wpbo_nonce', false, false );
			$output .= sprintf( '<input type="hidden" name="wpbo_id" id="wpbo_id" value="%s">', self::get_popup_id() );
			$output .= sprintf( '<input type="hidden" name="post_id" id="post_id" value="%s">', $post->ID );

			if ( false !== $return_url ) {
				$output .= sprintf( '<input type="hidden" name="return_url" id="return_url" value="%s">', $return_url );
			}

			/**
			 * wpbo_popup_hidden_fields hook
			 *
			 * @since  1.0.0
			 * @var    $popup_id ID of the popup to be triggered
			 * @var    $post->ID ID of the post being veiwed
			 */
			do_action( 'wpbo_popup_hidden_fields', $popup_id, $post->ID );

			/* Close the form */
			$output .= '</form>';

		}

		return $output;

	}

	/**
	 * Render popup markup.
	 *
	 * @since  1.0.0
	 */
	public function popup() {

		$output   = false;

		if ( false === $this->popup_id ) {
			return;
		}

		/**
		 * wpbo_popup_output hook
		 *
		 * @since  1.0.0
		 */
		$output = apply_filters( 'wpbo_popup_output', $this->get_markup(), $this->popup_id );

		if ( false === $output ) {
			echo "<!-- No template selected for popup #$this->popup_id -->";

			return false;
		}

		/**
		 * wpbo_before_popup_form hook
		 *
		 * @since  1.0.0
		 */
		do_action( 'wpbo_before_popup_form', $this->popup_id );

		/* Echo the popup */
		echo '<div class="wpbo wpbo-popup-' . $this->popup_id . '">' . $output . '</div>';

		/**
		 * wpbo_after_popup_form hook
		 *
		 * @since  1.0.0
		 */
		do_action( 'wpbo_after_popup_form', $this->popup_id );

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
	 * @return integer Total number of impressions
	 */
	public function new_impression() {

		$post_id = intval( $_POST['popup_id'] );
		$prev    = $this->get_impressions();
		$new     = ++ $prev;

		/* Log the impression */
		wpbo_db_insert_data( array(
			'popup_id'   => $post_id,
			'data_type'  => 'impression',
			'ip_address' => wpbo_get_ip_address(),
			'referer'    => esc_url( $_SERVER['HTTP_REFERER'] ),
			'user_agent' => $_SERVER['HTTP_USER_AGENT']
		), true );

		update_post_meta( $this->popup_id, 'wpbo_impressions', $new, $prev );

		return $new;

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

		<script type="text/javascript">if(window.location.search.indexOf("wpbo_submit=done")>-1){alert("<?php _e( 'You have successfully registered!', 'wpbo' ); ?>")}if(window.location.search.indexOf("wpbo_submit=fail")>-1){alert("<?php _e( 'Fail. Please try again.', 'wpbo' ); ?>")}</script>

	<?php }

}