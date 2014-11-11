<?php
class WPBO_Titan {

	/**
	 * Instance of the TItan Framework.
	 *
	 * @since  1.0.0
	 * @var    object
	 */
	public $titan = null;

	/**
	 * Instance of the plugin settings page.
	 *
	 * @since  1.0.0
	 * @var    object
	 */
	public $settings = null;

	public function __construct() {

		/**
		 * Load Titan Framework
		 */
		add_action( 'setup_theme', array( $this, 'load_titan_framework' ), 11 );

	}
	
	/**
	 * Loads Titan Framework.
	 *
	 * Titan Framework is used to handle all plugin options.
	 *
	 * @since 1.0.0
	 */
	public function load_titan_framework() {

		// Don't do anything when we're activating a plugin to prevent errors
		// on redeclaring Titan classes
		if ( ! empty( $_GET['action'] ) && ! empty( $_GET['plugin'] ) ) {
			if ( $_GET['action'] == 'activate' ) {
				return;
			}
		}

		// Check if the framework plugin is activated
		$useEmbeddedFramework = true;
		$activePlugins = get_option('active_plugins');
		if ( is_array( $activePlugins ) ) {
			foreach ( $activePlugins as $plugin ) {
				if ( is_string( $plugin ) ) {
					if ( stripos( $plugin, '/titan-framework.php' ) !== false ) {
						$useEmbeddedFramework = false;
						break;
					}
				}
			}
		}
		// Use the embedded Titan Framework
		if ( $useEmbeddedFramework && ! class_exists( 'TitanFramework' ) ) {
			require_once( WPBO_PATH . 'vendor/gambitph/titan-framework/titan-framework.php' );
		}

		/**
		 * wpbo_before_load_titan hook
		 */
		do_action( 'wpbo_before_load_titan' );

		$this->titan    = TitanFramework::getInstance( 'wpbo' );
		$this->settings = $this->titan->createAdminPanel( array( 'name' => __( 'Settings', 'wpbo' ), 'parent' => 'edit.php?post_type=wpbo-popup', 'position' => 999 ) );

		/* Get all options */
		$options = $this->get_options();

		/* Iterate */
		foreach( $options as $tab => $content ) {

			/* Add a new tab */
			$tab = $this->settings->createTab( array(
				'name'  => $content['name'],
				'title' => isset( $content['title'] ) ? $content['title'] : $content['name'],
				'id'    => $tab
				)
			);

			/* Add all options to current tab */
			foreach( $content['options'] as $option ) {
				$tab->createOption( $option );
			}

			$tab->createOption( array( 'type' => 'save', ) );

		}

	}

	protected function get_options() {
		return apply_filters( 'wpbo_plugin_settings', array() );
	}

}