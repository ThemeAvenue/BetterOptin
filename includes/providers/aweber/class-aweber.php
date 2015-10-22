<?php
/**
 * BetterOptin Provider Aweber
 *
 * @package   BetterOptin/Provider/Aweber/Settingd
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPBO_Aweber {

	/**
	 * Aweber authorization code
	 *
	 * @since 2.0
	 * @var string
	 */
	private $auth_code;

	/**
	 * Aweber API key
	 *
	 * @since 2.0
	 * @var string
	 */
	private $api_key;

	/**
	 * Aweber API secret key
	 *
	 * @since 2.0
	 * @var string
	 */
	private $api_secret;

	/**
	 * Aweber app access token
	 *
	 * @since 2.0
	 * @var string
	 */
	private $access_token = '';

	/**
	 * Aweber app access secret
	 *
	 * @since 2.0
	 * @var string
	 */
	private $access_secret = '';

	/**
	 * Aweber registered app ID
	 *
	 * @since 2.0
	 * @var string
	 */
	public static $app_id = '0cdbc51e';

	/**
	 * Aweber list ID
	 *
	 * @since 2.0
	 * @var string
	 */
	private $list_id;

	/**
	 * Aweber app authorize URL
	 *
	 * @since 2.0
	 * @var string
	 */
	public static $authorize_url = 'https://auth.aweber.com/1.0/oauth/authorize_app/';

	/**
	 * Instance of the Aweber class
	 *
	 * @since 2.0
	 * @var AWeberAPI
	 */
	private $aweber;

	/**
	 * Aweber user account
	 *
	 * @since 2.0
	 * @var
	 */
	private $account;

	/**
	 * Holds possible errors if the Aweber class can't be instantiated
	 *
	 * @since 2.0
	 * @var null|WP_Error
	 */
	public $error;

	public function __construct() {

		if ( ! class_exists( 'AWeberAPI' ) ) {
			require( WPBO_PATH . 'vendor/aweber/aweber/aweber_api/aweber.php' );
		}

		$this->auth_code  = trim( wpbo_get_option( 'aw_auth_code', '' ) );
		$this->api_key    = false !== $this->get_credentials() ? $this->get_credentials()['consumerKey'] : '';
		$this->api_secret = false !== $this->get_credentials() ? $this->get_credentials()['consumerSecret'] : '';
		$this->list_id    = wpbo_get_option( 'aw_list_id', '' );
		$this->get_tokens();

	}

	public static function get_authorization_url() {
		return self::$authorize_url . self::$app_id;
	}

	/**
	 * Get Aweber authorization tokens
	 *
	 * Try and get the tokens from the database. If no token is found, we query Aweber and request
	 * authorization.
	 *
	 * @since 1.0
	 * @return bool|WP_Error True if the auth tokens are set, WP_Error otherwise
	 */
	private function get_tokens() {

		$tokens = maybe_unserialize( get_option( 'wpbo_aweber_tokens' ) );

		if ( empty( $tokens ) || ! is_array( $tokens ) ) {
			$tokens = $this->get_access_tokens();
		}

		if ( is_wp_error( $tokens ) ) {
			return $tokens;
		}

		$this->access_token  = isset( $tokens[0] ) ? trim( $tokens[0] ) : '';
		$this->access_secret = isset( $tokens[1] ) ? trim( $tokens[1] ) : '';

		return true;

	}

	/**
	 * Get access tokens from Aweber.
	 *
	 * @return array Aweber tokens
	 * @since  1.0.0
	 */
	protected function get_access_tokens() {

		if ( is_wp_error( $this->aweber() ) ) {
			return $this->aweber();
		}

		/* Get credentials from Aweber key */
		$credentials = $this->get_credentials();

		if ( false === $credentials ) {
			return $this->error = new WP_Error( 'aweber_missing_auth_code', esc_html__( 'Aweber authorization code is missing', 'betteroptin' ) );
		}

		/* Set tokens */
		$this->aweber()->adapter->user->requestToken = $credentials['requestToken'];
		$this->aweber()->adapter->user->tokenSecret  = $credentials['tokenSecret'];
		$this->aweber()->adapter->user->verifier     = $credentials['verifier'];

		/* Request access tokens */
		try {

			$access_tokens = $this->aweber()->getAccessToken();
			$this->access_token  = $access_tokens[0];
			$this->access_secret = $access_tokens[1];

			/* Save access tokens */
			update_option( 'wpbo_aweber_tokens', $access_tokens );

			return $access_tokens;

		} catch ( Exception $e ) {
			return $this->error = new WP_Error( '', esc_html__( 'The Aweber authorization code you provided is incorrect or expired. Please authorize the plugin again.', 'betteroptin' ) );
		}

	}

	/**
	 * Get the instance of the Aweber class
	 *
	 * @since 2.0
	 * @return AWeberAPI|WP_Error
	 */
	public function aweber() {

		if ( is_object( $this->aweber ) && is_a( $this->aweber, 'AWeberAPI' ) ) {
			return $this->aweber;
		}

		/* Make sure we have the tokens */
		if ( empty( $this->api_key ) || empty( $this->api_secret ) ) {
			return $this->error = new WP_Error( 'missing_tokens', __( 'Aweber API keys are missing.', 'betteroptin' ) );
		}

		return $this->aweber = new AWeberAPI( $this->api_key, $this->api_secret );

	}

	/**
	 * Get an account instance.
	 *
	 * @return object User account instance
	 */
	public function account() {

		if ( ! is_null( $this->account ) ) {
			return $this->account;
		}

		/* Get user account */
		try {
			$this->account = $this->aweber()->getAccount( $this->access_token, $this->access_secret );
		} catch ( Exception $e ) {
			return $this->error = new WP_Error( 'aweber_connection_error', __( 'Unable to connect to Aweber', 'betteroptin' ) );
		}

		return $this->account;

	}

	/**
	 * Get account credentials.
	 *
	 * Takes the authorization code and extract account credentials.
	 *
	 * @since  1.0.0
	 * @return array Credentials
	 */
	protected function get_credentials() {

		if ( empty( $this->auth_code ) ) {
			return false;
		}

		$code = explode( '|', $this->auth_code );

		$credentials = array(
			'consumerKey'    => trim( $code[0] ),
			'consumerSecret' => trim( $code[1] ),
			'requestToken'   => trim( $code[2] ),
			'tokenSecret'    => trim( $code[3] ),
			'verifier'       => trim( $code[4] ),
		);

		return $credentials;

	}

	/**
	 * Subscribe the visitor to a list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data Form post data
	 *
	 * @return array Result
	 */
	public function subscribe( $data ) {

		if ( is_wp_error( $this->account() ) ) {
			return $this->account();
		}

		$account_id  = $this->account()->data['id'];
		$list_custom = get_post_meta( (int) $data['wpbo_id'], 'wpbo_aw_list', true );
		$list_id     = ( '' != $list_custom ) ? $list_custom : $this->list_id;
		$list_url    = "/accounts/$account_id/lists/$list_id";
		$subscriber  = array( 'email' => sanitize_email( $data['wpbo_email'] ), 'name' => $data['wpbo_name'] );

		/* Subscribe the new user */
		try {

			$list          = $this->account()->loadFromUrl( $list_url );
			$newSubscriber = $list->subscribers->create( $subscriber );

			return true;

		} catch ( Exception $exc ) {
			return false;
		}

	}

	/**
	 * Get user lists.
	 *
	 * @since  1.0.0
	 * @return array Array of available lists for this account
	 */
	public function get_lists() {

		$lists = maybe_unserialize( get_transient( 'wpbo_aw_lists' ) );

		if ( is_array( $lists ) ) {
			return $lists;
		}

		if ( is_wp_error( $this->account() ) ) {
			return array();
		}

		foreach ( $this->account()->lists as $list ) {
			$lists[] = array( 'id' => $list->id, 'name' => $list->name );
		}

		/* Cache the lists to avoid slow loading */
		set_transient( 'wpbo_aw_lists', $lists, 24 * 60 * 60 );

		return $lists;

	}

	public function is_error() {
		return $this->error;
	}

}