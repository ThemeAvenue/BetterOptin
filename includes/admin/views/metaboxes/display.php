<?php
$post_types    = get_post_types( array( 'public' => true ) );
$except        = array( 'attachment', 'wpbo-popup' );
$all           = isset( $_GET['post'] ) ? get_post_meta( intval( $_GET['post'] ), '_wpbo_display_all', true ) : '';
$relationships = get_option( 'wpbo_popup_relationships', array() );
?>
<p><?php _e( 'Please select the pages where you want this popup to show up. You can select multiple posts in each post type.', 'betteroptin' ); ?></p>
<fieldset class="wpbo-step"  data-step="3">
	<table class="form-table">
	<tbody>
		<tr valign="top" class="even first">
			<th scope="row" class="first last">
				<label for="wpbo_all"><?php _e( 'Everywhere', 'betteroptin' ); ?></label>
			</th>
			<td>
				<label for="wpbo_all">
					<input type="checkbox" name="wpbo_display_all" id="wpbo_all" value="all" <?php if( 'yes' == $all ): ?>checked="checked"<?php endif; ?>> 
					<?php _e( 'Display this popup everywhere on the site.', 'betteroptin' ); ?>
				</label>
			</td>
		</tr>

	<?php
	foreach( $post_types as $key => $pt ) {

		if( in_array( $key, $except ) )
			continue;

		$warning     = false;
		$list        = array();
		$name        = ucwords( $pt );
		$placeholder = sprintf( __( 'Select one or more %s', 'betteroptin' ), $name );
		$pt_display  = isset( $_GET['post'] ) ? get_post_meta( intval( $_GET['post'] ), '_wpbo_display_' . $pt, true ) : false;

		$args        = array(
			'post_type'              => $key,
			'post_status'            => 'publish',
			'order'                  => 'DESC',
			'orderby'                => 'post_title',
			'posts_per_page'         => -1,
			'no_found_rows'          => false,
			'cache_results'          => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);

		$posts = new WP_Query( $args );
		
		if( !empty( $posts->posts ) ) { ?>

			<tr valign="top" class="even first">
				<th scope="row" class="first last">
					<label for="wpbo_<?php echo $pt; ?>"><strong><?php echo $name; ?></strong></label>
				</th>
				<td>
					<div class="wpbo-multi-select" <?php if( 'all' == $pt_display ): ?>style="display:none;"<?php endif; ?>>
						<select name="wpbo_display_<?php echo $pt; ?>[]" id="wpbo_<?php echo $pt; ?>" data-placeholder="<?php echo $placeholder; ?>" style="width:350px;" multiple class="chosen-select">

						<?php
						foreach( $posts->posts as $post ) {

							$post_id    = $post->ID;
							$post_title = $post->post_title;
							$select     = ( is_array( $pt_display ) && in_array( $post_id, $pt_display ) ) ? true : false;

							echo "<option value='$post_id'";

							if( true === $select ) {

								echo ' selected="selected"';

								/* Check for conflicting relationships */
								if( is_array( $relationships ) && array_key_exists( $post_id, $relationships ) ) {

									if( is_array( $relationships[$post_id] ) && !empty( $relationships[$post_id] ) ) {

										$warning[$post_id] = $relationships[$post_id];

									}

								}

							}

							echo ">$post_title</option>";

						}
						?>

						</select>
					</div>
					<label for="wpbo_display_<?php echo $pt; ?>" class="wpbo-post-type-all">
						<input type="checkbox" name="wpbo_display_<?php echo $pt; ?>_all" id="wpbo_display_<?php echo $pt; ?>" value="all" <?php if( 'all' == $pt_display ): ?>checked="checked"<?php endif; ?>> 
						<?php printf( __( 'Display on all %s', 'betteroptin' ), $name ); ?>
					</label>

					<?php
					/* Show the possible warnings */
					if( false !== $warning && is_array( $warning ) ) {

						foreach( $warning as $key => $conflicts ) {

							if( count( $conflicts ) > 1 || 1 === count( $conflicts ) && !in_array( $_GET['post'], $conflicts ) ) {

								$page  = get_post( $key );
								
								echo '<p>';
								printf( __( 'Warning: other popups are already set to display on the following %s:', 'betteroptin' ), $pt );
								echo '</p><ul>';

								foreach( $conflicts as $conflict ) {

									$popup = get_post( $conflict );
									echo '<li>';
									printf( __( 'The %s <a href="%s" target="_blank">%s</a> already has popup <a href="%s" target="_blank">%s</a> set.', 'betteroptin' ), $pt, get_permalink( $page->ID ), $page->post_title, get_permalink( $popup->ID ), $popup->post_title );
									echo '</li>';

								}

								echo '</ul>';

							}

						}

					}
					?>
				</td>
			</tr>

		<?php }

	}

	wp_nonce_field( 'add_display', 'wpbo_display', false, true );

	$checks = array();

	/**
	 * Prepare a new array used for front-end verifications.
	 */
	foreach( $relationships as $page => $pop ) {

		$pge = get_post( $page );
		$pp  = get_post( $pop );

		$checks[$page] = array(
			'page_id'    => $page,
			'page_name'  => $pge->post_title,
			'popup_name' => $pp->post_title,
			'popup_link' => add_query_arg( array( 'post' => $pp->ID, 'action' => 'edit' ), admin_url( 'post.php' ) )
		);

	}
	?>
	<input type="hidden" name="wpbo_post_id" id="wpbo-post-id" value="<?php echo isset( $_GET['post'] ) ? $_GET['post'] : 0; ?>">
	</tbody>
	</table>
</fieldset>