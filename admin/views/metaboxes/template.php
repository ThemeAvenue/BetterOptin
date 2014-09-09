<?php
$post_id   = isset( $_GET['post'] ) ? $_GET['post'] : false;
$templates = Better_Optin_Admin::get_templates_list();
$current   = get_post_meta( $post_id, 'wpbo_template', true );
?>

<p><?php _e('Click on a thumbnail to preview in full size.', 'wpbo') ?></p>

<div class="tav-clearfix" id="wpbo-select-template">
	<fieldset class="wpbo-step" data-step="1">
		<?php
		foreach( $templates as $template => $image ) { ?>

			<div class="ta-ri-wrap">
				<label>
					<input type="radio" name="wpbo_template" id="wpbo_template_<?php echo $template; ?>" class="ta-ri" value="<?php echo $template; ?>" <?php if( $current == $template ): ?>checked="checked"<?php endif; ?>>
					<img src="<?php echo $image; ?>" alt="<?php echo $template; ?>" class="ta-ri-img">
				</label>
				<div class="ta-ri-crtl">
					<button class="button-primary"><?php _e( 'Select', 'wpbo' ); ?></button>
					<button type="submit" class="button-secondary" name="save_customize"><?php _e( 'Customize', 'wpbo' ); ?></button>
				</div>
				<span class="ta-ri-crtl-ico">&#10003;</span>
			</div>

		<?php } ?>
	</fieldset>
</div>