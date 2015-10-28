<?php
if ( ! class_exists( 'WPBO_Provider_MailPoet' ) ) {

	class WPBO_Provider_MailPoet {

		/**
		 * Trigger form submission.
		 *
		 * Add a last couple of checks, set the redirects and
		 * finally subscribe the visitor to the MailChimp list.
		 *
		 * @since  1.0.0
		 *
		 * @param array $data Form data
		 *
		 * @return bool
		 */
		public static function submit( $data ) {

			$user_id = self::subscribe( $data );

			if ( ! is_int( $user_id ) ) {
				return false;
			}

			return true;

		}

		/**
		 * Subscribe the visitor to a list.
		 *
		 * @since  1.0.0
		 *
		 * @param array $data Form data
		 *
		 * @return array Result
		 */
		protected function subscribe( $data ) {

			$email       = sanitize_email( $data['email'] );
			$list_id     = wpbo_get_option( 'mp_list_id', '' );
			$popup_id    = (int) $data['wpbo_id'];
			$custom_list = get_post_meta( $popup_id, 'wpbo_mp_list', true );
			$list_id     = '' != $custom_list ? $custom_list : $list_id;

			// Possibly get additional fields
			$first_name = isset( $data['first_name'] ) ? sanitize_text_field( $data['first_name'] ) : sanitize_key( $data['name'] );
			$last_name  = isset( $data['last_name'] ) ? sanitize_text_field( $data['last_name'] ) : '';

			$user_data = array(
				'email'     => $email,
				'firstname' => apply_filters( 'bomp_subscriber_first_name', $first_name ),
				'lastname'  => apply_filters( 'bomp_subscriber_last_name', $last_name )
			);

			$data_subscriber = array(
				'user'      => $user_data,
				'user_list' => array( 'list_ids' => array( $list_id ) )
			);

			$helper_user = WYSIJA::get( 'user', 'helper' );
			$add         = $helper_user->addSubscriber( $data_subscriber );

			return $add;

		}

	}
}