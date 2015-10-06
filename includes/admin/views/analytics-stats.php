<?php
/**
 * Define vars
 */
$datas = array();
$seen  = array();
$query = array( 'data_type' => 'any', 'limit' => -1 );

/* Select a specific popup */
if( 'all' != $show ) {
	$query['popup_id'] = intval( $show );
}

switch( $period ) {

	case 'today':
		$timeframe = strtotime( 'today' );
	break;

	case 'this_week':

		$timeframe = array(
			'from' => strtotime( 'last monday', time() ),
			'to'   => strtotime( 'next sunday', time() )
		);

	break;

	case 'last_week':

		$timeframe = array(
			'from' => strtotime( 'last monday -1 week' ),
			'to'   => strtotime( 'next sunday -1 week' )
		);

	break;

	case 'this_month':

		$timeframe = array(
			'from' => strtotime( 'first day of this month', time() ),
			'to'   => strtotime( 'last day of this month', time() )
		);

	break;

	case 'last_month':

		$timeframe = array(
			'from' => strtotime( 'first day of last month', time() ),
			'to'   => strtotime( 'last day of last month', time() )
		);

	break;

	case 'this_quarter':

		$quarters = array( 1, 4, 7, 10 );
		$month    = intval( date( 'm' ) );

		if( in_array( $month, $quarters ) ) {
			$current = date( 'Y-m-d', time() );
		} else {

			/* Get first month of this quarter */
			while( !in_array( $month, $quarters) ) {
				$month = $month-1;
			}

			$current = date( 'Y' ) . '-' . $month . '-' . '01';

		}

		$current = strtotime( $current );

		$timeframe = array(
			'from' => strtotime( 'first day of this month', $current ),
			'to'   => strtotime( 'last day of this month', strtotime( '+2 months', $current ) )
		);

	break;

	case 'last_quarter':

		$quarters = array( 1, 4, 7, 10 );
		$month    = intval( date( 'm' ) ) - 3;
		$rewind   = false;

		if( in_array( $month, $quarters ) ) {
			$current = date( 'Y-m-d', time() );
		} else {

			/* Get first month of this quarter */
			while( !in_array( $month, $quarters) ) {

				$month = $month-1;

				/* Rewind to last year after we passed January */
				if( 0 === $month )
					$month = 12;
			}

			$current = date( 'Y' ) . '-' . $month . '-' . '01';

		}

		/* Set the theorical current date */
		$current = false === $rewind ? strtotime( $current ) : strtotime( '-1 year', $current );

		$timeframe = array(
			'from' => strtotime( 'first day of this month', $current ),
			'to'   => strtotime( 'last day of this month', strtotime( '+2 months', $current ) )
		);

	break;

	case 'this_year':

		$timeframe = array(
			'from' => strtotime( 'first day of January', time() ),
			'to'   => strtotime( 'last day of December', time() )
		);

	break;

	case 'last_year':

		$timeframe = array(
			'from' => strtotime( 'first day of January last year', time() ),
			'to'   => strtotime( 'last day of December last year', time() )
		);

	break;

}

/* Set the period */
$query['period'] = $timeframe;

// print_r( $query );

/* Get the datas */
$datas = wpbo_get_datas( $query, 'OBJECT' );
?>
<div class="postbox">

	<!-- <h3><span>Conversion Statistics per Popup</span></h3> -->
	<div class="inside-no-padding">

		<table class="form-table" id="wpbo-stats-general" data-timeframe="<?php echo htmlspecialchars( serialize( $timeframe ) ); ?>" data-popup="<?php echo $show; ?>" data-period="<?php echo $period; ?>">
			<thead>
				<tr>
					<th class="row-title"><?php _e( 'Popup Name', 'wpbo' ); ?></th>
					<th><?php _e( 'Impressions', 'wpbo' ); ?></th>
					<th><?php _e( 'Conversions', 'wpbo' ); ?></th>
					<th><?php _e( '% Conversion', 'wpbo' ); ?></th>
					<th><?php _e( 'Status', 'wpbo' ); ?></th>
					<th data-bSortable="false"><?php _e( 'Settings', 'wpbo' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if( is_array( $datas ) ) {

					foreach( $datas as $key => $data ) {

						if( in_array( $data->popup_id, $seen ) )
							continue;

						/* Mark this popup as parsed */
						array_push( $seen, $data->popup_id );

						$popup        = get_post( $data->popup_id );
						$impressions  = wpbo_get_datas( array( 'popup_id' => $data->popup_id, 'data_type' => 'impression', 'limit' => -1, 'period' => $timeframe ), 'ARRAY_A' );
						$conversions  = wpbo_get_datas( array( 'popup_id' => $data->popup_id, 'data_type' => 'conversion', 'limit' => -1, 'period' => $timeframe ), 'ARRAY_A' );
						$rate         = ( 0 === count( $conversions ) || 0 === count( $impressions ) ) ? 0 : ( 100 * count( $conversions ) ) / count( $impressions );
						$status       = 'publish' == $popup->post_status ? __( 'Active', 'wpbo' ) : __( 'Inactive', 'wpbo' );
						$status_class = 'publish' == $popup->post_status ? 'wpbo-stats-active' : 'wpbo-stats-inactive';

						/* Increment the global vars */
						$total_impressions = $total_impressions + count( $impressions );
						$total_conversions = $total_conversions + count( $conversions );
						?>

						<tr valign="top">
							<td scope="row"><a href="<?php echo add_query_arg( array( 'post' => $data->popup_id, 'action' => 'edit' ), admin_url( 'post.php' ) ); ?>"><?php echo $popup->post_title; ?></a> <em>(#<?php echo $data->popup_id; ?>)</em></td>
							<td><?php echo number_format( count( $impressions ), 0 ); ?></td>
							<td><?php echo number_format( count( $conversions ), 0 ); ?></td>
							<td><?php echo number_format( $rate, 2 ); ?>% <!-- <span class="wpbo-stats-variation">&#9660;</span> --></td>
							<td><span class="<?php echo $status_class; ?>"><?php echo $status; ?></span></td>
							<td><a href="<?php echo add_query_arg( array( 'post' => $data->popup_id, 'action' => 'edit' ), admin_url( 'post.php' ) ); ?>" class="button-secondary"><?php _e( 'Settings', 'wpbo' ); ?></a></td>
						</tr>

					<?php }

				}
				?>
			</tbody>
		</table>
	</div> <!-- .inside -->
</div> <!-- .postbox -->