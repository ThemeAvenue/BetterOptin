/*global jQuery:false */
var DEBUG = false;

(function ($) {
	'use strict';

	// Reading WordPress Options
	var wpboStr, wpboObj;
	if (typeof wpbo !== 'undefined') {
		wpboStr = wpbo.replace(/&quot;/g, '"');
		wpboObj = $.parseJSON(wpboStr);
		if (DEBUG) {
			console.log(wpboObj);
		}
	} else {
		wpboObj = {
			close_overlay: true,
			close_esc: true,
			cookie_lifetime: '30',
			animation: 'bounceIn',
			popup_id: 'betterOpt',
			overlayOpacity: 0.5,
			overlayColor: '#000',
			credit: true
		};
	}

	/////////////////////
	// Document Ready //
	/////////////////////
	$(function () {

		// Define vars
		var wpboCookie = 'wpbo_' + wpboObj.popup_id,
			wpboModal = $('.wpbo-modal').hide(),
			wpboForm = wpboModal.parent('.optform');

		// Preload images
		function preload(arrayOfImages) {
			$(arrayOfImages).each(function () {
				$('<img />').attr('src', this).appendTo('body').hide();
			});
		}
		if ($('.wpbo-featured-img').length) {
			var wpboImages = $('.wpbo-featured-img').attr('src');
			if (DEBUG) {
				console.log(wpboImages);
			}
			preload([wpboImages]);
		}

		wpboModal.easyModal({
			top: 200,
			overlayOpacity: wpboObj.overlay_opacity,
			overlayColor: wpboObj.overlay_color,
			overlayClose: wpboObj.close_overlay,
			transitionIn: 'animated ' + wpboObj.animation,
			updateZIndexOnOpen: false,
			closeOnEscape: wpboObj.close_esc,
			closeButtonClass: '.wpbo-close',
			onClose: function () {

				// Set Cookie
				// @NOTE: Does not work locally, see http://stackoverflow.com/questions/335244/why-does-chrome-ignore-local-jquery-cookies
				var d = new Date();
				d = d.toDateString();
				d = d.split(' ').join('_');
				$.cookie(wpboCookie, 'closed_on_' + d, {
					expires: wpboObj.cookie_lifetime,
					path: '/'
				});
			},
			onOpen: function () {

				// Save impressions
				var data = {
					'action': 'wpbo_new_impression',
					'popup_id': wpboObj.popup_id
				};
				$.post(wpboObj.ajaxurl, data);

				// Trigger matchHeight
				if (jQuery().matchHeight) {
					$('.wpbo-col').matchHeight('.wpbo-grid-no-gutter');
				}

				// Focus on the first form input
				wpboForm.find('input:first').focus();

				// Disable the submit button onSubmit
				wpboForm.submit(function () {
					wpboForm.find('[type=submit]').prop('disabled', true).text('Please wait...');
				});

				// Add Credit
				if (wpboObj.credit === true) {
					wpboModal.append('<a class="wpbo-credit" href="http://betteropt.in/?utm_source=plugin&utm_medium=credit&utm_term=organic&utm_campaign=betteroptin" target="_blank">Popup created with <strong>BetterOptin</strong></a>');
				}
			}
		});

		// If cookie is not set, show the modal
		if ($.cookie(wpboCookie) == null) {

			$(document).one('mouseleave', function (e) {
				e.preventDefault();

				// Open the modal
				wpboModal.trigger('openModal').addClass('wpbo-modal-active');

				// Click outside animates the modal
				if (wpboObj.wiggle === true) {
					$(document).on('click', '.lean-overlay:not(".wpbo-modal")', function () {
						wpboModal.addClass('wpbo-tada');
						wpboModal.on('oanimationend animationend webkitAnimationEnd', function () {
							$(this).removeClass('wpbo-tada');
						});
					});
				}
			});
		}

		// Trigger the modal manually
		$('.wpbo-trigger').on('click', function (e) {
			e.preventDefault();
			wpboModal.trigger('openModal').addClass('wpbo-modal-active');
		});

		// Hide close icon if option is enabled
		if (wpboObj.hide_close_button) {
			$('.wpbo-close').hide();
		}

	});

}(jQuery));