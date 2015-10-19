<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TitanFrameworkOptionAweber extends TitanFrameworkOption {

	public $defaultSecondarySettings = array(
		'placeholder' => '', // show this when blank
		'is_password' => false,
	);

	/*public function __construct() {
		add_action( 'wp_ajax_tf_check_license', array( $this, 'check_license' ) );
	}*/

	/*
	 * Display for options and meta
	 */
	public function display() {

		$url = WPBO_Aweber::get_authorization_url();

		$this->echoOptionHeader();
		printf("<input class=\"regular-text wpbo-aweber\" name=\"%s\" placeholder=\"%s\" id=\"%s\" type=\"%s\" value=\"%s\" />",
			$this->getID(),
			$this->settings['placeholder'],
			$this->getID(),
			$this->settings['is_password'] ? 'password' : 'text',
			esc_attr( $this->getValue() ) );

		printf( " <a href=\"%s\" id=\"wpbo-aweber-auth\" class=\"button-secondary\" target=\"_blank\">%s</a>", $url, __( 'Authorize', 'wpbo' ) ); ?>

		<div id="tav-license-status">
			<div id="tav-license-status-empty" style="display: none; color: red;" class="tav-license-status"><p><?php _e( 'You did not enter your Envato purchase code.', 'wpbp' ); ?></p></div>
			<div id="tav-license-status-error" style="display: none; color: red;" class="tav-license-status"><p><?php _e( 'Validation of your Envato purchase code failed.', 'wpbp' ); ?></p></div>
			<div id="tav-license-status-ajaxfail" style="display: none; color: red;" class="tav-license-status"><p><?php _e( 'We were not able to check your Envato purchase code. Please try again later.', 'wpbp' ); ?></p></div>
			<div id="tav-license-status-valid" style="display: none; color: green;" class="tav-license-status"><p><?php _e( 'The license you entered is valid.', 'wpbp' ); ?></p></div>
		</div>

		<?php $this->echoOptionFooter();
	}

	/*
	 * Display for theme customizer
	 */
	public function registerCustomizerControl( $wp_customize, $section, $priority = 1 ) {
		$wp_customize->add_control( new TitanFrameworkCustomizeControl( $wp_customize, $this->getID(), array(
			'label' => $this->settings['name'],
			'section' => $section->settings['id'],
			'settings' => $this->getID(),
			'description' => $this->settings['desc'],
			'priority' => $priority,
		) ) );
	}
}