<?php
global $post, $wpdb;

$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : '';

$checks = array(
	'template' => array(
		'label'   => __( 'Choose a template', 'wpbo' ),
		'check'   => isset( $_GET['post'] ) ? get_post_meta( $post_id, 'wpbo_template', true ) : '',
		'against' => '',
		'compare' => 'NOT EQUAL'
	),
	'settings' => array(
		'label'   => __( 'Define settings', 'wpbo' ),
		'check'   => isset( $_GET['post'] ) ? get_post_meta( $post_id, '_wpbo_settings', true ) : '',
		'against' => '',
		'compare' => 'NOT EQUAL'
	),
	'pages' => array(
		'label'   => __( 'Choose Pages', 'wpbo' ),
		'check'   => $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key LIKE '_wpbo_display_%' AND meta_value <> 'no'" ),
		'against' => array(),
		'compare' => 'NOT EQUAL'
	),
	'customized' => array(
		'label'   => __( 'Customize', 'wpbo' ),
		'check'   => isset( $_GET['post'] ) ? get_post_meta( $post_id, '_wpbo_template_display', true ) : '',
		'against' => '',
		'compare' => 'NOT EQUAL'
	),
	'published' => array(
		'label'   => __( 'Launch', 'wpbo' ),
		'check'   => isset( $post ) ? $post->post_status : '',
		'against' => 'publish',
		'compare' => 'EQUAL'
	)
);

/* Filter the checklist */
$checks = apply_filters( 'wpbo_checklist', $checks );
?>
<ol id="wpbo-summary-list">

	<?php
	foreach( $checks as $check ) {

		$completed = false;

		switch( $check['compare'] ):

			case 'EQUAL':

				if( $check['check'] == $check['against'] )
					$completed = true;

			break;

			case 'NOT EQUAL':

				if( $check['check'] != $check['against'] )
					$completed = true;

			break;

		endswitch;
		?>

		<li <?php if( true === $completed ): ?>class="wpbo-step-completed"<?php endif; ?>>
			<i></i> <span><?php echo $check['label']; ?></span>
		</li>

	<?php } ?>
</ol>