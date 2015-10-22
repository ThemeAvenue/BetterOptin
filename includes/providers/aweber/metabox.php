<?php
global $wpbo_submit;

$lists   = wpbo_aw_get_lists();
$default = wpbo_get_option( 'aw_list_id' );
$opts    = array();
$value   = isset( $_GET['post'] ) ? get_post_meta( (int) $_GET['post'], 'wpbo_aw_list', true ) : '';

foreach ( $lists as $key => $list ) {
	$opts[ $list['id'] ] = $list['name'];
}

$def_name = isset( $opts[ $default ] ) ? $opts[ $default ] : esc_html_x( 'None', 'No mailing list selected', 'betteroptin' );

if ( '' == $value ): ?>
	<p><?php printf( esc_html__( 'If you don\'t select a list here, the default one will be used (currently %s).', 'betteroptin' ), "<code>$def_name</code>" ); ?></p>
<?php endif; ?>

<select id="wpbo_aw_list" name="wpbo_aw_list" style="width:100%">
	<option value="" <?php if( '' == $value ): ?>selected="selected"<?php endif; ?>><?php esc_html_x( 'Default', 'Default mailing list', 'betteroptin' ); ?></option>
	<?php
	foreach ( $opts as $id => $name ) { ?>
		<option value="<?php echo $id; ?>" <?php if( $id == $value ): ?>selected="selected"<?php endif; ?>><?php echo $name; ?></option>
	<?php } ?>
</select>