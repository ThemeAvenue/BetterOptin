<?php
$action = admin_url( 'edit.php' );
$args   = array(
	'post_type'              => 'wpbo-popup',
	'post_status'            => 'publish',
	'order'                  => 'ASC',
	'orderby'                => 'post_title',
	'posts_per_page'         => -1,
	'no_found_rows'          => false,
	'cache_results'          => false,
	'update_post_term_cache' => false,
	'update_post_meta_cache' => false,
	
);
$popups = new WP_Query( $args );
?>

<h2><?php _e( 'Which popup would you like to customize?', 'wpbo' ); ?></h2>
<form method="get" action="<?php echo $action; ?>">

	<select id="wpbo_edit_popup" name="wpbo_popup">

		<?php
		foreach( $popups->posts as $popup ) {

			echo "<option value='$popup->ID'>$popup->post_title</option>";

		}
		?>

	</select>

	<input type="hidden" name="post_type" value="wpbo-popup">
	<input type="hidden" name="page" value="wpbo-customizer">
	<input type="submit" class="button-primary" value="Edit">

</form>