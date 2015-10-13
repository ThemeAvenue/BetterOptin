<?php
class WPMC_MailChimp_Groups {

	/**
	 * @var string ID of the current list group
	 * @since 1.1
	 */
	protected $group_id;

	/**
	 * @var array Group values
	 * @since 1.1
	 */
	protected $options;

	/**
	 * @var int Current popup (post) ID
	 * @since 1.1
	 */
	protected $post_id;

	public function __construct( $group_id = '', $options = array(), $post_id = 0 ) {

		if ( ! empty( $group_id ) && ! empty( $options ) ) {
		
			$this->group_name = $group_id;
			$this->options    = $options;
			$this->post_id    = $post_id;

		}

	}

	/**
	 * Get the value.
	 *
	 * Get the value for a specific group.
	 *
	 * @since  1.1.0
	 * @param  integer  $group  The group ID
	 * @param  boolean  $post_id Post ID
	 * @return array             Array of values for this group (empty array if no values)
	 */
	public function get_value( $group, $post_id = false ) {

		/* Make sure we have a post ID to check */
		if ( false === $post_id ) {
			$post_id = $this->post_id;
		}

		$value = maybe_unserialize( get_post_meta( $post_id, 'wpbo_mc_list_groups', true ) );

		if ( isset( $value[$group] ) ) {
			return apply_filters( "wpmc_mailchimp_groups_value_$group", $value[$group] );
		} else {
			return array();
		}
		

	}

	/**
	 * Display group options as checkboxes.
	 *
	 * @since  1.1.0
	 */
	public function show_group_type_checkboxes() {

		do_action( 'wpmc_mailchimp_groups_type_checkboxes_before', $this->group_name ); ?>

		<fieldset>
			<?php
			foreach( $this->options as $option ):

				$value   = $this->get_value( $this->group_name );
				$checked = in_array( $option['name'], $value ) ? 'checked="checked"' : '';
				?>
				<legend class="screen-reader-text"><span><?php echo $option['name']; ?></span></legend>
				<label for="wpbo_mc_group_<?php echo $option['id']; ?>" class="ta-label-block">
						<input name="wpbo_mc_list_groups[<?php echo $this->group_name; ?>][]" type="checkbox" id="wpbo_mc_group_<?php echo $option['id']; ?>" value="<?php echo $option['name']; ?>" <?php echo $checked; ?>> <?php echo $option['name']; ?>
				</label>
			<?php endforeach; ?>
		</fieldset>

		<?php
		do_action( 'wpmc_mailchimp_groups_type_checkboxes_after', $this->group_name );

	}

	/**
	 * Display group options as radio.
	 *
	 * @since  1.1.0
	 */
	public function show_group_type_radio() {

		do_action( 'wpmc_mailchimp_groups_type_radio_before', $this->group_name ); ?>

		<fieldset>
			<?php
			foreach( $this->options as $option ):

				$value   = $this->get_value( $this->group_name );
				$checked = $option['name'] == $value ? 'checked="checked"' : '';
				?>
				<label>
					<input type="radio" name="wpbo_mc_list_groups[<?php echo $this->group_name; ?>]" value="<?php echo $option['name']; ?>" <?php echo $checked; ?>> 
					<span><?php echo $option['name']; ?></span>
				</label>
				<br>
			<?php endforeach; ?>
		</fieldset>

		<?php
		do_action( 'wpmc_mailchimp_groups_type_radio_after', $this->group_name );

	}

	/**
	 * Display group options as dropdown.
	 *
	 * @since  1.1.0
	 */
	public function show_group_type_dropdown() {

		do_action( 'wpmc_mailchimp_groups_type_dropdown_before', $this->group_name ); ?>

		<select name="wpbo_mc_list_groups[<?php echo $this->group_name; ?>]" id="wpbo_mc_list_groups_<?php echo $this->group_name; ?>" style="width:100%">
			<?php
			foreach( $this->options as $option ):

				$value   = $this->get_value( $this->group_name );
				$checked = ( $option['name'] == $value ) ? 'selected="selected"' : '';
				?>

				<option value="<?php echo $option['name']; ?>" <?php echo $checked; ?>><?php echo $option['name']; ?></option>

			<?php endforeach; ?>
		</select>

		<?php
		do_action( 'wpmc_mailchimp_groups_type_dropdown_after', $this->group_name );

	}

}