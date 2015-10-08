(function ($) {
	'use strict';

	$(function () {

		// Define variables
		var docWrapper = $('#ta-doc'),
			docToC = $('#ta-doc-toc'),
			docContent = $('#ta-doc-content'),
			adminBar = $('#wpadminbar');

		$.post(ajaxurl, {

			action: 'wpbo_get_doc'

		}).done(function (response) {

			// Append the HTML
			docContent.html(response);

			// Remove the embed TOC (Table of Contents Plus)
			$('#toc_container', docContent).remove();

			// Create new TOC
			docToC.toc({
				headings: 'h2,h3,h4'
			});

			// Add smooth scroll to TOC
			$('a', docWrapper).smoothScroll({
				offset: -(adminBar.height() + 20) // Margin-top of headings $('.ta-doc-content h*');
			});

			// Open External Links In New Window
			$('a', docContent).filter(function () {
				return this.hostname && this.hostname !== location.hostname;
			}).attr('target', '_blank');

		});

	});

}(jQuery));