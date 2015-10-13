<?php
global $wpbo_submit, $post;

if( ! WPBO_MC()->is_mailchimp_ready() ) {

	?><p class="wpbo-warning"><?php printf( __( 'MailChimp integration is not correctly setup. Your popups won\'t send subscribers anywhere! <a href="%s">Click here to see the settings</a>.', 'betteroptin' ), esc_url( add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'edit.php?post_type=wpbo-popup-settings&tab=mailchimp' ), admin_url( 'edit.php' ) ) ) ); ?></p><?php

} else {

	$lists    = WPBO_MC()->get_lists();
	$default  = wpbo_get_option( 'mc_list_id' );
	$opts     = array();
	$value    = isset( $_GET['post'] ) ? get_post_meta( intval( $_GET['post'] ), 'wpbo_mc_list', true ) : '';

	if( isset( $lists['data'] ) && is_array( $lists['data'] ) ) {

		foreach( $lists['data'] as $key => $list ) {
			$opts[$list['id']] = $list['name'];
		}

		$def_name = $opts[$default];

	} else { ?>

		<p><?php printf( __( 'If you don\'t specify a list here, the default one will be used (currently %s).', 'betteroptin' ), "<code>$default</code>" ); ?></p>
		<input type="text" id="wpbo_mc_list" name="wpbo_mc_list" style="width:100%" value="<?php echo $value; ?>" placeholder="<?php _e( 'List ID', 'betteroptin' ); ?>">
		
		<?php
		return;

	} ?>

	<p><?php _e( 'If you don\'t select a list here, the default one will be used.', 'betteroptin' ); ?></p>

	<select id="wpbo_mc_list" name="wpbo_mc_list" style="width:100%">
		<option value="" <?php if( '' == $value ): ?>selected="selected" data-listid="<?php echo $default; ?>"<?php endif; ?>><?php printf( __( 'Default (%s)', 'betteroptin' ), $def_name ); ?></option>
		<?php
		foreach( $opts as $id => $name ) { ?>
			<option value="<?php echo $id; ?>" <?php if( $id == $value ): ?>selected="selected"<?php endif; ?>><?php echo $name; ?></option>
		<?php }
		?>

	</select>

	<style type="text/css">
	.ta-metabox-subheading {
		font-weight: bold;
		margin: 1.25em 0 0.25em 0;
	}
	.ta-label-block {
		display: block;
		margin-bottom: 0.5em;
	}
	</style>

	<script type="text/javascript">
	jQuery(document).ready(function ($) {

		var listId, groupPlaceholder;

		$('select[name="wpbo_mc_list"]').on('change', function (event) {
			event.preventDefault();

			listId = $('option:selected', $(this)).val();
			groupPlaceholder = $('.mc-group-wrapper');

			// If using default list
			if (listId === '') {
				listId = $('option:selected', $(this)).data('listid');
			}

			// While loading...
			groupPlaceholder.html('<img src="<?php echo get_admin_url(); ?>images/wpspin_light.gif" alt="<?php esc_html_e( 'Loading...', 'betteroptin' ); ?>">');

			// Ajax below
			var data = {
				'action': 'mc_get_groups',
				'mc_list_id': listId,
				'mc_post_id': <?php echo $post->ID; ?>
			};
			$.post(ajaxurl, data, function (response) {
				groupPlaceholder.html(response);
			});

		});

		$('select[name="wpbo_mc_list"]').trigger('change');
	});
	</script>

	<div class="ta-metabox-subheading"><?php _e( 'Select Group(s):', 'betteroptin' ); ?></div>
	<div class="mc-group-wrapper"><img src="<?php echo get_admin_url(); ?>images/wpspin_light.gif" alt="<?php _e( 'Loading...', 'betteroptin' ); ?>"></div>

<?php }