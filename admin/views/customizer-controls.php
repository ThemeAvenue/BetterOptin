<?php
/**
 * Prepare action links
 */
$args = array(
	'wpbo_popup' => isset( $_GET['wpbo_popup'] ) ? intval( $_GET['wpbo_popup'] ) : 0,
	'post_type'  => 'wpbo-popup',
	'page'       => 'wpbo-customizer',
	'wpbo_reset' => wp_create_nonce( 'reset_template' )
);

$reset   = add_query_arg( $args, admin_url( 'edit.php' ) );
$cancel  = add_query_arg( array( 'post' => $popup_id, 'action' => 'edit' ), admin_url( 'post.php' ) );
$refresh = add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-customizer', 'wpbo_popup' => $popup_id ), admin_url( 'edit.php' ) );
?>

<div class="taed-sidebar">

	<div class="taed-sidebar-info">
		<div><?php _e( 'You are now editing', 'wpbo' ); ?></div>
		<div><?php echo get_the_title( $popup_id ); ?></div>
	</div>

	<div class="taed-buttons">
		<form method="post" action="<?php echo add_query_arg( array( 'post_type' => 'wpbo-popup', 'page' => 'wpbo-customizer', 'wpbo_popup' => $popup_id ), admin_url( 'edit.php' ) ); ?>">

			<button class="button-primary taed-save"><?php _e( 'Save', 'wpbo' ); ?></button>
			<a class="button-secondary taed-cancel" title="<?php _e( 'Are you sure? You will lose all unsaved changes. You can\'t undo this action.', 'wpbo' ); ?>" href="<?php echo $refresh; ?>"><?php _e( 'Cancel', 'wpbo' ); ?></button>
			<a href="<?php echo $reset; ?>" class="button-secondary taed-reset" title="<?php _e( 'Are you sure? This will reset to the original Template', 'wpbo' ); ?>"><?php _e( 'Reset', 'wpbo' ); ?></a>

			<fieldset style="display:none;">
				<input id="taed-google-font" name="taed-google-font" type="hidden">
				<textarea id="taed-outerhtml" name="taed-outerhtml"></textarea>
				<textarea id="taed-outerhtmlclean" name="taed-outerhtmlclean"></textarea>
				<?php wp_nonce_field( 'wpbo_customize_template', 'wpbo_nonce', true, true ); ?>
			</fieldset>
		</form>
	</div>

	<div class="taed-field taed-modalsize">
		<label><?php _e( 'Popup Size (in <em>pixels</em>)', 'wpbo' ); ?></label>
		<div class="clearfix">
			<div class="taed-third">
				<input class="form-control taed-modalwidth" type="text" name="" value="" placeholder="Width">
			</div>
			<div class="taed-third">
				<input class="form-control taed-modalheight" type="text" name="" value="" placeholder="Height">
			</div>
			<div class="taed-third">
				<a id="taed-modalsize-reset" href="#" class="button-secondary" title="<?php _e( 'Click here to reset to default size.', 'wpbo' ); ?>"><?php _e( 'Default', 'wpbo' ); ?></a>
			</div>
		</div>
	</div>

	<div class="taed-field taed-textEdit">
		<label for="taed-textedit-textarea"><?php _e( 'Edit content', 'wpbo' ); ?></label>
		<textarea id="taed-textedit-textarea" class="form-control"><?php _e( 'Error loading content', 'wpbo' ); ?></textarea>
	</div>

	<div class="taed-field taed-tinymce">
		<label for="taed-tinymce-textarea"><?php _e( 'Edit content', 'wpbo' ); ?></label>
		<?php
		// @NOTE: The tinymce parameter allows you to pass configuration options directly to TinyMCE. See http://stackoverflow.com/a/13293543/1414881
		if( version_compare( get_bloginfo( 'version' ), '3.9', '>=' ) ) {

			$args = array(
				'media_buttons' => false,
				'textarea_rows' => 5,
				'quicktags'     => false,
				'editor_class'  => 'taed-tinymce',
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,underline,strikethrough,hr,|,bullist,numlist,|,link,unlink',
					'toolbar2' => ''
					)
				);

			wp_editor( '', 'taed-tinymce-textarea', $args );

		} else {

			$args = array(
				'media_buttons' => false,
				'textarea_rows' => 5,
				'quicktags'     => false,
				'editor_class'  => 'taed-tinymce',
				'tinymce'       => array(
					'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink',
					'theme_advanced_buttons2' => ''
					)
				);

			wp_editor( '', 'taed-tinymce-textarea', $args );
		}
		?>
	</div>

	<div class="taed-field taed-backgroundColor">
		<label for="taed-bg-color"><?php _e( 'Background Color', 'wpbo' ); ?></label>
		<input id="taed-bg-color" class="form-control" type="text" name="" value="">
	</div>

	<div class="taed-field taed-backgroundImage">
		<label for="taed-bg-img"><?php _e( 'Background Image', 'wpbo' ); ?></label>
		<input id="taed-bg-img" name="" type="text" class="form-control tgm-new-media-image" value="" />
		<input class="tgm-open-media button" value="<?php _e( 'Upload', 'wpbo' ); ?>" type="button" data-tgmtitle="<?php _e( 'Upload a background image', 'wpbo' ); ?>" data-tgmbtn="<?php _e( 'Apply background image', 'wpbo' ); ?>" data-tgmlibtype="['image']" />
		<p class="taed-helper"><?php printf( __( 'If you\'re looking for seamless patterns, take a look at <a href="%s" target="_blank">Subtle Patterns</a>', 'wpbo' ), esc_url( 'http://subtlepatterns.com/thumbnail-view/' ) ); ?></p>
	</div>

	<div class="taed-field taed-img">
		<label for="taed-img"><?php _e( 'Image URL', 'wpbo' ); ?></label>
		<input id="taed-img" name="" type="text" class="form-control tgm-new-media-image" value="" />
		<input class="tgm-open-media button" value="<?php _e( 'Upload', 'wpbo' ); ?>" type="button" data-tgmtitle="<?php _e( 'Upload a new image', 'wpbo' ); ?>" data-tgmbtn="<?php _e( 'Insert new image', 'wpbo' ); ?>" data-tgmlibtype="['image']" />
		<p class="taed-helper"><?php printf( __( 'The maximum width allowed is %s.', 'wpbo' ), '<code>0px</code>' ); ?> <span class="wpbo-danger"><?php _e( 'Make sure to resize your image for maximum performance!', 'wpbo' ); ?></span></p>
	</div>

	<div class="taed-field taed-backgroundRepeat">
		<label for="taed-bg-repeat"><?php _e( 'Background Repeat', 'wpbo' ); ?></label>
		<select id="taed-bg-repeat" class="form-control" name="">
			<option value="repeat" selected="selected"><?php _e( 'Repeat', 'wpbo' ); ?></option>
			<option value="no-repeat"><?php _e( 'No-Repeat', 'wpbo' ); ?></option>
			<option value="repeat-x"><?php _e( 'Repeat Horizontally', 'wpbo' ); ?></option>
			<option value="repeat-y"><?php _e( 'Repeat Vertically', 'wpbo' ); ?></option>
		</select>
	</div>

	<div class="taed-field taed-backgroundPosition">
		<label for="taed-bg-position"><?php _e( 'Background Position', 'wpbo' ); ?></label>
		<input id="taed-bg-position" class="form-control" type="text" name="" value="">
		<p class="taed-helper"><?php _e( 'Please use the following format: <code>horizontal vertical</code>', 'wpbo' ); ?></p>
	</div>

	<div class="taed-field taed-backgroundSize">
		<label for="taed-bg-size"><?php _e( 'Background Size', 'wpbo' ); ?></label>
		<select id="taed-bg-size" class="form-control" name="">
			<option value="auto" selected="selected"><?php _e( 'Auto', 'wpbo' ); ?></option>
			<option value="cover"><?php _e( 'Cover', 'wpbo' ); ?></option>
			<option value="contain"><?php _e( 'Contain', 'wpbo' ); ?></option>
		</select>
		<p class="taed-helper"><?php _e( 'Specify the size of the background image', 'wpbo' ); ?></p>
	</div>

	<div class="taed-field taed-color">
		<label for="taed-color"><?php _e( 'Text Color', 'wpbo' ); ?></label>
		<input id="taed-color" class="form-control" type="text" name="" value="">
	</div>

	<div class="taed-field taed-fontFamily">
		<label for="taed-font-family"><?php _e( 'Font Family', 'wpbo' ); ?></label>
		<select id="taed-font-family" class="form-control">
			<?php
			$fonts = Better_Optin_Admin::get_font_stack();

			if( is_array( $fonts ) ) {
				foreach( $fonts as $font ) {

					$font_stack = str_replace( '"', "'", str_replace( array( 'font-family: ', ';' ), '', $font['font_stack'] ) ); ?>

					<option value="<?php echo $font_stack; ?>"><?php echo $font['font_name']; ?></option>

				<?php }
			} ?>
		</select>
	</div>

	<div class="taed-field taed-fontSize">
		<label for="taed-font-size"><?php _e( 'Font Size', 'wpbo' ); ?></label>
		<input id="taed-font-size" class="form-control" type="range" id="fontSize" name="" min="10" max="42" step="2">
		<output for="fontSize" id="fontSizeValue"></output>
	</div>

	<div class="taed-field taed-textAlign">
		<label for="taed-text-align"><?php _e( 'Text Alignment', 'wpbo' ); ?></label>
		<input type="hidden" class="form-control">
		<div class="taed-textAlign-select">
			<div data-align="start" class="mce-widget mce-btn" role="button" aria-label="Align left">
				<button role="presentation" type="button"><i class="mce-ico mce-i-alignleft"></i>
				</button>
			</div>
			<div data-align="center" class="mce-widget mce-btn" role="button" aria-label="Align center">
				<button role="presentation" type="button"><i class="mce-ico mce-i-aligncenter"></i>
				</button>
			</div>
			<div data-align="right" class="mce-widget mce-btn" role="button" aria-label="Align right">
				<button role="presentation" type="button"><i class="mce-ico mce-i-alignright"></i>
				</button>
			</div>
			<div data-align="justify" class="mce-widget mce-btn" role="button" aria-label="Align justify">
				<button role="presentation" type="button"><i class="mce-ico mce-i-alignjustify"></i>
				</button>
			</div>
		</div>
	</div>

</div>