(function ($) {
	'use strict';

	$(function () {

		// Define variables
		var docWrapper = $('.ta-doc-content'),
			docToTop = $('.ta-summary');
		var data = {
			'action': 'wpbo_get_doc'
		};

		$.post(ajaxurl, data).done(function (response) {

			// Append the doc content
			docWrapper.html(response);

			// Create Summary
			docWrapper.find('h2,h3').each(function (i) {
				var position = i + 1,
					heading = $(this).text(),
					headingText = $('<li><a href="#ta-doc-' + position + '">' + heading + '</a></li>');
				$(this).attr('id', 'ta-doc-' + position).addClass('ta-doc-heading').append('<a href="#top" class="ta-doc-totop" title="Back to the summary">&#9650; Back to top</span></a>');
				docToTop.show().find('ol').append(headingText);
			});

			// Wrap everything between headings
			$('.ta-doc-heading').each(function () {
				$(this).nextUntil('.ta-doc-heading').andSelf().wrapAll('<div class="ta-doc-section" />');
			});

			// Smooth Scrolling
			$('a[href*=#]:not([href=#])').on('click', function () {
				if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
					var target = $(this.hash);
					target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
					if (target.length) {
						$('html,body').animate({
							scrollTop: target.offset().top - 50
						}, 600, function () {
							// Animation complete, highlight active section
							$('.ta-doc-section-hl').removeClass('ta-doc-section-hl');
							target.closest('.ta-doc-section').addClass('ta-doc-section-hl');
							setTimeout(function () {
								$('.ta-doc-section-hl').removeClass('ta-doc-section-hl');
							}, 600);
						});

						return false;
					}
				}
			});

			// Open External Links In New Window
			$('a[href^="http://"]', docWrapper).attr('target', '_blank');

		});

		// Back to top
		$(document).on('click', '.ta-doc-totop', function (event) {
			event.preventDefault();
			$('html, body').animate({
				scrollTop: 0
			}, 400);
			return false;
		});
	});

}(jQuery));