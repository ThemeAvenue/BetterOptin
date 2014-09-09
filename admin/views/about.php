<div class="wrap about-wrap">

	<h1>Welcome to BetterOptin</h1>

	<div class="about-text">This plugin was created by the folks at <a href="http://themeavenue.net/" target="_blank">ThemeAvenue</a>.</div>

	<div class="changelog">
		<div class="feature-section col two-col">
			<div>
				<p><strong>BetterOptin</strong> is a lead generation and conversion rate optimization plugin for WordPress.</p>
				<h3>Convert abandoning visitors into customers</h3>
				<p>Chances are, most first-time visitors who abandon your site won't be coming back. <a href="//betteropt.in/" target="_blank">BetterOptin</a> gives you the opportunity to <strong>turn your exiting visitors into customers</strong>.</p>
				<p>The plugins allows you to show a popup right when the visitor is about to leave, giving you a second chance to catch his attention.</p>
			</div>
			<div class="last-feature about-colors-img">
				<div class="ta-video-container"><iframe width="499" height="281" src="//www.youtube.com/embed/iQvJHSVhNUc" frameborder="0" allowfullscreen></iframe></div>
				<small class="ta-video-helper">Watching in Full Screen is advised</small>
			</div>
		</div>
	</div>

	<hr>

	<div class="changelog">
		<div class="feature-section col two-col">
			<div class="col-1">
				<img src="<?php echo WPBO_URL; ?>admin/assets/images/about-exit-intent.png" alt="">
				<h4>Why is the "Exit-Intent" behavior innovative?</h4>
				<p>Did you ever feel frustrated when a website threw an opt-in popup right at you? Of course you did! Do you want your visitors to feel the same way and leave your site? Probably not.</p>
				<p>BetterOptin solves this frustrating behavior by prompting visitors when they are about to leave.</p>
			</div>
			<div class="col-2 last-feature">
				<img src="<?php echo WPBO_URL; ?>admin/assets/images/about-visual-editor.png" alt="">
				<h4>Flexible &amp; Powerful Visual Editor</h4>
				<p>Our visual editor is super easy to use. Simply hover an element to see if it is editable, then click it to edit. A sidebar will slide-in, allowing you to change colors, fonts, images, backgrounds and content.</p>
				<p>Be creative! You know your audience, so design stunning opt-in popups that match your visitors expectations.</p>
			</div>
		</div>
	</div>

	<hr>

	<div class="changelog">
		<div class="feature-section col two-col">
			<div class="col-1">
				<img src="<?php echo WPBO_URL; ?>admin/assets/images/about-targeted-campaigns.png">
				<h4>Targeted Campaigns</h4>
				<p>As a marketer, you know that the more precise the offer is, the better it converts. BetterOptin allows you to do exactly this: associate a popup to individual pages, posts or any custom post type (for instance if you have a custom post type "product").</p>
				<p>Capture your leads into targeted MailChimp lists or into WordPress.</p>
			</div>
			<div class="col-2 last-feature">
				<img src="<?php echo WPBO_URL; ?>admin/assets/images/about-analytics.png">
				<h4>Valuable Insights</h4>
				<p>Our built-in analytics allows to quicky visualize conversion rates. You can also see which popup is converting best so you can refine your strategy.</p>
				<p>We will also be showing you referral pages soon so that you can figure out which page works best.</p>
			</div>
		</div>
	</div>

	<hr>

	<div class="changelog">
		<div class="feature-section col three-col about-updates" style="text-align: center;">
			<div class="col-1">
				<span class="dashicons dashicons-awards" style="font-size: 48px;"></span>
				<h3>Blazing Fast</h3>
				<p>As it has be proven that performance affects conversion, we focused on delivering a lightweight product. The front-end dependencies are <strong>only 17kb</strong>, and the server processing uses a pseudo-caching system to reduce calculations.</p>
				<p>So don't be worried, our plugin won't affect your website load time at all.</p>
			</div>
			<div class="col-2">
				<span class="dashicons dashicons-editor-code" style="font-size: 48px;"></span>
				<h3>No Coding Skills Required</h3>
				<p>BetterOptin is carefully built with simplicity in mind. Almost every single element of the popups can be customised with our visual editor.</p>
				<p>You don't have to edit a single line of code. But if you're a developer and want to extend the plugin's features we've got you covered.</p>
			</div>
			<div class="col-3 last-feature">
				<span class="dashicons dashicons-groups" style="font-size: 48px;"></span>
				<h3>Capturing leads is easy</h3>
				<p>At the moment, BetterOptin is capable of capturing your leads directly in WordPress, where you can give them a specific role.</p>
				<p>With our premium add-ons, you can also use <a href="//betteropt.in/downloads/mailchimp/" target="_blank">MailChimp</a>, <a href="//betteropt.in/downloads/aweber/" target="_blank">AWeber</a> or <a href="//betteropt.in/downloads/mailpoet/" target="_blank">MailPoet</a>.</p>
			</div>
		</div>
	</div>

	<hr>

	<div class="changelog">
		<div class="feature-section col one-col center-col">
			<img src="<?php echo WPBO_URL; ?>admin/assets/images/logo-transparent-black.png" alt="ThemeAvenue">
			<h3>Want to hear more about ThemeAvenue?</h3>
			<p><a href="http://themeavenue.net/" target="_blank">ThemeAvenue</a> is a premium WordPress themes and plugins development company. Feel free to check our <a href="http://codecanyon.net/user/themeavenue"></a>portfolio on CodeCanyon and visit our <a href="http://support.themeavenue.net/">support site</a> if you need assistance with our products.</p>
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=647293568670589&version=v2.0";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
			<div class="fb-like-box" data-href="https://www.facebook.com/ThemeAvenue" data-width="1050" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false"></div>
		</div>

		<h4>Latest article from us</h4>

		<?php
		include_once( ABSPATH . WPINC . '/feed.php' );

		$rss = fetch_feed( 'http://themeavenue.net/feed/' );

		if( !is_wp_error( $rss ) ):

			$maxitems  = $rss->get_item_quantity( 5 ); 
			$rss_items = $rss->get_items( 0, $maxitems ); ?>

			<ul>
				<?php if ( $maxitems == 0 ) : ?>
					<li><?php _e( 'No items', 'wpbo' ); ?></li>
				<?php else :

					foreach ( $rss_items as $item ) : ?>
						<li>
							<a href="<?php echo esc_url( $item->get_permalink() ); ?>" title="<?php printf( __( 'Posted %s', 'wpbo' ), $item->get_date('j F Y | g:i a') ); ?>" target="_blank">
								<?php echo esc_html( $item->get_title() ); ?>
							</a>
						</li>
					<?php endforeach;

				endif; ?>

			</ul>

		<?php endif; ?>
	
	</div>

	<hr>

</div>