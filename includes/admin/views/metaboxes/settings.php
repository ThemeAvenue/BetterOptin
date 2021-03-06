<?php
global $post;

$post_id = isset( $post->ID ) ? $post->ID : 0;
$popup = new WPBO_Popup( $post_id );

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
					<label for="wpbo_close_overlay"><?php _e( 'Close on Overlay', 'betteroptin' ); ?></label>
				</th>
				<td>
					<label for="wpbo_close_overlay">
						<input name="wpbo_settings[close_overlay]" type="checkbox" id="wpbo_close_overlay" value="1" <?php if( '1' == $popup->option( 'close_overlay', '0' ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'betteroptin' ); ?>
					</label>
					<p class="description"><?php _e( 'Popup closes when user clicks anywhere outside the popup.', 'betteroptin' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_close_esc"><?php _e( 'Close on &laquo;ESC&raquo;', 'betteroptin' ); ?></label>
				</th>
				<td>
					<label for="wpbo_close_esc">
						<input name="wpbo_settings[close_esc]" type="checkbox" id="wpbo_close_esc" value="1" <?php if( '1' == $popup->option( 'close_esc', '0' ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'betteroptin' ); ?>
					</label>
					<p class="description"><?php _e( 'Popup closes when user hits the "Esc" key.', 'betteroptin' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_close_esc"><?php _e( 'Hide Close Button', 'betteroptin' ); ?></label>
				</th>
				<td>
					<label for="wpbo_hide_close_button">
						<input name="wpbo_settings[hide_close_button]" type="checkbox" id="wpbo_hide_close_button" value="1" <?php if( '1' == $popup->option( 'hide_close_button', '0' ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'betteroptin' ); ?>
					</label>
					<p class="description"><?php _e( 'Do you want to hide the close button that appears on the top right corner of the popup.', 'betteroptin' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_close_esc"><?php _e( 'Wiggle when Overlay Clicked', 'betteroptin' ); ?></label>
				</th>
				<td>
					<label for="wpbo_wiggle">
						<input name="wpbo_settings[wiggle]" type="checkbox" id="wpbo_wiggle" value="1" <?php if( '1' == $popup->option( 'wiggle', '0' ) ): ?>checked="checked"<?php endif; ?>>
						<?php _e( 'Yes', 'betteroptin' ); ?>
					</label>
					<p class="description"><?php _e( 'Popup wiggles when the users clicks the overlay.', 'betteroptin' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_cookie_lifetime"><?php _e( 'Cookie Lifetime', 'betteroptin' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[cookie_lifetime]" type="text" id="wpbo_cookie_lifetime" class="small-text" value="<?php echo $popup->option( 'cookie_lifetime', '30' ); ?>">
					<p class="description"><?php _e( 'Delay before a visitor sees the popup again after closing it (in days).', 'betteroptin' ); ?></p>
				</td>
			</tr>
			<!-- <tr valign="top">
				<th scope="row">
					<label for="wpbo_animation"><?php _e( 'Popup Animation', 'betteroptin' ); ?></label>
				</th>
				<td>
					<select name="wpbo_settings[animation]" id="wpbo_animation">

						<?php
						foreach( $animations as $id => $name ) { ?>
							<option value="<?php echo $id; ?>"><?php echo $name; ?></option>
						<?php }
						?>

					</select>
					<p class="description"><?php _e( 'Animation to use when the popup appears.', 'betteroptin' ); ?></p>
				</td>
			</tr> -->
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_overlay_color"><?php _e( 'Overlay Color', 'betteroptin' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[overlay_color]" type="text" id="wpbo-overlay-color" class="tav-colorpicker" value="<?php echo $popup->option( 'overlay_color', '#000' ); ?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_overlay_opacity"><?php _e( 'Overlay Opacity', 'betteroptin' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[overlay_opacity]" type="range" id="wpbo-overlay-opacity" min="0" max="1" step="0.1" class="tav-range" value="<?php echo $popup->option( 'overlay_opacity', '0.5' ); ?>" oninput="this.form.amount.value=this.value">
					<output name="amount" for="wpbo-overlay-opacity"><?php echo $popup->option( 'overlay_opacity', '0.5' ); ?></output>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wpbo_return_url"><?php _e( 'Return URL', 'betteroptin' ); ?></label>
				</th>
				<td>
					<input name="wpbo_settings[return_url]" type="text" class="regular-text" id="wpbo_return_url"value="<?php echo $popup->option( 'return_url', '' ); ?>">
					<?php $returl = '' == ( $url = wpbo_get_option( 'return_url' ) ) ? 'none' : esc_url( $url ); ?>
					<p><?php wp_kses( printf( __( 'You can use the post ID, page ID or custom URL. <a href="%s" target="_blank">How To Find The Post ID In WordPress</a>.', 'betteroptin' ), esc_url( 'https://pagely.com/blog/2015/04/find-post-id-wordpress/' ) ), array(  'a' => array( 'href' => array() ) ) ); ?></p>
					<p><?php printf( __( 'The return URL is optional. If not specified, the URL set in the general settings will be used (currently %s)', 'betteroptin' ), "<code>$returl</code>" ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>