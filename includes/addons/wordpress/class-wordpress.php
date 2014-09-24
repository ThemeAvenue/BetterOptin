<?php
class WPBO_WordPress extends WPBO_Submit {

	public function __construct( $main = false ) {

		global $post;
		
		/**
		 * Set vars
		 */
		$options          = maybe_unserialize( get_option( 'wpbo_options', array() ) );
		$this->role       = isset( $options['wp_default_role'] ) ? $options['wp_default_role'] : 'betteroptin';
		$this->password   = false;

		/* Add direct link to leads. */
		add_action( 'admin_menu', array( $this, 'add_leads_menu' ), 9 );

		if ( !is_admin() && $this->is_submission() ) {
			add_action( 'init', array( $this, 'submit' ) );
		}

	}

	/**
	 * Register the new provider.
	 *
	 * @since  1.0.0
	 */
	public static function register() {
		add_filter( 'wpbo_mailing_providers', array( 'WPBO_WordPress', 'provider' ) );
		add_filter( 'wpbo_plugin_settings', array( 'WPBO_WordPress', 'settings' ) );
		add_filter( 'wpbo_registered_providers', array( 'WPBO_WordPress', 'provider_class' ) );
	}

	/**
	 * Add link to leads.
	 *
	 * Add a direct link to the list of leads
	 * collected by BetterOptin.
	 *
	 * @since    1.2.1
	 */
	public function add_leads_menu() {

		$role        = wpbo_get_option( 'wp_default_role' );
		$page        = add_query_arg( array( 'role' => $role ), 'users.php' );
		$this->leads = add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Leads', 'wpbo' ), __( 'Leads', 'wpbo' ), 'administrator', $page );

	}

	/**
	 * Trigger form submission.
	 *
	 * @since  1.0.0
	 * @return null
	 */
	public function submit() {

		/* Generate a random password */
		$this->password = wp_generate_password();

		/* Subscribe the new user */
		$user = $this->subscribe();

		/* Insertion is successfull */
		if( !is_wp_error( $user ) ) {

			/* Notify admin and user */
			wp_new_user_notification( $user, $this->password );

			/* Add a marker in user meta */
			add_user_meta( $user, 'wpbo_subscription', 'yes', true );

			$return_url = $this->get_return_url();

		} else {

			$return_url = $this->get_return_url( 'fail' );

		}

		/* Redirect */
		wp_redirect( $return_url );
		exit;

	}

	/**
	 * Add new provider.
	 *
	 * Add MailChimp as a provider in the plugin general settings.
	 *
	 * @since  1.0.0
	 * @param  array $providers List of available providers
	 * @return array            Updated list of providers containing MailChimp
	 */
	public static function provider( $providers ) {
		$providers['wordpress'] = 'WordPress';
		return $providers;
	}

	/**
	 * Register the provider and its class name.
	 *
	 * @since  1.0.0
	 * @param  array $providers Old list of providers
	 * @return array            All registered providers
	 */
	public static function provider_class( $providers ) {
		$providers['wordpress'] = 'WPBO_WordPress';
		return $providers;
	}

	/**
	 * Addon settings.
	 *
	 * Add new settings to the plugin settings page.
	 *
	 * @since  1.0.0
	 * @param  array $settings Pre-existing settings
	 * @return array           Updated settings containing MailChimp options
	 */
	public static function settings( $settings ) {

		$roles = array();

		if( !function_exists( 'get_editable_roles' ) )
			require_once( ABSPATH . '/wp-admin/includes/user.php' );

		$editable_roles = get_editable_roles();

		if( is_array( $editable_roles ) && count( $editable_roles ) >= 1 ) {

			foreach( $editable_roles as $role_name => $role_info ) {
				$roles[$role_name] = $role_info['name'];
			}

		}

		$settings['wordpress'] = array(
			'name'    => __( 'WordPress', 'wpbo' ),
			'options' => array(
				array(
					'name'    => __( 'Default Role', 'wpbo' ),
					'id'      => 'wp_default_role',
					'type'    => 'select',
					'default' => 'betteroptin',
					'desc'    => __( 'Role attributed to all subscribers', 'wpbo' ),
					'options' => $roles
				),
				array(
					'name' => __( 'Uninstallation', 'wpbo' ),
					'type' => 'heading',
				),
				array(
					// 'name' => __( 'Uninstallation', 'wpbo' ),
					'type' => 'note',
					'desc' => sprintf( __( 'When the plugin is uninstalled (deleted from your site, not just deactivated), how shall we deal with the subscribers saved in your database? Please <a href="%s" target="_blank">read the documentation</a> for a full understanding of what you\'re doing.', 'wpbo' ), esc_url( 'http://themeavenue.net' ) )
				),
				array(
					'name'    => __( 'Delete Subscriber', 'wpbo' ),
					'id'      => 'wp_delete_users_uninstall',
					'type'    => 'checkbox',
					'default' => false,
					'desc'    => __( 'YES, DELETE subscribers from my database. Can NOT be undone.', 'wpbo' ),
				),
				array(
					'name'    => __( 'Delete Marker', 'wpbo' ),
					'id'      => 'wp_delete_users_marker',
					'type'    => 'checkbox',
					'default' => true,
					'desc'    => __( 'Delete the markers. You will not be able to differentiate the subscribers from the rest of your users.', 'wpbo' ),
				),
			),
		);

		return $settings;

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

		$fields = apply_filters( 'wpbo_wp_allowed_fields', array( 'first_name' ) );
		$clean  = array();

		/* Need to use a merge var for the name */
		if( isset( $_POST['wpbo_name'] ) )
			$_POST['first_name'] = $_POST['wpbo_name'];

		foreach( $fields as $field ) {

			if( isset( $_POST[$field] ) )
				$clean[$field] = $_POST[$field];

		}

		return $clean;

	}

	/**
	 * Subscribe the visitor to a list.
	 *
	 * @since  1.0.0
	 * @return array Result
	 */
	protected function subscribe() {

		$extra = $this->clean_fields();

		$args = array(
			'user_email'   => sanitize_email( $_POST['wpbo_email'] ),
			'user_login'   => sanitize_email( $_POST['wpbo_email'] ),
			'first_name'   => $extra['first_name'],
			'display_name' => $extra['first_name'],
			'user_pass'    => md5( $this->password ),
			'role'         => $this->role
		);

		$user = wp_insert_user( $args );

		return $user;

	}

}