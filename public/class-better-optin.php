<?php
/**
 * Better Optin.
 *
 * @package   Better_Optin
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 */
class Better_Optin {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.2.1';

	/**
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'better-optin';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Register post type
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Add popup settings and markup
		add_action( 'wp_head', array( $this, 'popup_settings' ) );
		add_action( 'wp_footer', array( $this, 'popup' ) );
		add_action( 'wp_footer', array( $this, 'submission_confirmation' ), 999 );

		/* Check posts associations */
		add_action( 'wp_ajax_wpbo_new_impression',  array( $this, 'new_impression' ) );
		add_action( 'wp_ajax_nopriv_wpbo_new_impression',  array( $this, 'new_impression' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		
		/* Add new role */
		$subscriber = get_role( 'subscriber' );
		add_role( 'betteroptin', 'BetterOptin', $subscriber->capabilities );

		/* Create database table */
		WPBO_Analytics::create_table();

		/* Write database version */
		update_option( 'wpbo_db_version', WPBO_Analytics::$db_version );

		/**
         * Add an option in DB to know when the plugin has just been activated.
         *
         * @link http://stackoverflow.com/questions/7738953/is-there-a-way-to-determine-if-a-wordpress-plugin-is-just-installed/13927297#13927297
         */
        add_option( 'wpbo_just_activated', true );

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Load the plugin addons.
	 * 
	 * @return void
	 * @since  1.2.0
	 */
	public static function load_addons() {

		/* The WordPress addon is build-in the core plugin */
		$load = array(
			'WPBO_WordPress' => str_replace( array( '/public', '\public' ), '', plugin_dir_path( __FILE__ ) ) . 'includes/addons/wordpress/class-wordpress.php'
		);

		/* Get all the addons to load */
		$addons = apply_filters( 'wpbo_addons', $load );

		if ( is_array( $addons ) ) {

			foreach ( $addons as $name => $path ) {

				if ( file_exists( $path ) ) {

					/* Load the main class */
					require_once( $path );

					/* Register the settings */
					if ( class_exists( $name ) ) {
						$name::register();
					}
				}

			}

		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if( !$this->is_popup_available() )
			return;

		wp_enqueue_style( $this->plugin_slug . '-main', WPBO_URL . 'public/assets/css/betteroptin.css', array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if( !$this->is_popup_available() )
			return;

		$settings = get_post_meta( $this->is_popup_available(), '_wpbo_settings', true );

		/**
		 * Use booleans
		 */
		$settings['close_overlay']   = isset( $settings['close_overlay'] ) ? true : false;
		$settings['close_esc']       = isset( $settings['close_esc'] ) ? true : false;
		$settings['wiggle']          = isset( $settings['wiggle'] ) ? true : false;
		$settings['cookie_lifetime'] = isset( $settings['cookie_lifetime'] ) ? intval( $settings['cookie_lifetime'] ) : 30;

		/**
		 * Add popup ID
		 */
		$settings['popup_id'] = $this->is_popup_available();

		/**
		 * Define Ajax URL
		 */
		$settings['ajaxurl'] = admin_url( 'admin-ajax.php' );

		wp_enqueue_script( $this->plugin_slug . '-script', WPBO_URL . 'public/assets/js/betterOptin.min.js', array( 'jquery' ), self::VERSION );
		wp_localize_script( $this->plugin_slug . '-script', 'wpbo', json_encode( $settings ) );
	}

	/**
	 * Shows a confirmation alert.
	 *
	 * This is only used if the used didn't set a custom
	 * thank you page.
	 *
	 * @since  1.0.0
	 */
	public function submission_confirmation() { ?>

	<script type="text/javascript">if(window.location.search.indexOf("wpbo_submit=done")>-1){alert("<?php _e( 'You have successfully registered!', 'wpbo' ); ?>")}if(window.location.search.indexOf("wpbo_submit=fail")>-1){alert("<?php _e( 'Fail. Please try again.', 'wpbo' ); ?>")}</script>

	<?php }

	/**
	 * Register the popups post type
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		/* Set menu icon */
		$icon = 'dashicons-share-alt2';

		$labels = array(
			'name'               => _x( 'Popups', 'post type general name', 'better-optin' ),
			'singular_name'      => _x( 'Popup', 'post type singular name', 'better-optin' ),
			'menu_name'          => _x( 'Popups', 'admin menu', 'better-optin' ),
			'name_admin_bar'     => _x( 'Popup', 'add new on admin bar', 'better-optin' ),
			'add_new'            => _x( 'Add New', 'book', 'better-optin' ),
			'add_new_item'       => __( 'Add New Popup', 'better-optin' ),
			'new_item'           => __( 'New Popup', 'better-optin' ),
			'edit_item'          => __( 'Edit Popup', 'better-optin' ),
			'view_item'          => __( 'View Popup', 'better-optin' ),
			'all_items'          => __( 'All Popups', 'better-optin' ),
			'search_items'       => __( 'Search Popups', 'better-optin' ),
			'parent_item_colon'  => __( 'Parent Popups:', 'better-optin' ),
			'not_found'          => __( 'No popup found.', 'better-optin' ),
			'not_found_in_trash' => __( 'No popups found in Trash.', 'better-optin' ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'popups' ),
			'capability_type'   => 'post',
			'has_archive'       => false,
			'hierarchical'      => false,
			'menu_position'     => null,
			'menu_icon'         => $icon,
			'supports'          => array( 'title' )
		);

		register_post_type( 'wpbo-popup', $args );

	}

	/**
	 * Check if a popup is available for the current page.
	 *
	 * The function checks, in this order, if a popup is available for:
	 * - The current post
	 * - The current post type
	 * - The whole site
	 *
	 * @since  1.0.0
	 * @return mixed Popup ID if a popup is available, false otherwise
	 */
	public static function is_popup_available() {

		global $post;

		/**
		 * First of all let's check if the user is an admin
		 * and if popups are hidden for admins.
		 */
		if( is_user_logged_in() && current_user_can( 'administrator' ) ) {

			$admin = boolval( wpbo_get_option( 'hide_admins', false ) );

			if( true === $admin )
				return false;

		}

		/* Try to avoid all the calculation with the use of sessions */
		if( isset( $_SESSION['wpbo'][$post->ID] ) ) {

			$popup  = $_SESSION['wpbo'][$post->ID];
			$status = get_post_status( $popup );

			/* Make sure the popup hasn't been disabled while browsing */
			if( 'publish' != $status ) {
				unset( $_SESSION['wpbo'][$post->ID] );
				return false;
			}
		}

		else {

			$relationships = get_option( 'wpbo_popup_relationships', array() );
			$popup         = false;
			$check         = false;
			$post_type     = $post->post_type;
			$query_args    = array(
				'post_type'              => 'wpbo-popup',
				'post_status'            => 'publish',
				'order'                  => 'DESC',
				'orderby'                => 'date',
				'posts_per_page'         => 1,
				'no_found_rows'          => false,
				'cache_results'          => false,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			);		

			/**
			 * There is a popup set for this specific page
			 */
			if( is_array( $relationships ) && array_key_exists( $post->ID, $relationships ) && 'publish' == get_post_status( $relationships[$post->ID] ) ) {
				$popup = $relationships[$post->ID];
			}

			/**
			 * Let's check for more global popups
			 */
			else {

				/**
				 * Check if there is a popup to display for this type
				 */
				$query_args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key'     => '_wpbo_display_' . $post_type,
						'value'   => 'all',
						'type'    => 'CHAR',
						'compare' => '='
					),
					array(
						'key'     => '_wpbo_display_all',
						'value'   => 'no',
						'type'    => 'CHAR',
						'compare' => '='
					)
				);

				$check = new WP_Query( $query_args );
				
				if( isset( $check->post ) ) {
					$popup = $check->post->ID;
				}

				/**
				 * Check if there is a popup to display everywhere
				 */
				else {

					$query_args['meta_query'] = array(
						array(
							'key'     => '_wpbo_display_all',
							'value'   => 'yes',
							'type'    => 'CHAR',
							'compare' => '='
						)
					);

					$check = new WP_Query( $query_args );

					if( isset( $check->post ) )
						$popup = $check->post->ID;

				}

			}

		}

		/* Store popup ID in session to avoid calculating again on page refresh */
		if( !isset( $_SESSION['wpbo'] ) )
			$_SESSION['wpbo'] = array();

		$_SESSION['wpbo'][$post->ID] = $popup;

		/**
		 * Shall the popup be displayed for this visitor?
		 */
		if( false !== $popup ) {

			if( isset( $_COOKIE["wpbo_$popup"] ) && !has_shortcode( $post->post_content, 'wpbo_popup' ) )
				return false;

		}

		return $popup;

	}

	/**
	 * Render popup markup.
	 *
	 * @since  1.0.0
	 */
	public function popup() {

		$popup_id = $this->is_popup_available();
		$output   = false;

		if( false === $popup_id )
			return;

		/**
		 * wpbo_popup_output hook
		 *
		 * @since  1.0.0
		 */
		$output = apply_filters( 'wpbo_popup_output', self::get_popup_markup( $popup_id ), $popup_id );

		if( false === $output ) {
			echo "<!-- No template selected for popup #$popup_id -->";
			return false;
		}

		/**
		 * wpbo_before_popup_form hook
		 *
		 * @since  1.0.0
		 */
		do_action( 'wpbo_before_popup_form', $popup_id );

		/* Echo the popup */
		echo '<div class="wpbo wpbo-popup-' . $popup_id . '">' . $output . '</div>';

		/**
		 * wpbo_after_popup_form hook
		 *
		 * @since  1.0.0
		 */
		do_action( 'wpbo_after_popup_form', $popup_id );

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
	public static function get_popup_markup( $popup_id ) {

		/* Check if the template was customized */
		if( '' != ( $customized = get_post_meta( $popup_id, '_wpbo_template_display', true ) ) ) {

			if( is_admin() ) {
				$output = html_entity_decode( get_post_meta( $popup_id, '_wpbo_template_editor', true ), ENT_COMPAT | ENT_HTML401, 'UTF-8' );
			} else {
				$output = html_entity_decode( $customized, ENT_COMPAT | ENT_HTML401, 'UTF-8' );
			}
		}

		/* Otherwise use the default template */
		else {

			$template = get_post_meta( $popup_id, 'wpbo_template', true );

			if( '' == $template )
				return false;

			$file     = $template . '.php';
			$filepath = WPBO_PATH . 'admin/views/templates/' . $file;

			if( file_exists( $filepath ) ) {

				/* Turn on buffering */
				ob_start();

				require( $filepath );

				/* Get the buffered content into a var */
				$output = ob_get_contents();

				/* Clean buffer */
				ob_end_clean();

			}

		}

		if( !is_admin() ) {

			global $post;

			/* Get return URL */
			if( '' != ( $custom_url = wpbo_get_option( 'return_url', '', $popup_id ) ) ) {
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
			$return_url = apply_filters( 'wpbo_return_url', $return_url, $popup_id, $post->ID );

			/* Add the form */
			$output = sprintf( '<form role="form" class="optform" id="%s" action="%s" method="post">', 'wpbo-popup-' . $popup_id, get_permalink( $post->ID ) ) . $output;

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
	 * Get popup settings and display them.
	 *
	 * @since  1.0.0
	 * @return [type] [description]
	 */
	public function popup_settings() {

		$popup = $this->is_popup_available();

		if( false === $popup )
			return;

	}

	/**
	 * Record popup impression.
	 *
	 * @since  1.0.0
	 * @return integer Total number of impressions
	 */
	public function new_impression() {

		global $wpbo_analytics;

		$post_id = intval( $_POST['popup_id'] );
		$prev    = get_post_meta( $post_id, 'wpbo_impressions', true );
		$clean   = '' == $prev ? 0 : intval( $prev );
		$new     = ++$clean;

		/* Log the impression */
		$log = $wpbo_analytics->insert_data( array( 'popup_id' => $post_id, 'data_type' => 'impression', 'ip_address' => self::get_ip_address(), 'referer' => esc_url( $_SERVER['HTTP_REFERER'] ), 'user_agent' => $_SERVER['HTTP_USER_AGENT'] ), true );

		update_post_meta( $post_id, 'wpbo_impressions', $new, $prev );
		echo $new;
		die();

	}

	/**
	 * Get popup ID.
	 *
	 * @since  1.0.0
	 * @return integer ID of the popup
	 */
	public static function get_popup_id() {

		if( is_admin() ) {

			if( isset( $_GET['wpbo_popup'] ) )
				return $_GET['wpbo_popup'];

		} else {
			return Better_Optin::is_popup_available();
		}

	}

	/**
	 * Get visitor IP address.
	 *
	 * @since  1.0.0
	 * @return string IP address
	 * @see    http://stackoverflow.com/a/15699314
	 */
	public static function get_ip_address() {

		$env = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		);

		foreach( $env as $key ) {

			if( array_key_exists( $key, $_SERVER ) === true ) {

				foreach( explode( ',', $_SERVER[$key] ) as $IPaddress ) {
                
                	$IPaddress = trim( $IPaddress ); // Just to be safe

					if( filter_var( $IPaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false )
						return $IPaddress;
				}
			}
		}

		return 'unknown';

	}

}
