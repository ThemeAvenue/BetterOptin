<?php
$post_id = isset( $_GET['post'] ) ? $_GET['post'] : false;

$animations = array(
	'bounceIn'          => 'bounceIn',
	'bounceInDown'      => 'bounceInDown',
	'bounceInLeft'      => 'bounceInLeft',
	'bounceInRight'     => 'bounceInRight',
	'bounceInUp'        => 'bounceInUp',
	'fadeIn'            => 'fadeIn',
	'fadeInDown'        => 'fadeInDown',
	'fadeInDownBig'     => 'fadeInDownBig',
	'fadeInLeft'        => 'fadeInLeft',
	'fadeInLeftBig'     => 'fadeInLeftBig',
	'fadeInRight'       => 'fadeInRight',
	'fadeInRightBig'    => 'fadeInRightBig',
	'fadeInUp'          => 'fadeInUp',
	'fadeInUpBig'       => 'fadeInUpBig',
	'rotateIn'          => 'rotateIn',
	'rotateInDownLeft'  => 'rotateInDownLeft',
	'rotateInDownRight' => 'rotateInDownRight',
	'rotateInUpLeft'    => 'rotateInUpLeft',
	'rotateInUpRight'   => 'rotateInUpRight',
	'slideInLeft'       => 'slideInLeft',
	'slideInRight'      => 'slideInRight',
	'slideInDown'       => 'slideInDown',
	'rollIn'            => 'rollIn',
	'flipInX'           => 'flipInX',
	'flipInY'           => 'flipInY',
	'lightSpeedIn'      => 'lightSpeedIn',
);
?>
<fieldset class="wpbo-step" data-step="2">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_close_overlay"><?php _e( 'Close on Overlay', 'wpbo' ); ?></label>
				</th>
				<td>
					<label for="wpbo_close_overlay">
						<input name="wpbo_settings[close_overlay]" type="checkbox" id="wpbo_close_overlay" value="1" <?php if( '1' == wpbo_get_option( 'close_overlay', '0', $post_id ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'wpbo' ); ?>
					</label>
					<p class="description"><?php _e( 'Popup closes when user clicks anywhere outside the popup.', 'wpbo' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_close_esc"><?php _e( 'Close on &laquo;ESC&raquo;', 'wpbo' ); ?></label>
				</th>
				<td>
					<label for="wpbo_close_esc">
						<input name="wpbo_settings[close_esc]" type="checkbox" id="wpbo_close_esc" value="1" <?php if( '1' == wpbo_get_option( 'close_esc', '0', $post_id ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'wpbo' ); ?>
					</label>
					<p class="description"><?php _e( 'Popup closes when user hits the "Esc" key.', 'wpbo' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_close_esc"><?php _e( 'Wiggle when Overlay Clicked', 'wpbo' ); ?></label>
				</th>
				<td>
					<label for="wpbo_wiggle">
						<input name="wpbo_settings[wiggle]" type="checkbox" id="wpbo_wiggle" value="1" <?php if( '1' == wpbo_get_option( 'wiggle', '0', $post_id ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'wpbo' ); ?>
					</label>
					<p class="description"><?php _e( 'Popup wiggles when the users clicks the overlay.', 'wpbo' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_cookie_lifetime"><?php _e( 'Cookie Lifetime', 'wpbo' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[cookie_lifetime]" type="text" id="wpbo_cookie_lifetime" class="small-text" value="<?php echo wpbo_get_option( 'cookie_lifetime', '30', $post_id ); ?>">
					<p class="description"><?php _e( 'Delay before a visitor sees the popup again after closing it (in days).', 'wpbo' ); ?></p>
				</td>
			</tr>
			<!-- <tr valign="top">
				<th scope="row">
					<label for="wpbo_animation"><?php _e( 'Popup Animation', 'wpbo' ); ?></label>
				</th>
				<td>
					<select name="wpbo_settings[animation]" id="wpbo_animation">

						<?php
						foreach( $animations as $id => $name ) { ?>
							<option value="<?php echo $id; ?>"><?php echo $name; ?></option>
						<?php }
						?>

					</select>
					<p class="description"><?php _e( 'Animation to use when the popup appears.', 'wpbo' ); ?></p>
				</td>
			</tr> -->
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_overlay_color"><?php _e( 'Overlay Color', 'wpbo' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[overlay_color]" type="text" id="wpbo-overlay-color" class="tav-colorpicker" value="<?php echo wpbo_get_option( 'overlay_color', '#000', $post_id ); ?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_overlay_opacity"><?php _e( 'Overlay Opacity', 'wpbo' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[overlay_opacity]" type="range" id="wpbo-overlay-opacity" min="0" max="1" step="0.1" class="tav-range" value="<?php echo wpbo_get_option( 'overlay_opacity', '0.5', $post_id ); ?>" oninput="this.form.amount.value=this.value">
					<output name="amount" for="wpbo-overlay-opacity"><?php echo wpbo_get_option( 'overlay_opacity', '0.5', $post_id ); ?></output>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_return_url"><?php _e( 'Return URL', 'wpbo' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[return_url]" type="url" class="medium-text" id="wpbo_return_url"value="<?php echo wpbo_get_option( 'return_url', '', $post_id ); ?>">
					<p><?php printf( __( 'The return URL is optional. If not specified, the URL set in the general settings will be used (currently %s)', 'wpbo' ), '<code>' . wpbo_get_option( 'return_url' ) . '</code>' ); ?></p>
				</td>
			</tr>
			<!-- <tr valign="top">
				<th scope="row">
					<label for="wpbo_close_button"><?php _e( 'Close Button', 'wpbo' ); ?></label>
				</th>
				<td>
					<label for="wpbo_close_button">
						<input name="wpbo_settings[close_button]" type="checkbox" id="wpbo_close_button" value="1" <?php if( '1' == wpbo_get_option( 'close_button', '1', $post_id ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'wpbo' ); ?>
					</label>
					<p class="description"><?php _e( 'Give the visitor a button to close the popup.', 'wpbo' ); ?></p>
				</td>
			</tr> -->
		</tbody>
	</table>
</fieldset>