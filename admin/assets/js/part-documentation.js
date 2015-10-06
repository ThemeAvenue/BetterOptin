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

			// Remove the embed TOC (Table of Contents Plus)
			docWrapper.find('#toc_container').remove();

			// Create the table of content (TOC)
			$('#ta-toc').html('').toc({
				content: '.ta-doc-content',
				headings: 'h2,h3,h4'
			});

			// Add smooth scroll
			$('.wrap a').smoothScroll();

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