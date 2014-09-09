<?php
/**
 * Better Optin Form Submission.
 *
 * @package   Better_Optin
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

class WPBO_Submit {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance of the provider class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	public static $provider_inst = null;

	/**
	 * E-Mailing Providers.
	 *
	 * @since  1.0.0
	 * @var    array
	 */
	protected static $providers = array();

	public function __construct() {

		/* Load the provider class */
		add_action( 'init', array( $this, 'load_provider_class' ), 9 );

		/* Initialize the submission related actions */
		if( $this->is_submission() ) {

			$this->popup_id = intval( $_POST['wpbo_id'] );
			$this->post_id  = intval( $_POST['post_id'] );

			add_action( 'init', array( $this, 'init' ), 9 );

		}

	}

	public function load_provider_class() {

		/* Get user mailing provider */
		$mailer = $this->get_provider_class( $this->get_provider() );

		/* If we have a provider class */
		if( false !== $mailer && class_exists( $mailer ) ) {

			/* Let the addon do the work */
			self::$provider_inst = new $mailer( true );

		}

	}

	public function init() {

		global $wpbo_analytics;

		$popup_id    = intval( $_POST['wpbo_id'] );
		$options     = get_post_meta( $popup_id, '_wpbo_settings', true );
		$cookie_life = isset( $options['cookie_lifetime'] ) ? intval( $options['cookie_lifetime'] ) : 30;

		/* Yay! We have a submission! */
		if( $this->is_submission() ) {

			/* Log the convertion */
			$log = $wpbo_analytics->insert_data( array( 'popup_id' => $popup_id, 'data_type' => 'conversion', 'ip_address' => Better_Optin::get_ip_address(), 'referer' => esc_url( $_SERVER['HTTP_REFERER'] ), 'user_agent' => $_SERVER['HTTP_USER_AGENT'] ), false );

			/* Set the cookie */
			$cookie = setcookie( 'wpbo_' . $popup_id, strtotime( date( 'Y-m-d H:i:s' ) ), time()+60*60*$cookie_life, '/' );

		}

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Get available providers.
	 *
	 * @since  1.0.0
	 * @return array Filtered list of providers
	 */
	public function get_provider() {

		$options = maybe_unserialize( get_option( 'wpbo_options', array() ) );
		
		if( isset( $options['mailing_provider'] ) )
			return $options['mailing_provider'];

		else
			return false;
	}

	public function providers() {
		$providers = apply_filters( 'wpbo_registered_providers', array() );
		return $providers;
	}

	public function get_provider_class( $provider ) {

		$providers = $this->providers();

		if( isset( $providers[$provider] ) )
			return $providers[$provider];

		else
			return false;

	}

	/**
	 * Check if provider is ready.
	 *
	 * Addons should use the wpbo_provider_ready filter to return
	 * true or false whether or not the provider settings have been set.
	 * 
	 * @return boolean Readiness.
	 */
	public function is_ready() {
		return apply_filters( 'wpbo_provider_ready', false );
	}

	public function is_valid_submission() {

		if( isset( $_POST['wpbo_nonce'] ) || wp_verify_nonce( $_POST['wpbo_nonce'], 'subscribe' ) )
			return true;

		else
			return false;

	}

	public function is_submission() {

		$submission = false;

		if( isset( $_POST['wpbo_nonce'] ) && true === $this->is_valid_submission() && isset( $_POST['wpbo_email'] ) && isset( $_POST['wpbo_id'] ) )
			return true;

		else
			return false;

	}

	/**
	 * Get the return URL.
	 *
	 * @todo   add support for failed submissions
	 * @param  string $case The submission status
	 * @return string       URL to redirect to
	 */
	public function get_return_url( $case = 'success' ) {

		$field = ( 'success' == $case ) ? 'return_url' : 'return_url';

		if( isset( $_POST[$field] ) && '' != $_POST[$field] ) {
			$url = esc_url( $_POST[$field] );
		} elseif( '' != ( $custom_url = wpbo_get_option( $field, '', $this->popup_id ) ) ) {
			$url = $custom_url;
		} elseif( ( '' != ( $default_url = wpbo_get_option( $field, '' ) ) ) ) {
			$url = $default_url;
		} else {
			$status = ( 'success' == $case ) ? 'done' : 'fail';
			$url    = add_query_arg( array( 'wpbo_submit' => $status ),  esc_url( get_permalink( $this->post_id ) ) );
		}

		return $url;

	}

}