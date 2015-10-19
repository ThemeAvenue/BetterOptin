<?php
global $wpbo_submit;

$lists   = wpbo_mp_get_mailpoet_lists();
$default = wpbo_get_option( 'mp_list_id' );
$opts    = array();
$value   = isset( $_GET['post'] ) ? get_post_meta( intval( $_GET['post'] ), 'wpbo_mp_list', true ) : '';

foreach ( $lists as $key => $list ) {
	$opts[ $list['list_id'] ] = $list['name'];
}

$def_name = $opts[ $default ];

if ( '' == $value ): ?>
	<p><?php printf( __( 'If you don\'t select a list here, the default one will be used (currently %s).', 'betteroptin' ), "<code>$def_name</code>" ); ?></p>
<?php endif; ?>

<select id="wpbo_mp_list" name="wpbo_mp_list" style="width:100%">
	<option value=""
	        <?php if ( '' == $value ): ?>selected="selected"<?php endif; ?>><?php _e( 'Default', 'betteroptin' ); ?></option>
	<?php
	foreach ( $opts as $id => $name ) { ?>
		<option value="<?php echo $id; ?>"
		        <?php if ( $id == $value ): ?>selected="selected"<?php endif; ?>><?php echo $name; ?></option>
	<?php } ?>
</select>