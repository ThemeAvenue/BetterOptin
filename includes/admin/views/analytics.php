<?php
/**
 * Global vars
 */
$total_impressions = 0;
$total_conversions = 0;

/**
 * List all popups
 */
$args = array(
	'post_type'              => 'wpbo-popup',
	'post_status'            => 'any',
	'order'                  => 'DESC',
	'orderby'                => 'post_title',
	'posts_per_page'         => -1,
	'no_found_rows'          => false,
	'cache_results'          => false,
	'update_post_term_cache' => false,
	'update_post_meta_cache' => false,
	
);

$popups = new WP_Query( $args );

/**
 * Define default data
 */
$show = isset( $_GET['popup'] ) ? $_GET['popup'] : 'all';

/**
 * Define dates
 */
$period = isset( $_GET['period'] ) ? $_GET['period'] : 'today';

?>
<div class="wrap">
	
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php _e( 'Analytics: Impression and Conversion Stats', 'betteroptin' ); ?></h2>

	<div class="wpbo-stats-controls">
		<form method="get" action="<?php echo admin_url( 'edit.php' ); ?>">
			<select id="wpbo-stats-popup-select" name="popup">
				<option value="all"><?php _e( 'All popups', 'betteroptin' ); ?></option>
				<?php
				if( isset( $popups->posts ) && count( $popups->posts ) > 0 ) {

					foreach( $popups->posts as $popup ) { ?>

						<option value="<?php echo $popup->ID; ?>" <?php if( isset( $_GET['popup'] ) && $popup->ID == $_GET['popup'] ): ?>selected='selected'<?php endif; ?>><?php echo $popup->post_title; ?></option>

					<?php }

				}
				?>
			</select>
			<label for="wpbo-stats-popup-select"><?php _e( 'View popup', 'betteroptin' ); ?></label>

			<select id="wpbo-stats-date-select" name="period">
				<option value="today" <?php if( isset( $_GET['period'] ) && 'today' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Today', 'betteroptin' ); ?></option>
				<option value="this_week" <?php if( isset( $_GET['period'] ) && 'this_week' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Week', 'betteroptin' ); ?></option>
				<option value="last_week" <?php if( isset( $_GET['period'] ) && 'last_week' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Week', 'betteroptin' ); ?></option>
				<option value="this_month" <?php if( isset( $_GET['period'] ) && 'this_month' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Month', 'betteroptin' ); ?></option>
				<option value="last_month" <?php if( isset( $_GET['period'] ) && 'last_month' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Month', 'betteroptin' ); ?></option>
				<option value="this_quarter" <?php if( isset( $_GET['period'] ) && 'this_quarter' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Quarter', 'betteroptin' ); ?></option>
				<option value="last_quarter" <?php if( isset( $_GET['period'] ) && 'last_quarter' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Quarter', 'betteroptin' ); ?></option>
				<option value="this_year" <?php if( isset( $_GET['period'] ) && 'this_year' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Year', 'betteroptin' ); ?></option>
				<option value="last_year" <?php if( isset( $_GET['period'] ) && 'last_year' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Year', 'betteroptin' ); ?></option>
			</select>
			<label for="wpbo-stats-date-select"><?php _e( 'Filter by Date', 'betteroptin' ); ?></label>

			<!-- <a id="wpbo-stats-reset" href="#" class="button-secondary tav-fr" title="Are you sure to reset statistics for the current view? This statistics will be deleted immediately. You can't undo this action."><?php _e( 'Reset', 'betteroptin' ); ?></a> -->

			<input type="hidden" name="post_type" value="wpbo-popup">
			<input type="hidden" name="page" value="wpbo-analytics">
		</form>
	</div>
	
	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				
				<div class="meta-box-sortables ui-sortable">
					
					<?php require_once( WPBO_PATH . 'includes/admin/views/analytics-stats.php' ); ?>
					
				</div> <!-- .meta-box-sortables .ui-sortable -->
				
				<div class="meta-box-sortables ui-sortable">
					
					<div class="postbox">

						<h3><span><?php _e( 'Graph Statistics', 'betteroptin' ); ?></span></h3>
						<div class="inside">
							<div id="wpbo-stats-graph" class="wpbo-loading" style="height: 400px; margin: 20px 0;"></div>
						</div> <!-- .inside -->

					</div> <!-- .postbox -->
					
				</div> <!-- .meta-box-sortables .ui-sortable -->
				
			</div> <!-- post-body-content -->
			
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				
				<div class="meta-box-sortables">
					
					<div class="postbox">

						<h3><span><?php printf( __( '%s\'s Stats Breakdown', 'betteroptin' ), ucwords( str_replace( '_', ' ', $period ) ) ); ?></span></h3>
						<?php
						$total_rate = ( 0 === $total_conversions || 0 === $total_impressions ) ? 0 : ( 100 * $total_conversions ) / $total_impressions;
						$total_rate = number_format( $total_rate, 2 );
						?>
						<div class="inside">
							<ul>
								<li><?php printf( __( 'Total impressions for %s: %s', 'betteroptin' ), str_replace( '_', ' ' , $period ), '<code>' . number_format( $total_impressions, 0 ) . '</code>' ); ?></li>
								<li><?php printf( __( 'Total conversions for %s: %s', 'betteroptin' ), str_replace( '_', ' ' , $period ), '<code>' . number_format( $total_conversions, 0 ) . '</code>' ); ?></li>
								<li><?php printf( __( 'Percentage of conversions for %s: %s', 'betteroptin' ), str_replace( '_', ' ' , $period ), "<code>$total_rate%</code>" ); ?></li>
							</ul>
							<p id="wpbo-stats-today" data-dimension="254" data-text="<?php echo $total_rate; ?>%" data-info="<?php printf( __( '%s\'s conversion', 'betteroptin' ), ucwords( str_replace( '_', ' ' , $period ) ) ); ?>" data-width="30" data-fontsize="38" data-percent="<?php echo $total_rate; ?>" data-fgcolor="#61a9dc" data-bgcolor="#f1f1f1"></p>
						</div> <!-- .inside -->
						
					</div> <!-- .postbox -->
					
					<?php
					/*
					<div class="postbox">

						<h3><span>Export Data</span></h3>
						<div class="inside">
							<!--
							Download JSON to CSV using PHP:
							https://gist.github.com/Kostanos/5641110
							http://stackoverflow.com/a/4811885
							http://stackoverflow.com/a/9573700
							-->
							<p>To export Today's Conversions to CSV, simply click on the Download button below:</p>
							<!-- <p>There is no conversion data to download at this time.</p> -->
							<a href="#" class="button-secondary">Download</a>
						</div> <!-- .inside -->
						
					</div> <!-- .postbox -->
					*/
					?>
					
				</div> <!-- .meta-box-sortables -->
				
			</div> <!-- #postbox-container-1 .postbox-container -->
			
		</div> <!-- #post-body .metabox-holder .columns-2 -->
		
		<br class="clear">
	</div> <!-- #poststuff -->
	
</div> <!-- .wrap -->