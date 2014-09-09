(function ($) {
	"use strict";

	$(function () {

		var settingsForm = $('#post'),
			btnCustomize = $('[name="save_customize"]');

		if (jQuery().wpColorPicker) {
			$('.tav-colorpicker').wpColorPicker();
		}

		////////////////////////////
		// Update the Checklist //
		////////////////////////////
		$('.wpbo-step').on('change', 'input', function () {
			var stepNo = $(this).parents('fieldset').data('step');
			$('#wpbo-summary-list > li:nth-child(' + stepNo + ')').addClass('wpbo-step-completed');
			if (stepNo === 1) {
				btnCustomize.prop('disabled', false);
				$('#wpbo-customizer-not-ready').hide();
				$('#wpbo-customizer-ready').show();
			}
		});

		////////////////////////
		// Template preview //
		////////////////////////
		var tavRi = $('.ta-ri-wrap'),
			modalBackdrop = $('<div class="media-modal-backdrop"></div>');

		tavRi.find('input[type="radio"]').addClass('sr-only');
		tavRi.find('input[type="radio"]:checked').closest(tavRi).addClass('ta-ri-selected');

		tavRi.on('click', '.ta-ri-crtl > button', function () {
			tavRi.removeClass('ta-ri-selected');
			$(this).closest(tavRi).addClass('ta-ri-selected');
			$(this).closest(tavRi).find('input[type="radio"]').prop('checked', true).trigger('change');
			if ($(this).hasClass('button-primary')) {
				return false;
			} else {
				$(this).prop('disabled', false).text('Loading...');
				$('<div class="ta-loading-notice">Loading... Please wait</div>').appendTo('body');
				modalBackdrop.appendTo('body');
				return true;
			}
		});

		tavRi.on('click', 'img', function (e) {
			e.preventDefault();
			modalBackdrop.appendTo('body').addClass('ta-overlay-preview');
			$(this).clone().appendTo('body').removeClass().addClass('ta-view-full').show();
		});

		$(document).on('click', '.ta-overlay-preview, .ta-view-full', function (e) {
			e.preventDefault();
			$('.ta-view-full').remove();
			modalBackdrop.remove();
		});

		///////////////////////
		// Multiple Select //
		///////////////////////
		if (jQuery().chosen) {

			$('.chosen-select').each(function () {
				var el = {
					parentTD: $(this).parents('td'),
					errorMsg: $('<div>', {
						class: 'wpbo-warning',
						style: 'display:none'
					})
				};

				el.errorMsg.appendTo(el.parentTD);

				$(this).chosen().change(function () {

					$(this).next('.chosen-container').find('.chosen-choices').addClass('chosen-loading');

					var data = {
						'action': 'wpbo_check_page_availability',
						'post_id': parseInt($('option:selected:last', this).val(), 10),
						'current_id': $('#wpbo-post-id').val(),
						'selected_all': $('option:selected', this).map(function () {
							return this.value;
						}).get().join(',')
					};

					$.post(ajaxurl, data, function (response) {
						$('.chosen-choices').removeClass('chosen-loading');
						$('[data-step="3"] input').trigger('change');
						if (response !== '0') {
							el.errorMsg.html(response).show();
						}
					});

				});
			});
		}

		/////////////////////////
		// Toggle visibility //
		// Show/hide child checkbox
		/////////////////////////
		var checkboxAll = $('.wpbo-post-type-all'),
			multipleSelectWrap = $('.wpbo-multi-select');

		$('#wpbo_all').on('change', function () {
			if ($(this).is(':checked')) {
				$(':checkbox', checkboxAll).prop('checked', true);
				multipleSelectWrap.hide();
			} else {
				$(':checkbox', checkboxAll).prop('checked', false);
				multipleSelectWrap.show();
			}
		});

		$(':checkbox', checkboxAll).on('change', function () {
			if ($(this).is(':checked')) {
				$(this).closest(checkboxAll).prev(multipleSelectWrap).hide();
			} else {
				$(this).closest(checkboxAll).prev(multipleSelectWrap).show();
			}
		});

	});

}(jQuery));