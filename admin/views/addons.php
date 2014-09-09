<div class="wrap about-wrap">

	<h1>Add-Ons Are Coming Soon!</h1>

	<div class="about-text">We're planning on developing several extensions for <strong>BetterOptin</strong><br> At this time we can't announce release dates or price.</div>
	<h3>Free &amp; Paid Extensions</h3>
	<p>On our market place you'll find both free and paid extensions. If you're a developer and you would like to create an extension for <strong>BetterOptin</strong>, <a target="_blank" href="https://docs.google.com/forms/d/1vByv77ssC8JpoehFGBjxdK_l0MBbTDFFw74v2kBXaxo/viewform?usp=send_form">please fill in this form</a>.</p>

	<div class="changelog">
		<div class="feature-section col two-col">
			<div class="col-1">
				<img src="<?php echo WPBO_URL; ?>admin/assets/images/addon-mc.png" alt="" class="ta-shadow">
				<h4>MailChimp</h4>
				<p>Collect email opt-ins directly in <strong>MailChimp lists</strong>.<br> <a href="http://betteropt.in/downloads/mailchimp/" target="_blank">Get this extension for $20 &rarr;</a></p>
			</div>
			<div class="col-2 last-feature">
				<img src="<?php echo WPBO_URL; ?>admin/assets/images/addon-aw.png" alt="" class="ta-shadow">
				<h4>Aweber</h4>
				<p>An easy way to collect email opt-ins with <strong>Aweber</strong>.<br> <a href="http://betteropt.in/downloads/aweber/" target="_blank">Get this extension for $20 &rarr;</a></p>
			</div>
		</div>
	</div>

	<hr>

	<div class="changelog ta-marketplace">

		<div class="feature-section col three-col">
			<div>
				<div class="ta-addon ta-addon-free">
					<img src="<?php echo WPBO_URL; ?>admin/assets/images/addon-mailcheck.png" alt="Invalid Email Suggestion">
					<div class="ta-addon-ribbon">Premium</div>
					<div class="ta-addon-inner">
						<h4>Invalid Email Suggestion</h4>
						<p>We use Mailcheck to help reduce typos in email addresses during sign ups. It has been proven that it can reduce opt-in confirmation email bounces by 50%.</p>
						<p><a href="http://eepurl.com/2Kbon" class="button-primary" title="Get notified">COMING SOON</a></p>
					</div>
				</div>
			</div>
			<div>
				<div class="ta-addon ta-addon-premium">
					<a class="ta-addon-img" href="http://betteropt.in/downloads/mailpoet/" title="MailPoet extension" target="_blank"><img src="<?php echo WPBO_URL; ?>admin/assets/images/addons-mp.png" alt="" width="312" height="200"></a>
					<div class="ta-addon-ribbon">Premium</div>
					<div class="ta-addon-inner">
						<h4>MailPoet</h4>
						<p>Using MailPoet for sending out newsletter? You can now collect email opt-ins using BetterOptin!</p>
						<p><a href="http://betteropt.in/downloads/mailpoet/" class="button-primary">Get this extension</a></p>
					</div>
				</div>
			</div>
			<div class="last-feature">
				<div class="ta-addon ta-addon-premium">
					<img src="<?php echo WPBO_URL; ?>admin/assets/images/addons-wc.png" alt="WooCommerce integration" width="312" height="200">
					<div class="ta-addon-ribbon">Free</div>
					<div class="ta-addon-inner">
						<h4>WooCommerce integration</h4>
						<p>This addon enables you to create targeted campaigns for WooCommerce powered e-commerce shops. For instance, alert the user that is about to leave that his cart isn't empty.</p>
						<p><a href="http://eepurl.com/2Kbon" class="button-primary" title="Get notified">COMING SOON</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<hr>

</div>

<div style="display: none;">
	<div id="mailchimp-form">
		<p>Get notified when the extension is released. Subscribe below:</p>
		<!-- Begin MailChimp Signup Form -->
		<div id="mc_embed_signup">
			<form action="http://themeavenue.us4.list-manage1.com/subscribe/post?u=46ccfe899f0d2648a8b74454a&amp;id=6e08441cb7" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				<h2>Subscribe to stay tuned!</h2>
				<div class="mc-field-group">
					<label for="mce-EMAIL">Email Address</label>
					<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
				</div>
				<div id="mce-responses" class="clear">
					<div class="response" id="mce-error-response" style="display:none"></div>
					<div class="response" id="mce-success-response" style="display:none"></div>
				</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
				<div style="position: absolute; left: -5000px;"><input type="text" name="b_46ccfe899f0d2648a8b74454a_6e08441cb7" tabindex="-1" value=""></div>
				<div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
			</form>
		</div>
		<!--End mc_embed_signup-->
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function ($) {

	//////////////////////////////////////////
	// http://codex.wordpress.org/ThickBox //
	//////////////////////////////////////////
	
	// Thickbox iframe (easy to implement)
	if ($('.tav-trigger-modal').length > 0) {
		$('.tav-trigger-modal').on('click', function () {
			var url = $(this).attr('href'),
				title = $(this).attr('title');
			tb_show(title, url + '?TB_iframe=true');
			return false;
		});
	}

	// Thickbox inline (faster, more flexible)
	// @TODO: Fix Thickbox dimensions on WP3.9+
	if ($('.tav-trigger-modalinline').length > 0) {
		$('.tav-trigger-modalinline').on('click', function () {
			var title = $(this).attr('title'),
				target = $(this).attr('href').split('#')[1],
				width = $(window).width(),
				H = $(window).height(),
				W = (720 < width) ? 720 : width;
			W = W - 80;
			H = H - 84;
			tb_show(title, '#TB_inline?width=' + W + '&height=400&inlineId=' + target);
			return false;
		});
	}
});
</script>