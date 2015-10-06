(function ($) {
	'use strict';

	$(function () {

		// Define variables
		var docWrapper = $('.ta-doc-content'),
			toc = $('#ta-toc'),
			adminBar = $('#wpadminbar');

		$.post(ajaxurl, {
			action: 'wpbo_get_doc'
		}).done(function (response) {

			// Append the doc content
			docWrapper.html(response);

			// Remove the embed TOC (Table of Contents Plus)
			docWrapper.find('#toc_container').remove();

			// Create the table of content (TOC)
			toc.html('').toc({
				content: '.ta-doc-content',
				headings: 'h2,h3,h4'
			});

			// Add smooth scroll
			$('.wrap a').smoothScroll({
				offset: -(adminBar.height() + 20) // Margin-top of headings $('.ta-doc-content h*');
			});

			// Open External Links In New Window
			$('a', docWrapper).filter(function () {
				return this.hostname && this.hostname !== location.hostname;
			}).attr('target', '_blank');

		});

	});

}(jQuery));