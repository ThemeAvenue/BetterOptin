<?php
/**
 * Generate random date.
 *
 * @see  http://stackoverflow.com/a/14186825
 * @param  [type] $min_date [description]
 * @param  [type] $max_date [description]
 * @return [type]           [description]
 */
function wpbo_rand_date( $min_date, $max_date ) {
    /* Gets 2 dates as string, earlier and later date.
       Returns date in between them.
    */

    $rand_epoch = rand( $min_date, $max_date );

    return date( 'Y-m-d H:i:s', $rand_epoch );
}

function wpbo_input_dummy( $lines = 5 ) {

	if( isset( $_GET['wpbo_dummy'] ) && is_numeric( $_GET['wpbo_dummy'] ) )
		$lines = $_GET['wpbo_dummy'];

	/* Max conversion rate */
	$ratio = 10;

	/* Max conversion added based on max ratio */
	$max = ( 20 * $lines ) / 100;

	$args = array(
		'post_type'      => 'wpbo-popup',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		
	);

	$popups = new WP_Query( $args );

	if( count( $popups->posts ) >= 1 ) {

		foreach( $popups->posts as $popup ) {

			/* Count ratio */
			$limit = 0;

			$types = array(
				'impression',
				'conversion'
			);
			
			for( $count = 0; $count <= $lines; $count++ ) {

				if( $limit >= $max )
					$types[1] = 'impression';

				$dtype = rand( 0, 1 );
				$dtype = $types[$dtype];
				$time  = wpbo_rand_date( strtotime( 'first day of January last year', time() ), strtotime( 'last day of December this year', time() ) );

				if( $dtype === 'conversion' )
					++$limit;

				wpbo_db_insert_data( array( 'time' => $time, 'data_type' => $dtype, 'popup_id' => $popup->ID, ), false );

			}

		}

	}

}

function wpbo_add_dummy() {
	add_action( 'init', 'wpbo_input_dummy' );
}

if ( isset( $_GET['wpbo_dummy'] ) ) {
	wpbo_add_dummy();
}