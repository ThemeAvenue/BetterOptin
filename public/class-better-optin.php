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
	const VERSION = '1.2.4';

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

		/* Check posts associations */
		add_action( 'wp_ajax_wpbo_new_impression',  array( $this, 'new_impression' ) );
		add_action( 'wp_ajax_nopriv_wpbo_new_impression',  array( $this, 'new_impression' ) );

		/* The following shouldn't be loaded during Ajax requests */
		if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) {
			/* Add popup settings and markup */
			add_action( 'wp_head', array( $this, 'popup_settings' ) );
			add_action( 'wp_footer', array( $this, 'popup' ) );
			add_action( 'wp_footer', array( $this, 'submission_confirmation' ), 999 );
		}

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
	 * Render popup markup.
	 *
	 * @since  1.0.0
	 */
	public function popup() {

		$popup_id = $this->is_popup_available();
		$output   = false;

		if ( false === $popup_id ) {
			return;
		}

		/**
		 * wpbo_popup_output hook
		 *
		 * @since  1.0.0
		 */
		$output = apply_filters( 'wpbo_popup_output', self::get_popup_markup( $popup_id ), $popup_id );

		if ( false === $output ) {
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
