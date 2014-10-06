<?php
/**
 * Better Optin.
 *
 * @package   BetterOptin_Admin
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2014 ThemeAvenue
 */

class Better_Optin_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin            = Better_Optin::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$plugin_basename   = WPBO_BASENAME;
		$activated         = get_option( 'wpbo_just_activated', false );

		/**
		 * First thing we check if the plugin has just been activated.
		 * If so, we take the user to the about page and delete the
		 * option we used for the check.
		 */
		if( $activated ) {

			/* Delete the option */
			delete_option( 'wpbo_just_activated' );

			/* Redirect to about page */
			wp_redirect( add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-about' ), admin_url( 'edit.php' ) ) );

			/* Don't do anything else */
			exit;

		}

		/* Check posts associations */
		add_action( 'wp_ajax_wpbo_check_page_availability',  array( $this, 'check_page_availability' ) );
		add_action( 'wp_ajax_wpbo_get_graph_data',  array( $this, 'get_graph_data' ) );
		add_action( 'wp_ajax_wpbo_tour_completed', array( $this, 'tour_completed' ) );
		add_action( 'wp_ajax_wpbo_get_doc', array( $this, 'get_documentation' ) );

		/* The following shouldn't be loaded during Ajax requests */
		if( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) {

			// Load admin style sheet and JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'admin_init', array( $this, 'disable_autosave' ) );

			/* Remote dashboard notifications */
			add_action( 'plugins_loaded', array( $this, 'remote_notices' ), 11 );

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_settings_page' ) );

			/* Add plugin and post actions. */
			add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
			add_filter( 'post_row_actions', array( $this, 'row_action' ), 10, 2 );

			/* Prevent from publishing right away */
			add_filter( 'wp_insert_post_data', array( $this, 'save_before_publish' ), 99, 2 );

			/* Change Publish label to Save for new popups */
			add_filter( 'gettext', array( $this, 'change_publish_button_label' ), 10, 2 );

			/* Draft notice */
			add_action( 'admin_notices', array( $this, 'unpublished_popup_notice' ) );

			/* Save complex custom fields */
			add_action( 'save_post', array( $this, 'save_custom_fields' ) );

			/* Save the customized templates */
			if( isset( $_GET['wpbo_popup'] ) && isset( $_POST['wpbo_nonce'] ) )
				add_action( 'init', array( $this, 'save_templates' ) );

			/* Reset to default template */
			if( isset( $_GET['wpbo_reset'] ) && isset( $_GET['wpbo_popup'] ) && wp_verify_nonce( $_GET['wpbo_reset'], 'reset_template' ) )
				add_action( 'init', array( $this, 'reset_template' ) );

			/* Register metaboxes */
			add_action( 'add_meta_boxes', array( $this, 'register_metabox_steps' ) );

			/* Delete post relationships */
			add_action( 'before_delete_post', array( $this, 'delete_post_relationships' ) );

			/* Customize footer text */
			add_filter( 'admin_footer_text', array( $this, 'copyright' ), 10, 2);

			/* Add custom column */
			add_filter( 'manage_wpbo-popup_posts_columns', array( $this, 'relationships' ), 10, 2);
			add_action( 'manage_wpbo-popup_posts_custom_column', array( $this, 'relationships_content' ), 10, 2);

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
	 * Register and enqueue admin-specific style sheet
	 *
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		global $post, $current_screen;			

		if( $this->is_plugin_page() ) {

			wp_enqueue_style( $this->plugin_slug .'-admin', WPBO_URL . 'admin/assets/css/admin.css', array(), Better_Optin::VERSION );
			wp_enqueue_style( $this->plugin_slug .'-admin-chosen', WPBO_URL . 'bower_components/chosen_v1.1.0/chosen.min.css', array(), Better_Optin::VERSION );

			/* Customizer page */
			if( isset( $_GET['wpbo_popup'] ) ) {

				wp_enqueue_style( $this->plugin_slug .'-editor', WPBO_URL . 'admin/assets/css/ta-editor.css', array(), Better_Optin::VERSION );
				wp_enqueue_style( $this->plugin_slug . '-main', WPBO_URL . 'public/assets/css/betteroptin.css', array(), Better_Optin::VERSION );

			}

			/* Load colorpicker style */
			if( isset( $_GET['action'] ) && 'edit' == $_GET['action'] || isset( $current_screen->action ) && isset( $current_screen->post_type ) && 'add' == $current_screen->action && 'wpbo-popup' == $current_screen->post_type ) {
				wp_enqueue_style( 'wp-color-picker' );
			}

			/* Analytics page */
			if( isset( $_GET['page'] ) && 'wpbo-analytics' == $_GET['page'] ) {

				wp_enqueue_style( $this->plugin_slug .'-dataTables', '//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.1/css/jquery.dataTables.min.css', array(), Better_Optin::VERSION );
				wp_enqueue_style( $this->plugin_slug .'-circliful', WPBO_URL . 'bower_components/circliful/css/jquery.circliful.css', array(), Better_Optin::VERSION );

			}

		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		global $post, $current_screen;		

		if( $this->is_plugin_page() ) {

			/* Required on all plugin pages */
			wp_enqueue_script( $this->plugin_slug . '-admin', WPBO_URL . 'admin/assets/js/admin.js', array( 'jquery' ), Better_Optin::VERSION );

			if( isset( $_GET['action'] ) && 'edit' == $_GET['action'] || isset( $_GET['post_type'] ) && 'wpbo-popup' == $_GET['post_type'] ) {

				wp_enqueue_script( $this->plugin_slug . '-admin-chosen', WPBO_URL . 'bower_components/chosen_v1.1.0/chosen.jquery.min.js', array( 'jquery' ), Better_Optin::VERSION );

			}

			/* Required only on the post edit screen */
			if( isset( $_GET['action'] ) && 'edit' == $_GET['action'] || isset( $current_screen->action ) && isset( $current_screen->post_type ) && 'add' == $current_screen->action && 'wpbo-popup' == $current_screen->post_type ) {
				wp_enqueue_script( 'wp-color-picker' );
			}

			/* Required on the customizer page only */
			if( isset( $_GET['wpbo_popup'] ) ) {

				wp_enqueue_media();
				wp_enqueue_script( $this->plugin_slug . '-admin-script', WPBO_URL . 'admin/assets/js/ta-live-editor.js', array( 'jquery', 'wp-color-picker' ), Better_Optin::VERSION );
				wp_enqueue_script( $this->plugin_slug . '-admin-autosize', WPBO_URL . 'bower_components/jquery-autosize/jquery.autosize.min.js', array( 'jquery' ), Better_Optin::VERSION );
				wp_enqueue_script( $this->plugin_slug . '-admin-matchHeight', WPBO_URL . 'bower_components/matchHeight/jquery.matchHeight-min.js', array( 'jquery' ), Better_Optin::VERSION );

			}

			if( isset( $_GET['page'] ) ) {

				/* Analytics page */
				if( 'wpbo-analytics' == $_GET['page'] ) {

					wp_enqueue_script( $this->plugin_slug . '-admin-dataTables', '//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.1/js/jquery.dataTables.min.js', array( 'jquery' ), Better_Optin::VERSION );
					wp_enqueue_script( $this->plugin_slug . '-admin-flot', WPBO_URL . 'bower_components/flot/jquery.flot.js', array( 'jquery' ), Better_Optin::VERSION );
					wp_enqueue_script( $this->plugin_slug . '-admin-flot-time', WPBO_URL . 'bower_components/flot/jquery.flot.time.js', array( 'jquery' ), Better_Optin::VERSION );
					wp_enqueue_script( $this->plugin_slug . '-admin-flot-tooltip', WPBO_URL . 'bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js', array( 'jquery' ), Better_Optin::VERSION );
					wp_enqueue_script( $this->plugin_slug . '-admin-circliful', WPBO_URL . 'bower_components/circliful/js/jquery.circliful.min.js', array( 'jquery' ), Better_Optin::VERSION );
					wp_enqueue_script( $this->plugin_slug . '-admin-analytics', WPBO_URL . 'admin/assets/js/part-analytics.js', array( 'jquery' ), Better_Optin::VERSION );

				}

			}

		}

	}

	/**
	 * Add general settings.
	 *
	 * @since  1.0.0
	 * @param  array $settings Pre-existing settings
	 * @return array           Updated plugin settings
	 */
	public static function settings( $settings ) {

		$providers = apply_filters( 'wpbo_mailing_providers', array() );

		$settings['general'] = array(
			'name'    => __( 'General', 'wpbo' ),
			'options' => array(
				array(
					'name'    => __( 'E-Mailing Provider', 'wpbo' ),
					'id'      => 'mailing_provider',
					'type'    => 'select',
					'options' => $providers,
					'desc'    => __( 'Which e-mailing provider do you use?', 'wpbo' ),
					'default' => 'wordpress'
				),
				array(
					'name'    => __( 'Return URL', 'wpbo' ),
					'id'      => 'return_url',
					'type'    => 'text',
					'desc'    => __( 'Where should the user be redirected after subscribing?', 'wpbo' ),
					'default' => ''
				),
				array(
					'name'    => __( 'Anonymize IPs', 'wpbo' ),
					'id'      => 'anonymize_ip',
					'type'    => 'checkbox',
					'desc'    => __( 'Delete the last byte(s) of stored IP addresses? This will remove the last digits of saved IP addresses to protect users privacy.', 'wpbo' ),
					'default' => false
				),
				array(
					'name'    => __( 'Hide for Admins', 'wpbo' ),
					'id'      => 'hide_admins',
					'type'    => 'checkbox',
					'desc'    => __( 'Hide the popups for admins? No popup will ever show up for site administrators.', 'wpbo' ),
					'desfult' => true
				)
			)
		);

		return $settings;

	}

	public function is_plugin_page() {

		global $post;

		$slugs = array( 'wpbo-customizer', 'wpbo-relationships', 'wpbo-about', 'wpbo-addons', 'wpbo-analytics', 'edit.php?post_type=wpbo-popup-settings' );

		if( isset( $post ) && is_object( $post ) && isset( $post->post_type ) && 'wpbo-popup' == $post->post_type ) {

			return true;

		} elseif( isset( $_GET['page'] ) && in_array( $_GET['page'], $slugs) ) {

			return true;

		} else {

			return false;

		}

	}

	/**
	 * Add Plugin Menu Items.
	 *
	 * @since  1.0.0
	 */
	public function add_plugin_settings_page() {

		global $_registered_pages;

		/* Register customizer page without adding it to the menu */
		$_registered_pages['wpbo-popup_page_wpbo-customizer'] = true;
		add_action( 'wpbo-popup_page_wpbo-customizer', array( $this, 'display_popup_customizer' ) );

		$this->analytics = add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Analytics', 'wpbo' ), __( 'Analytics', 'wpbo' ), 'administrator', 'wpbo-analytics', array( $this, 'display_popup_analytics' ) );
		$this->addons = add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Addons', 'wpbo' ), __( 'Addons', 'wpbo' ), 'administrator', 'wpbo-addons', array( $this, 'display_popup_addons' ) );
		$this->about = add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'About', 'wpbo' ), __( 'About', 'wpbo' ), 'administrator', 'wpbo-about', array( $this, 'display_popup_about' ) );
		$this->doc = add_submenu_page( 'edit.php?post_type=wpbo-popup', __( 'Documentation', 'wpbo' ), __( 'Documentation', 'wpbo' ), 'administrator', 'wpbo-doc', array( $this, 'display_popup_doc' ) );

	}

	/**
	 * Display Customizer Page Content.
	 *
	 * @since  1.0.0
	 */
	public function display_popup_customizer() {
		require_once( WPBO_PATH . 'admin/views/customizer.php' );
	}

	/**
	 * Display Relationships.
	 *
	 * @since  1.0.0
	 */
	public function display_popup_relationships() {
		require_once( WPBO_PATH . 'admin/views/relationships.php' );
	}

	/**
	 * Display About Page.
	 *
	 * @since  1.0.0
	 */
	public function display_popup_about() {
		require_once( WPBO_PATH . 'admin/views/about.php' );
	}

	/**
	 * Display About Page.
	 *
	 * @since  1.0.0
	 */
	public function display_popup_doc() {
		require_once( WPBO_PATH . 'admin/views/documentation.php' );
	}

	/**
	 * Display Analytics Page.
	 *
	 * @since  1.0.0
	 */
	public function display_popup_analytics() {
		require_once( WPBO_PATH . 'admin/views/analytics.php' );
	}

	/**
	 * Display Addons Page.
	 *
	 * @since  1.0.0
	 */
	public function display_popup_addons() {
		require_once( WPBO_PATH . 'admin/views/addons.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'edit.php?post_type=wpbo-popup-settings' ), admin_url( 'edit.php' ) ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Popup Design Step.
	 *
	 * This popup requires additional checks so
	 * we use a completely custom one.
	 *
	 * @since  1.0.0
	 */
	public function register_metabox_steps() {
		add_meta_box( 'wpbo_step1_template', __( 'Step 1: Choose a Template', 'wpbo' ), array( $this, 'display_metabox_step1' ), 'wpbo-popup', 'normal', 'default' );
		add_meta_box( 'wpbo_step2_settings', __( 'Step 2: Edit Settings', 'wpbo' ), array( $this, 'display_metabox_step2' ), 'wpbo-popup', 'normal', 'default' );
		add_meta_box( 'wpbo_step3_display', __( 'Step 3: Choose where to Display', 'wpbo' ), array( $this, 'display_metabox_step3' ), 'wpbo-popup', 'normal', 'default' );
		add_meta_box( 'wpbo_step4_design', __( 'Step 4: Customize the Design', 'wpbo' ), array( $this, 'display_metabox_step4' ), 'wpbo-popup', 'normal', 'default' );
		add_meta_box( 'wpbo_step5_checklist', __( 'Checklist', 'wpbo' ), array( $this, 'display_metabox_step5' ), 'wpbo-popup', 'side', 'high' );
	}

	/**
	 * Display the content of step 1 metabox.
	 *
	 * @since  1.0.0
	 */
	public function display_metabox_step1() {
		include_once( WPBO_PATH . 'admin/views/metaboxes/template.php' );
	}

	/**
	 * Display the content of step 2 metabox.
	 *
	 * @since  1.0.0
	 */
	public function display_metabox_step2() {
		include_once( WPBO_PATH . 'admin/views/metaboxes/settings.php' );
	}

	/**
	 * Display the content of step 3 metabox.
	 *
	 * @since  1.0.0
	 */
	public function display_metabox_step3() {
		include_once( WPBO_PATH . 'admin/views/metaboxes/display.php' );
	}

	/**
	 * Display the content of step 3 metabox.
	 *
	 * @since  1.0.0
	 */
	public function display_metabox_step4() {
		include_once( WPBO_PATH . 'admin/views/metaboxes/design.php' );
	}

	/**
	 * Display the content of step 3 metabox.
	 *
	 * @since  1.0.0
	 */
	public function display_metabox_step5() {
		include_once( WPBO_PATH . 'admin/views/metaboxes/checklist.php' );
	}

	/**
	 * Retrieve the popup templates list.
	 *
	 * @since  1.0.0
	 * @return array List of available templates with the associated screenshot
	 */
	public static function get_templates_list() {

		/* Set the default templates directory */
		$directory = array(
			'path' => WPBO_PATH . 'admin/views/templates',
			'url'  => WPBO_URL . 'admin/views/templates'
		);

		/* Allow for extra directories */
		$dirs = apply_filters( 'wpbo_templates_dirs', array( $directory ) );
		$list = array();

		foreach( $dirs as $key => $dir ) {

			$exceptions = array( '.', '..' );

			if( !isset( $_GET['test_template'] ) )
				$exceptions[] = 'template-test.php';

			/* Get file paths with trailing slashes */
			$path = trailingslashit( $dir['path'] );
			$url  = trailingslashit( $dir['url'] );

			/* Scan the content */
			$templates = scandir( $path );

			foreach( $templates as $key => $template ) {

				$images = array( 'png', 'jpg', 'jpeg', 'gif' ); // Allowed images types

				/* Don't process the '.' and '..' */
				if( in_array( $template, $exceptions ) )
					continue;

				/* Get file extension */
				$ext = pathinfo( $path . $template, PATHINFO_EXTENSION );

				/* Only check the php files */
				if( 'php' != $ext )
					continue;

				/* Get template base name */
				$tpl = str_replace( ".$ext", '', $template );

				foreach( $images as $k => $type ) {

					$imgfile = $tpl . '.' . $type;

					if( file_exists( $path . $imgfile ) ) {

						/**
						 * @todo need to get image URL
						 */
						$img = $url . $imgfile;
						break;

					}

				}

				/* Add new template to the list */
				$list[$tpl] = $img;		

			}
		}

		return $list;

	}

	/**
	 * Check page availability.
	 *
	 * When the user adds a new page where the current popup should be triggered,
	 * we check if the page is not already used by another popup.
	 *
	 * As we can't have 2 popups on one page, we only want to keep one popup per page. Hence,
	 * we tell the user that the page he just selected
	 * 
	 * @return [type] [description]
	 */
	public function check_page_availability() {

		$post_id    = isset( $_POST['post_id'] ) ? $_POST['post_id'] : false; // Last selected item
		$current_id = isset( $_POST['current_id'] ) ? $_POST['current_id'] : false; // The current post ID (popup currently being edited)
		$selected   = isset( $_POST['selected_all'] ) ? explode( ',', $_POST['selected_all'] ) : array(); // All selected items
		$messages   = '0'; // Default string to return

		if( is_array( $selected ) && count( $selected ) > 0 && false !== $current_id ) {

			$relationships = get_option( 'wpbo_popup_relationships', array() );

			foreach( $selected as $post_id ) {

				if( array_key_exists( $post_id, $relationships ) && $current_id != $relationships[$post_id] ) {

					/* Page details */
					$post  = get_post( $post_id );
					$title = $post->post_title;

					/* Popup details */
					$popup  = get_post( $relationships[$post_id] );
					$ptitle = $popup->post_title;
					$plink  = add_query_arg( array( 'post' => $popup->ID, 'action' => 'edit' ), admin_url( 'post.php' ) );

					$msg = '<p>';
					$msg .= sprintf( __( 'The page %s (#%s) is already used by the popup <a href="%s" target="_blank">%s</a> (#%s).', 'wpbo' ), "<strong><em>$title</em></strong>", $post_id, $plink, $ptitle, $popup->ID );
					$msg .= '</p>';

					/* Convert $messages into an array when there is at least one warning message to save */
					if( !is_array( $messages ) )
						$messages = array();

					array_push( $messages, $msg );
				}

			}

		}

		/* Convert the possible messages to string before we return it */
		if( is_array( $messages ) ) {

			/* Explain what's going to happen next */
			array_push( $messages, '<p><em>' . __( 'TIP: If you keep the conflicting page(s) selected, they will be removed from the other popup(s).', 'wpbo' ) . '</em></p>' );

			/* Convert to string */
			$messages = implode( '', $messages );
		}

		echo $messages;
		die();

	}

	/**
	 * Save Customized Templates.
	 *
	 * @since  1.0.0
	 */
	public function save_templates() {

		if( !isset( $_GET['wpbo_popup'] ) || !isset( $_POST['wpbo_nonce'] ) )
			return;

		$post_id = intval( $_GET['wpbo_popup'] );

		if( 'wpbo-popup' != get_post_type( $post_id ) )
			return;

		if( !wp_verify_nonce( $_POST['wpbo_nonce'], 'wpbo_customize_template' ) )
			return;

		if( isset( $_POST['taed-outerhtml'] ) )
			update_post_meta( $post_id, '_wpbo_template_editor', htmlentities( $_POST['taed-outerhtml'], ENT_COMPAT | ENT_HTML401, 'UTF-8' ) );

		if( isset( $_POST['taed-outerhtmlclean'] ) )
			update_post_meta( $post_id, '_wpbo_template_display', htmlentities( $_POST['taed-outerhtmlclean'], ENT_COMPAT | ENT_HTML401, 'UTF-8' ) );

		/* Read-only redirect */
		// wp_redirect( add_query_arg( array( 'post' => $post_id, 'action' => 'edit', 'message' => '1' ), admin_url( 'post.php' ) ), $status );
		wp_redirect( add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-customizer', 'wpbo_popup' => $post_id, 'message' => 'updated' ), admin_url( 'edit.php' ) ), $status );
		exit;

	}

	/**
	 * Reset to default template.
	 *
	 * Deletes all customizations from database which
	 * will result in using the default template file.
	 *
	 * @since  1.0.0
	 */
	public function reset_template() {

		if( isset( $_GET['wpbo_reset'] ) && isset( $_GET['wpbo_popup'] ) && wp_verify_nonce( $_GET['wpbo_reset'], 'reset_template' ) ) {

			delete_post_meta( $_GET['wpbo_popup'], '_wpbo_template_editor' );
			delete_post_meta( $_GET['wpbo_popup'], '_wpbo_template_display' );

			// wp_redirect( add_query_arg( array( 'post' => $_GET['post'], 'action' => 'edit' ), admin_url( 'post.php' ) ) );
			wp_redirect( add_query_arg( array( 'wpbo_popup' => $_GET['wpbo_popup'], 'post_type' => 'wpbo-popup', 'page' => 'wpbo-customizer' ), admin_url( 'edit.php' ) ) );
			exit;

		}

	}

	/**
	 * Save custom fields.
	 *
	 * Save the complex custom fields that can't be handled
	 * through TItan Framework. This includes step 3: where
	 * the popup should be displayed.
	 *
	 * @since  1.0.0
	 */
	public function save_custom_fields( $post_id ) {

		if( !isset( $_POST['wpbo_display'] ) || isset( $_POST['wpbo_display'] ) && !wp_verify_nonce( $_POST['wpbo_display'], 'add_display' ) )
			return;

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if( !isset( $_POST['post_type'] ) || isset( $_POST['post_type'] ) && 'wpbo-popup' != $_POST['post_type'] )
			return;

		if( !current_user_can( 'edit_post', $post_id ) )
			return;

		/**
		 * STEP 1: Template
		 */
		if( isset( $_POST['wpbo_template'] ) ) {

			$previous_template = get_post_meta( $post_id, 'wpbo_template', true );
			update_post_meta( $post_id, 'wpbo_template', $_POST['wpbo_template'], $previous_template );

			/* Delete possible customizations */
			if( $previous_template != $_POST['wpbo_template'] ) {
				delete_post_meta( $post_id, '_wpbo_template_editor' );
				delete_post_meta( $post_id, '_wpbo_template_display' );
			}

		}

		/**
		 * STEP 2: Settings
		 */
		$settings = array();
		$step2    = array(
			'close_overlay',
			'close_esc',
			'cookie_lifetime',
			'animation',
			'close_button',
			'overlay_color',
			'overlay_opacity',
			'wiggle',
			'return_url',
		);

		if( isset( $_POST['wpbo_settings'] ) ) {

			foreach( $step2 as $option ) {

				if( isset( $_POST['wpbo_settings'][$option] ) ) {

					$settings[$option] = $_POST['wpbo_settings'][$option];

				}

			}

			update_post_meta( $post_id, '_wpbo_settings', $settings );

		}

		/**
		 * STEP 3: Display
		 */
		
		/**
		 * Display everywhere.
		 *
		 * This is the simplest case: just display the popup
		 * on every single page that is loaded.
		 */
		if( isset( $_POST['wpbo_display_all'] ) ) {
			update_post_meta( $post_id, '_wpbo_display_all', 'yes' );
		} else {
			update_post_meta( $post_id, '_wpbo_display_all', 'no' );
		}

		/* Get available public post types */
		$post_types = get_post_types( array( 'public' => true ) );
		$except     = array( 'attachment', 'wpbo-popup' );

		/* Get popup / posts relationships */
		$relationships = get_option( 'wpbo_popup_relationships', array() );

		/**
		 * Handle each post individually.
		 */
		foreach( $post_types as $key => $pt ) {

			/* Exclude specific post types */
			if( in_array( $key, $except ) )
				continue;

			/* Current value */
			$current = get_post_meta( $post_id, '_wpbo_display_' . $pt, true );

			/* Set $current at the correct format if needed */
			if ( !is_array( $current ) ) {
				$current = array();
			}

			if( isset( $_POST['wpbo_display_' . $pt] ) ) {

				$display = $_POST['wpbo_display_' . $pt];

				if( isset( $_POST['wpbo_display_' . $pt . '_all'] ) )
					$display = 'all';

				/**
				 * Create the relationships
				 */
				if( is_array( $display ) ) {

					foreach( $display as $key => $post ) {

						/**
						 * Check if a relationship already exists for this post.
						 * If there is one, we remove it from the post meta itself
						 * as relationships are also stored in the post metas (so that
						 * we can populate the fields).
						 */
						if( isset( $relationships[$post] ) && $post_id != $relationships[$post] ) {

							$old = $edit = get_post_meta( $relationships[$post], '_wpbo_display_' . $pt, true );

							if( is_array( $edit ) && ( $relation_key = array_search( $post, $edit ) ) !== false ) {

								unset( $edit[$relation_key] );

								update_post_meta( $relationships[$post], '_wpbo_display_' . $pt, $edit, $old );
							}

						}

						/* Add the new relationship */
						$relationships[$post] = $post_id;
					}

					/**
					 * Clean previous relationships that might have been removed.
					 */
					$diff = array_diff( $current, $display );

					foreach( $diff as $dkey => $dval ) {
						if( isset( $relationships[$dval] ) )
							unset( $relationships[$dval] );
					}

					/**
					 * Update the relationships
					 */
					update_option( 'wpbo_popup_relationships', $relationships );

				}

				if( maybe_serialize( $current ) != maybe_serialize( $display ) )
					update_post_meta( $post_id, '_wpbo_display_' . $pt, $display );

			} elseif( !isset( $_POST['wpbo_display_' . $pt] ) ) {

				$prev = get_post_meta( $post_id, '_wpbo_display_' . $pt, true );

				if( isset( $_POST['wpbo_display_' . $pt . '_all'] ) && 'all' != $current )
					update_post_meta( $post_id, '_wpbo_display_' . $pt, 'all' );

				elseif( !isset( $_POST['wpbo_display_' . $pt . '_all'] ) && '' != $current )
					delete_post_meta( $post_id, '_wpbo_display_' . $pt );

				/**
				 * Remove the relationships
				 */
				if( is_array( $prev ) ) {

					foreach( $prev as $pid ) {

						if( isset( $relationships[$pid] ) )
							unset( $relationships[$pid] );

					}

					update_option( 'wpbo_popup_relationships', $relationships );

				}

			}

		}

		/* Redirect to customizer */
		if( isset( $_POST['save_customize'] ) ) {

			$customizer = add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-customizer', 'wpbo_popup' => $post_id ), admin_url( 'edit.php' ) );

			wp_redirect( $customizer );
			exit;

		}

	}

	/**
	 * Delete Post Relationships.
	 *
	 * Delete all relationships when a popup
	 * is deleted by the user.
	 *
	 * @since  1.0.0
	 * @param  bool $post_id ID of the post to be deleted
	 */
	public function delete_post_relationships( $post_id ) {

		$relationships = $new = get_option( 'wpbo_popup_relationships', array() );
		$post_types    = get_post_types( array( 'public' => true ) );
		$except        = array( 'attachment', 'wpbo-popup' );

		/* Iterate through all allowed post types */
		foreach( $post_types as $key => $pt ) {

			if( in_array( $key, $except ) )
				continue;

			/* Get relationships for this post type */
			$display = get_post_meta( $post_id, '_wpbo_display_' . $pt, true );

			if( is_array( $display ) ) {

				foreach( $display as $key => $post ) {

					/* Remove deprecated relations */
					if( isset( $new[$post] ) )
						unset( $new[$post] );

				}

			}

		}

		/* Update relationships if needed only */
		if( serialize( $relationships ) !== serialize( $new ) )
			update_option( 'wpbo_popup_relationships', $new );

	}

	/**
	 * Get font stack.
	 *
	 * @since  1.0.0
	 * @return array List of all available fonts
	 */
	public static function get_font_stack() {

		/* Try to get body from the transient */
		$body = get_transient( 'wpbo_fonts' );

		if( false === $body ) {

			/* Prepare the HTTP request */
			$route    = 'http://www.kimonolabs.com/api/8qckyf28?';
			$api_key  = '34f710899fb2424aeb213c881ff10109';
			$endpoint = $route . http_build_query( array( 'apikey' => $api_key ) );
			$response = wp_remote_get( $endpoint );
			$body     = wp_remote_retrieve_body( $response );

			/* Get response from the request if it is valid */
			if( !is_wp_error( $response ) && '' != $body ) {

				/**
				 * Set the cache
				 */
				set_transient( 'wpbo_fonts', $body, 60*60*60 );
				update_option( 'wpbo_fonts', $body );

			}

			/* Otherwise get it from the options, even if deprecated */
			else {
				$body = get_option( 'wpbo_fonts', false );
			}

		}

		/* Decode the JSON */
		$body = json_decode( $body, TRUE );

		if( !is_array( $body ) )
			return false;

		/* Return fonts only */
		return $body['results']['collection1'];

	}

	/**
	 * Retrieve data to feed the graph.
	 *
	 * @since  1.0.0
	 * @return string Records encoded in JSON
	 */
	public function get_graph_data() {

		$query     = array( 'data_type' => 'any', 'limit' => -1 );
		$timeframe = unserialize( stripslashes( $_POST['wpbo_analytics_time'] ) );
		$popup     = isset( $_POST['wpbo_analytics_popup'] ) ? $_POST['wpbo_analytics_popup'] : 'all';
		$period    = isset( $_POST['wpbo_analytics_period'] ) ? $_POST['wpbo_analytics_period'] : 'today';

		/* Set the period */
		$query['period'] = $timeframe;

		/* Select the popup */
		if( 'all' != $popup ) {
			$query['popup_id'] = intval( $popup );
		}

		/* Separate impressions and conversions */
		$query_i = $query;
		$query_i['data_type'] = 'impression';

		$query_c = $query;
		$query_c['data_type'] = 'conversion';

		/* Get the datas */
		$impressions = wpbo_get_datas( $query_i, 'OBJECT' );
		$conversions = wpbo_get_datas( $query_c, 'OBJECT' );

		/* Set the scale */
		$scale  = date( 'Y-m-d' );

		switch( $period ):

			case 'today':
				$scale       = 'Y-m-d H:00:00';
				$timeformat  = '%d/%b';
				$minticksize =  array( 1, 'hour' );
				$min         = strtotime( date( 'Y-m-d 00:00:00' ) ) * 1000;
				$max         = strtotime( date( 'Y-m-d 23:59:59' ) ) * 1000;
			break;

			case 'this_week':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'last monday' ) * 1000;
				$max         = strtotime( 'next sunday' ) * 1000;
			break;

			case 'last_week':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'last monday -7 days' ) * 1000;
				$max         = strtotime( 'next sunday -7 days' ) * 1000;
			break;

			case 'this_month':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'first day of this month' ) * 1000;
				$max         = strtotime( 'last day of this month' ) * 1000;
			break;

			case 'last_month':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'first day of last month' ) * 1000;
				$max         = strtotime( 'last day of last month' ) * 1000;
			break;

			case 'this_quarter':

				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$quarters    = array( 1, 4, 7, 10 );
				$month       = intval( date( 'm' ) );

				if( in_array( $month, $quarters ) ) {
					$current = date( 'Y-m-d', time() );
				} else {

					/* Get first month of this quarter */
					while( !in_array( $month, $quarters) ) {
						$month = $month-1;
					}

					$current = date( 'Y' ) . '-' . $month . '-' . '01';

				}

				$current = strtotime( $current );
				$min     = strtotime( 'first day of this month', $current ) * 1000;
				$max     = strtotime( 'last day of this month', strtotime( '+2 months', $current ) ) * 1000;

			break;

			case 'last_quarter':

				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$quarters    = array( 1, 4, 7, 10 );
				$month       = intval( date( 'm' ) ) - 3;
				$rewind      = false;

				if( in_array( $month, $quarters ) ) {
					$current = date( 'Y-m-d', time() );
				} else {

					/* Get first month of this quarter */
					while( !in_array( $month, $quarters) ) {

						$month = $month-1;

						/* Rewind to last year after we passed January */
						if( 0 === $month )
							$month = 12;
					}

					$current = date( 'Y' ) . '-' . $month . '-' . '01';

				}

				/* Set the theorical current date */
				$current = false === $rewind ? strtotime( $current ) : strtotime( '-1 year', $current );
				$min     = strtotime( 'first day of this month', $current ) * 1000;
				$max     = strtotime( 'last day of this month', strtotime( '+2 months', $current ) ) * 1000;

			break;

			case 'this_year':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$min         = strtotime( 'first day of January', time() ) * 1000;
				$max         = strtotime( 'last day of December', time() ) * 1000;
			break;

			case 'last_year':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$min         = strtotime( 'first day of January last year', time() ) * 1000;
				$max         = strtotime( 'last day of December last year', time() ) * 1000;
			break;

		endswitch;

		/* Propare global array */
		$datas = array(
			'impressionsData' => array(
				'label' => __( 'Impressions', 'wpbo' ),
				'id'    => 'impressions',
				'data'  => array()
			),
			'conversionsData' => array(
				'label' => __( 'Conversions', 'wpbo' ),
				'id'    => 'conversions',
				'data'  => array()
			),
			'scale' => array(
				'minTickSize' => $minticksize,
				'timeformat'  => $timeformat
			),
			'min' => $min,
			'max' => $max
		);

		/* Get the count on the scaled timestamp */
		$imp_array = $this->array_merge_combine( $impressions, $scale );
		$con_array = $this->array_merge_combine( $conversions, $scale );

		/**
		 * Fill the blanks!
		 *
		 * Both impressions and conversions array need to have the same number of entries
		 * (same number of timestamps) for the graph to work properly.
		 *
		 * We alternatively merge the impressions and conversions array. The only added keys
		 * must have a value of 0.
		 */
		$tmp_arr_imp = array_flip( array_keys( $imp_array ) );
		$tmp_arr_con = array_flip( array_keys( $con_array ) );

		/* Set all counts to 0 */
		$tmp_arr_imp = array_map( array( 'Better_Optin_Admin', 'return_zero' ), $tmp_arr_imp );
		$tmp_arr_con = array_map( array( 'Better_Optin_Admin', 'return_zero' ), $tmp_arr_con );

		/* Add missing values in both impressions and conversions arrays */
		$imp_array = $imp_array + $tmp_arr_con;
		$con_array = $con_array + $tmp_arr_imp;

		/* Convert the arrays to a format that Float can read. */
		$imp_array = $this->float_format( $imp_array );
		$con_array = $this->float_format( $con_array );		

		/* Add the hits to datas array */
		$datas['impressionsData']['data'] = $imp_array;
		$datas['conversionsData']['data'] = $con_array;

		/* Return results to script */
		print_r( json_encode( $datas ) );
		die();

	}

	/**
	 * Return zero
	 *
	 * The function just returns 0 and is used for array_map.
	 * This function is required for PHP < 5.3 as anonymous functions
	 * are not yet supported.
	 *
	 * @since  1.0.1
	 * @see    Better_Optin_Admin::get_graph_data()
	 * @param  mixed   $item Array item to reset
	 * @return integer       Zero
	 */
	public static function return_zero( $item ) {
		return 0;
	}

	/**
	 * Prepare the hist array.
	 *
	 * The function takes an array of datas and then,
	 * based on the time scale, gets the number of hits
	 * in a specific timeframe (eg. number of hits per hour).
	 *
	 * @since  1.0.0
	 * @param  array  $array  An array of data
	 * @param  string $format A date format (as used in date())
	 * @return array          An array sorted by time and hits in a format compatible with Float for the graph
	 */
	public static function array_merge_combine( $array, $format ) {

		$parsed = array();
		$new    = array();

		/* Count the number of hits per timeframe */
		foreach( $array as $object ) {

			$date = strtotime( date( $format, strtotime( $object->time ) ) );

			if( !in_array( $date, $parsed ) ) {
				array_push( $parsed, $date );
				$new[$date] = 1;
			} else {
				++$new[$date];
			}

		}

		return $new;

	}

	public function float_format( $array ) {

		$new = array();
		
		/* Reorder the array */
		ksort( $array );

		/** Transform the array in a readable format for Float */
		foreach( $array as $key => $value ) {
			array_push( $new, array( $key * 1000, $value ) ); // Timestamp must be in miliseconds
		}

		return $new;

	}

	/**
	 * Add link to action row.
	 *
	 * Add a direct link to customizer in the post
	 * action row.
	 * 
	 * @param  array $actions List of available actions
	 * @param  opject $post   Post currently parsed
	 * @return array          List of actions containing the customizer link
	 */
	public function row_action( $actions, $post ) {

		/* Only add the link for our post type */
		if( 'wpbo-popup' != $post->post_type )
			return $actions;

		/* Only add the link if a template is set */
		if( '' != get_post_meta( $post->ID, 'wpbo_template', true ) ) {

			$actions['wpbo_customize'] = sprintf( '<a href="%s" class="google_link">%s</a>', add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-customizer', 'wpbo_popup' => $post->ID ), admin_url( 'edit.php' ) ), __( 'Customize', 'wpbo' ) );

		}

		$actions['wpbo_analytics'] = sprintf( '<a href="%s" class="google_link">%s</a>', add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-analytics', 'popup' => $post->ID, 'period' => 'today' ), admin_url( 'edit.php' ) ), __( 'Stats', 'wpbo' ) );

		return $actions;
	}

	/**
	 * Add copyright.
	 *
	 * Add a copyright text at the bottom of all plugin pages.
	 *
	 * @since  1.0.0
	 * @param  string $text WordPress footer text
	 * @return string       BetterOptin copyright
	 */
	public function copyright( $text ) {

		if( !$this->is_plugin_page() )
			return $text;

		printf( __( '<a href="%s" target="_blank">BetterOptin</a> version %s developed by <a href="%s" target="_blank">ThemeAvenue</a>.', 'wpbo' ), esc_url( 'http://betteropt.in' ), Better_Optin::VERSION, esc_url( 'http://themeavenue.net?utm_source=plugin&utm_medium=footer_link&utm_campaign=BetterOptin' ) );

	}

	/**
	 * Add relationship column.
	 *
	 * Add a relationships column in the popup
	 * list screen.
	 * 
	 * @param  array $columns Currently available columns
	 * @return array          Columns containing the relationships
	 */
	public function relationships( $columns ) {

		$new = array();

		foreach( $columns as $key => $label ) {

			$new[$key] = $label;

			if( 'title' == $key )
				$new['relationship'] = __( 'Appears On', 'wpbo' );

		}

		return $new;

	}

	/**
	 * Relationship content.
	 *
	 * Get the relationships for all popups and display it
	 * in the relationships custom column.
	 *
	 * @since  1.0.0
	 * @param  array   Current column ID
	 * @param  integer Current post ID
	 */
	public function relationships_content( $column, $post_id ) {

		if( 'relationship' != $column )
			return;

		/**
		 * First we check if it is "display everywhere".
		 */
		if( 'yes' == get_post_meta( $post_id, '_wpbo_display_all', true ) ) {
			_e( 'Everywhere', 'wpbo' );
			return;
		}

		/**
		 * Second we check if it displays everywhere for a specific post type.
		 */
		$post_types = get_post_types( array( 'public' => true ) );
		$except     = array( 'attachment', 'wpbo-popup' );
		$pts        = array();

		foreach( $post_types as $key => $pt ) {

			if( in_array( $key, $except ) )
				continue;

			if( 'all' == get_post_meta( $post_id, '_wpbo_display_' . $pt, true ) )
				array_push( $pts, sprintf( __( 'All %s', 'wpbo' ), ucwords( $pt ) ) );

		}

		if( count( $pts ) > 0 ) {
			echo implode( ', ', $pts );
			return;			
		}

		/**
		 * Third we check the individual relationships.
		 */
		$relationships = get_option( 'wpbo_popup_relationships', array() );
		$reverse       = array();
		$list          = array();

		/**
		 * Switch keys and values without erasing duplicate values
		 * (which is why array_flip() would not work).
		 */
		foreach( $relationships as $page => $popup ) {

			if( !isset( $reverse[$popup] ) )
				$reverse[$popup] = array();

			array_push( $reverse[$popup], $page );

		}

		/* No relationships at all */
		if( !array_key_exists( $post_id, $reverse ) ) {
			echo '-';
			return;
		}

		/**
		 * Print all the relationships in a table.
		 */
		foreach( $reverse[$post_id] as $key => $page ) {

			$page  = get_post( $page );
			$link  = add_query_arg( array( 'post' => $page->ID, 'action' => 'edit' ), admin_url( 'post.php' ) );
			$title = $page->post_title;

			array_push( $list, "<a href='$link' class='wpbo-tag'>$title</a>" );

		}

		if( count( $list ) > 0 )
			echo implode( ' ', $list );

	}

	/**
	 * Enable Remote Dashboard Notifications
	 *
	 * @since 1.0.0
	 */
	public function remote_notices() {

		/* Load RDN class */
		if( !class_exists( 'TAV_Remote_Notification_Client' ) )
			require_once( WPBO_PATH . 'admin/includes/class-remote-notification.php' );

		/* Instantiate the class */
		$notification = new TAV_Remote_Notification_Client( 5, '278afa858b56d071', 'http://api.themeavenue.net?post_type=notification' );

	}

	/**
	 * Prevent from publishing new popups.
	 *
	 * When a new popup is created, it is saved as a draft
	 * in order to avoid publishing a non customized popup.
	 *
	 * @since  1.0.0
	 * @param  array $data    Sanitized post data
	 * @param  array $postarr Raw post data
	 * @return array          Updated $data
	 */
	public function save_before_publish( $data, $postarr ) {

		if( 'wpbo-popup' == $postarr['post_type'] && isset( $postarr['original_post_status'] ) ) {

			if( 'auto-draft' == $postarr['original_post_status'] )
				$data['post_status'] = 'draft';

			if( 'draft' == $postarr['original_post_status'] ) {

				$customized = get_post_meta( $postarr['ID'], '_wpbo_template_display', true );

				if( '' == $customized )
					$data['post_status'] = 'draft';

			}

		}

		return apply_filters( 'wpbo_publish_button_action', $data, $postarr );

	}

	/**
	 * Change publish button label.
	 *
	 * Change Publish button label to Save on new popups
	 * as the button action is now save as draft instead of publish.
	 *
	 * @since  1.0.0
	 * @see    Better_Optin_Admin::save_before_publish()
	 * @param  string $translation Current text string
	 * @param  string $text        String translation
	 * @return string              New label
	 */
	public function change_publish_button_label( $translation, $text ) {

		global $typenow;

		// $customized = isset( $_GET['post'] ) ? get_post_meta( $post_id, '_wpbo_template_display', true ) : '';

		if( 'wpbo-popup' == $typenow ) {
			if( ( !isset( $_GET['post'] ) || isset( $_GET['post'] ) && '' == get_post_meta( intval( $_GET['post'] ), '_wpbo_template_display', true ) ) && 'Publish' == $text )
				$translation = __( 'Save', 'wpbo' );
		}

		return apply_filters( 'wpbo_publish_button_label', $translation, $text );
	}

	/**
	 * Unpublished notice.
	 *
	 * Warn the user when the popup is not published yet.
	 *
	 * @since  1.0.0
	 */
	public function unpublished_popup_notice() {

		global $typenow, $post;

		if( 'wpbo-popup' == $typenow && isset( $_GET['post'] ) && isset( $post ) && 'draft' == $post->post_status ): ?>

			<div class="error">
				<p><?php _e( 'This popup is still in draft mode and is <strong>not visible on the site</strong>. Don\'t forget to publish it when you\'re ready.', 'wpbo' ); ?></p>
			</div>

		<?php endif;
		
	}
	
	/**
	 * Disable auto-save for this post type.
	 *
	 * Autosave is causing issues when user clicks the "Customize" button
	 * directly in the template selection metabox.
	 *
	 * Moreover, in our case, auto-save will only affect the popup title
	 * which is not critical.
	 *
	 * @since  1.0.0
	 * @return null
	 */
	public function disable_autosave() {

		if( isset( $_GET['post_type'] ) && 'wpbo-popup' == $_GET['post_type'] || isset( $_GET['post'] ) && 'wpbo-popup' == get_post_type( intval( $_GET['post'] ) ) )
			wp_deregister_script( 'autosave' );

	}

	/**
	 * Dismiss Customizer Tour
	 *
	 * Mark the tour as completed in the user profile
	 * if the tour is actually completed or if the user
	 * closes the popup window.
	 *
	 * @since  1.0.0
	 * @return integer/boolean Row ID on successful update, false on failure
	 */
	public function tour_completed() {

		$user_id = get_current_user_id();

		/* Make sure we have a user */
		if( 0 === $user_id )
			return false;

		/* Get dismissed pointers */
		$dismissed = get_user_meta( $user_id, 'dismissed_wp_pointers', true );
		$pointers  = explode( ',', $dismissed );

		/* Add ours */
		if( !in_array( 'wpbo_tour', $pointers ) )
			array_push( $pointers, 'wpbo_tour' );

		/* Update the dismissed pointers for this user */
		$update = update_user_meta( $user_id, 'dismissed_wp_pointers', implode( ',', $pointers ), $dismissed );

		echo $update;
		die;

	}

	/**
	 * Check if Tour was Completed
	 *
	 * Check the user dismissed pointers and verify if
	 * the tour was already completed (or dismissed).
	 *
	 * @since  1.0.0
	 * @return boolean True if completed, false otherwise
	 */
	public static function is_tour_completed() {

		$user_id = get_current_user_id();

		/* Make sure we have a user */
		if( 0 === $user_id )
			return false;

		/* Get dismissed pointers */
		$dismissed = get_user_meta( $user_id, 'dismissed_wp_pointers', true );
		$pointers  = explode( ',', $dismissed );

		if( in_array( 'wpbo_tour', $pointers ) )
			return true;

		else
			return false;

	}

	/**
	 * Get plugin documentation.
	 *
	 * Use the JSON API to get the doc from
	 * http://support.themeavenue.net
	 *
	 * @since  1.0.0
	 * @return string Documentation page content
	 */
	public function get_documentation() {

		$doc = get_transient( 'wpbo_documentation' );

		if( false === $doc ) {

			$post_id  = 15151;
			$route    = 'http://support.themeavenue.net/wp-json/posts/';
			$response = wp_remote_get( $route . $post_id );

			if( 200 === $response['response']['code'] ) {
			
				$doc = wp_remote_retrieve_body( $response );
				$doc = json_decode( $doc );
				$doc = $doc->content;
				set_transient( 'wpbo_documentation', $doc, 60*60*72 );

			}

		}

		if( false === $doc )
			printf( __( 'Oops! We were unable to fetche the documentation from our support site. Please <a href="%s" target="_blank">click here to see the doc on our site</a>.', 'wpbo' ), esc_url( 'http://support.themeavenue.net/plugins/betteroptin/getting-started/' ) );

		else
			echo $doc;
		
		die;

	}
}