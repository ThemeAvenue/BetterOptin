<?php
/**
 * BetterOptin Provider MailChimp
 *
 * @package   BetterOptin/Provider/MailChimp
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2015 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

final class WPBO_MailChimp {

	/**
	 * @var WPBO_MailChimp $instance Holds the unique instance of the MailChimp provider
	 * @since 2.0
	 */
	private static $instance;

	/**
	 * @var string MailChimp API key
	 * @since 1.0
	 */
	private static $api_key;

	/**
	 * @var string MailChimp list ID
	 * @since 1.0
	 */
	private static $list_id;

	/**
	 * @var bool Whether or not to use double optin for new subscribers
	 * @since 1.0
	 */
	private static $double_optin;

	/**
	 * @var bool Whether to update existing contacts or add new
	 * @since 1.0
	 */
	private static $update;

	/**
	 * @var bool Whether or not to send the welcome message to new subscribers
	 * @since 1.0
	 */
	private static $welcome;

	/**
	 * @var array A list of allowed fields to be passed to MailChimp
	 * @since 1.0
	 */
	private static $fields;

	/**
	 * Instantiate and return the unique BetterOptin object
	 *
	 * @since     2.0
	 * @return object BetterOptin Unique instance of BetterOptin
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPBO_MailChimp ) ) {

			// Instantiate
			self::$instance = new WPBO_MailChimp;
			self::$instance->setup_options();
			self::$instance->load_mailchimp_api_wrapper();

		}

		return self::$instance;

	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 2.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpas' ), '2.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 2.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpas' ), '2.0' );
	}

	/**
	 * Setup MailChimp options
	 *
	 * @since 2.0
	 * @return void
	 */
	private function setup_options() {
		self::$api_key      = wpbo_get_option( 'mc_api_key', '' );
		self::$list_id      = wpbo_get_option( 'mc_list_id', '' );
		self::$double_optin = wpbo_get_option( 'mc_double_optin', true );
		self::$update       = wpbo_get_option( 'mc_update_existing', true );
		self::$welcome      = wpbo_get_option( 'mc_welcome', true );
		self::$fields       = apply_filters( 'wpbo_mc_merge_vars', array( 'FNAME' ) );
	}

	/**
	 * Load the MailChimp API wrapper
	 *
	 * @since 1.0
	 * @return void
	 */
	private function load_mailchimp_api_wrapper() {
		/**
		 * Load MailChimp API Wrapper
		 *
		 * @link https://bitbucket.org/mailchimp/mailchimp-api-php
		 */
		if ( ! class_exists( 'Drewm\MailChimp' ) ) {
			require( WPBO_PATH . 'vendor/drewm/mailchimp-api/src/Drewm/MailChimp.php' );
		}
	}

	/**
	 * Trigger form submission.
	 *
	 * Add a last couple of checks, set the redirects and
	 * finally subscribe the visitor to the MailChimp list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data Popup form data
	 *
	 * @return null
	 */
	public function submit( $data ) {

		/* Check for credentials */
		if ( empty( self::$api_key ) || empty( self::$list_id ) ) {
			return new WP_Error( 'missing_credentials', esc_html__( 'The MailChimp credentials are missing. Please verify your settings.', 'betteroptin' ) );
		}

		/* Do the subscription */
		$subscribe = $this->subscribe( $data );

		if ( isset( $subscribe['status'] ) && 'error' == $subscribe['status'] || false === $subscribe ) {
			return new WP_Error( 'missing_credentials', esc_html__( 'An error occurred during submission.', 'betteroptin' ) );
		}

		return true;

	}


	/**
	 * Check if MailChimp settings are correct.
	 *
	 * @since  1.0.0
	 * @return boolean True if MailChimp integration is ready to work
	 */
	public function is_mailchimp_ready() {

		if ( empty( self::$api_key ) || empty( self::$list_id ) ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Clean the post.
	 *
	 * Filter the post data and only keep
	 * values that are actually supported
	 * by the API.
	 *
	 * @since  1.0.0
	 * @return array Clean list of merge fields
	 */
	protected function clean_fields() {

		$fields = self::$fields;
		$clean  = array();

		/* Need to use a merge var for the name */
		if ( isset( $_POST['wpbo_name'] ) ) {
			$_POST['FNAME'] = $_POST['wpbo_name'];
		}

		foreach ( $fields as $field ) {

			if ( isset( $_POST[ $field ] ) ) {
				$clean[ $field ] = $_POST[ $field ];
			}

		}

		return $clean;

	}

	/**
	 * Subscribe the visitor to a list.
	 *
	 * @since  1.0.0
	 *
	 * @param array $data Popup form data
	 *
	 * @return array Result
	 */
	private function subscribe( $data ) {

		$popup_id    = (int) $data['wpbo_id'];
		$custom_list = get_post_meta( $popup_id, 'wpbo_mc_list', true );
		$list_id     = '' != $custom_list ? $custom_list : self::$list_id;

		// Prepare e-mail content
		$email = array(
			'email' => sanitize_email( $data['email'] ),
			'euid'  => md5( sanitize_email( $data['email'] ) ),
			'leid'  => false
		);

		// Get cleaned merge fields
		$merge_vars = $this->clean_fields();

		// MailChimp e-mail array
		$settings = array(
			'id'                => $list_id,
			'email'             => $email,
			'merge_vars'        => $merge_vars,
			'email_type'        => 'html',
			'double_optin'      => self::$double_optin,
			'update_existing'   => self::$update,
			'replace_interests' => false,
			'send_welcome'      => self::$welcome,
		);

		/* Instantiate the MailChimp API Wrapper */
		$mc = new \Drewm\MailChimp( self::$api_key );

		/**
		 * Get the groups.
		 */
		$groups = maybe_unserialize( get_post_meta( $popup_id, 'wpbo_mc_list_groups', true ) );

		if ( is_array( $groups ) ) {

			/* Declare the grouping array */
			if ( ! isset( $settings['merge_vars']['groupings'] ) ) {
				$settings['merge_vars']['groupings'] = array();
			}

			foreach ( $groups as $group => $option ) {

				if ( is_array( $option ) ) {

					$opts = array();

					foreach ( $option as $opt ) {
						array_push( $opts, $opt );
					}

					array_push( $settings['merge_vars']['groupings'], array( 'id' => $group, 'groups' => $opts ) );

				} else {
					array_push( $settings['merge_vars']['groupings'], array(
						'id'     => $group,
						'groups' => array( $option )
					) );
				}

			}

		}

		/* Call the API */
		$subscribe = $mc->call( 'lists/subscribe', $settings );

		return $subscribe;

	}

	/**
	 * Get user lists.
	 *
	 * @since  1.0.0
	 * @return array Array of available lists for this account
	 */
	public function get_lists() {

		if ( empty( self::$api_key ) ) {
			return array();
		}

		/* Instantiate */
		$mc = new \Drewm\MailChimp( self::$api_key );

		$lists = $mc->call( 'lists/list' );

		return $lists;

	}

	/**
	 * Get the groups list.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $list_id ID of the list to get the groups of
	 *
	 * @return mixed            Array of groups or error
	 */
	public function get_groups( $list_id = '' ) {

		/* Verify the list ID and try to retrieve it if needed. */
		if ( empty( $list_id ) ) {
			return new WP_Error( 'list_id_missing', __( 'The list ID is missing.', 'wpmc' ) );
		}

		/* Try to get groups from cache */
		$groups = get_transient( "wpbo_mc_groups_list_$list_id" );

		if ( false !== $groups ) {
			return $groups;
		}

		/* Check if the credentials are set. */
		if ( empty( self::$api_key ) ) {
			return new WP_Error( 'api_credentials_missing', __( 'The API credentials are missing.', 'wpmc' ) );
		}

		/* Instanciate MailChimp API Wrapper. */
		$mc = new \Drewm\MailChimp( self::$api_key );

		/* Get the lists. */
		$groups = $mc->call( 'lists/interest-groupings', array(
				'id' => $list_id
			)
		);

		/* An error occurred during the request, thus no groups. */
		if ( isset( $groups['status'] ) && 'error' === $groups['status'] && isset( $groups['error'] ) ) {
			return new WP_Error( 'api_credentials_missing', $groups['error'] );
		} else {

			/* Set a transient to reduce load time */
			$lifetime = apply_filters( 'wpbo_mc_list_groups_cache_lifetime', 24 * 60 * 60 );
			set_transient( "wpbo_mc_groups_list_$list_id", $groups, $lifetime );

			/* Return the groups */

			return $groups;

		}

	}

	/**
	 * Check if a list has groups.
	 *
	 * Use the MailChimp API to check if a given mailing list
	 * has groups available.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $list_id The list ID
	 *
	 * @return boolean          Request result
	 */
	public function has_groups( $list_id = '' ) {

		/* Verify the list ID and try to retrieve it if needed. */
		if ( empty( $list_id ) ) {
			return new WP_Error( 'list_id_missing', __( 'The list ID is missing.', 'wpmc' ) );
		}

		/* Get the groups. */
		$groups = $this->get_groups( $list_id );

		if ( is_wp_error( $groups ) || false === $groups || is_array( $groups ) && empty( $groups ) ) {
			return false;
		} else {
			return true;
		}

	}

}