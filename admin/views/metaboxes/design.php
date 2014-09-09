<?php $template = isset( $_GET['post'] ) ? get_post_meta( $_GET['post'], 'wpbo_template', true ) : ''; ?>

<div id="wpbo-customizer-ready" class="wpbo-success" <?php if( '' == $template ): ?>style="display: none;"<?php endif; ?>>
	<p>&#10004; <strong><?php _e( 'You are now ready to customize the popup template.', 'wpbo' ); ?></strong></p>
	<p><?php _e( 'Please click the below button to show the customizer.', 'wpbo' ); ?></p>
</div>

<?php if( '' == $template ): ?>

	<div id="wpbo-customizer-not-ready" class="wpbo-warning">
		<p><?php _e( 'Please select a template before customizing the popup.', 'wpbo' ); ?></p>
	</div>

<?php endif; ?>

<input type="submit" class="button-secondary" name="save_customize" value="<?php _e( 'Customize', 'wpbo' ); ?>" <?php if( '' == $template ): ?>disabled="disabled"<?php endif; ?>>