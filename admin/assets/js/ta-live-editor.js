/*global jQuery:false */
var DEBUG = false;

(function ($) {
	"use strict";

	function lightenDarkenColor(col, amt) {
		var usePound = false;
		if (col[0] == "#") {
			col = col.slice(1);
			usePound = true;
		}
		var num = parseInt(col, 16);
		var r = (num >> 16) + amt;
		if (r > 255) r = 255;
		else if (r < 0) r = 0;
		var b = ((num >> 8) & 0x00FF) + amt;
		if (b > 255) b = 255;
		else if (b < 0) b = 0;
		var g = (num & 0x0000FF) + amt;
		if (g > 255) g = 255;
		else if (g < 0) g = 0;
		return (usePound ? "#" : "") + (g | (b << 8) | (r << 16)).toString(16);
	}

	// Make the function global
	// http://stackoverflow.com/a/2223370
	jQuery.taedNoSelection = function taedNoSelection() {
		$('.taed-elem-active').removeClass('taed-elem-active');
		$('.taed-sidebar-active').removeClass('taed-sidebar-active');
		$('.taed-field-active').removeClass('taed-field-active');
		$('.wrap').removeClass('wrap-has-sidebar');
	};

	$(function () {

		var elModal = $('.wpbo-modal'),
			elSidebar = $('.taed-sidebar'),
			elField = $('.taed-field'),
			btnWrap = $('.taed-buttons'),
			btnSave = $('.taed-save');

		if (jQuery().matchHeight) {
			$('.wpbo-col').matchHeight('.wpbo-grid-no-gutter');
		}

		elSidebar.on('click', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});

		elSidebar.on('click', '.taed-helper a', function (e) {
			window.open($(this).attr('href'));
		});

		$('#wpwrap').on('click', function () {
			$.taedNoSelection();
		});

		////////////////////////////////
		// WordPress Media Uploader //
		////////////////////////////////

		// https://github.com/thomasgriffin/New-Media-Image-Uploader
		// http://ssovit.com/using-wordpress-media-frame-uploader-in-wordpress-themes-or-plugins/
		if ($('.tgm-open-media').length > 0) {

			$('.tgm-open-media').on('click', function (e) {

				e.preventDefault();

				var tgm_media_frame = false,
					tgmTitle = $(this).data('tgmtitle') || 'Upload media',
					tgmBtn = $(this).data('tgmbtn') || 'Insert media',
					tgmLibType = $(this).data('tgmlibtype') || ['image', 'video'],
					inputText = $(this).prev('.tgm-new-media-image');

				if (tgm_media_frame) {
					tgm_media_frame.open();
					return;
				}

				tgm_media_frame = wp.media.frames.tgm_media_frame = wp.media({
					className: 'media-frame tgm-media-frame',
					frame: 'select',
					multiple: false,
					title: tgmTitle,
					library: {
						type: tgmLibType // mime types (example: video, image, mp4) as an array: ['image', 'video']
					},
					button: {
						text: tgmBtn
					}
				});

				tgm_media_frame.on('select', function () {
					var media_attachment = tgm_media_frame.state().get('selection').first().toJSON();
					inputText.val(media_attachment.url).change();
				});

				tgm_media_frame.open();
			});
		}

		/////////////////////
		// ACTION : SAVE //
		// Get outerHTML and submit form
		/////////////////////
		btnSave.on('click', function (e) {
			e.preventDefault();

			// Avoid multiple submissions
			$(this).prop('disabled', true).text('Saving...');

			// The active class shouldn't be saved
			$('.taed-elem-active').removeClass('taed-elem-active');

			// Get the outerHTML (http://jsperf.com/outerhtml-vs-jquery-clone-hack/4)
			var outerHtml = elModal.clone().wrap('<div>').parent().html();
			var outer = elModal.clone().wrap('<div>').parent();

			// Clean the HTML output (remove useless attributes)
			outer.find('[data-editable]').removeAttr('data-editable');
			var outerHtmlClean = outer.html();

			// Pass value in hidden textareas
			$('#taed-outerhtml').val(outerHtml);
			$('#taed-outerhtmlclean').val(outerHtmlClean);
			$(this).closest('form').submit();
		});

		/////////////////////////////////
		// ACTION : CANCEL AND RESET //
		// Reload page or template
		/////////////////////////////////
		btnWrap.on('click', '.taed-cancel, .taed-reset', function (e) {
			e.preventDefault();
			var conf = confirm($(this).attr('title'));
			if (conf === true) {
				// Go to URL
				window.location.href = $(this).attr('href');

				// Avoid multiple submissions
				$(this).prop('disabled', true).text('Please wait...');
			}
		});

		/////////////////////
		// ACTION : EDIT //
		// Read and Write CSS
		/////////////////////
		$(document).on('click', '[data-editable]', function (e) {
			e.stopPropagation();
			e.preventDefault();
			$.taedNoSelection();

			// Show sidebar & Buttons
			elSidebar.addClass('taed-sidebar-active');
			$('.wrap').addClass('wrap-has-sidebar');

			// Add active class to current node
			var $this = $(this).addClass('taed-elem-active');

			// Show controls & Populate values
			var elem = $(this).data('editable');
			$.each(elem, function (i, val) {

				var formControl = $('.taed-sidebar > .taed-' + elem[i] + ' > .form-control'),
					formFieldWrap = $('.taed-sidebar > .taed-' + elem[i]).addClass('taed-field-active');

				if (DEBUG) {
					console.log(formFieldWrap, formControl, $this.css(elem[i]));
				}

				switch (elem[i]) {

					///////////////////////////////////////////
					// Media Uploader: Background Image //
					///////////////////////////////////////////
				case 'backgroundImage':
					var bgImg = $this.css('backgroundImage').replace('url(', '').replace(')', '');
					formControl.val(bgImg);
					formControl.on('change keyup', function (e) {
						e.preventDefault();
						$(document).find('.taed-elem-active').css('backgroundImage', 'url(' + $(this).val() + ')');
					});
					break;

					////////////////////////////////
					// Media Uploader: Image //
					////////////////////////////////
				case 'img':
					formControl.val($this.attr('src'));
					formFieldWrap.find('.taed-helper > code').text($this.css('width'));
					formControl.on('change keyup', function (e) {
						e.preventDefault();
						$(document).find('.taed-elem-active').attr('src', $(this).val());
						$.fn.matchHeight._update();
					});
					break;

					//////////////////////
					// ColorPicker //
					//////////////////////
				case 'backgroundColor':
				case 'color':
					var colorRGB = $this.css(elem[i]);

					if (elem[i] == 'backgroundColor') {
						$('.taed-backgroundColor > .form-control').val(colorRGB);
						$('.taed-backgroundColor .wp-color-result').css('backgroundColor', $this.css(elem[i]));
					}
					if (elem[i] == 'color') {
						$('.taed-color > .form-control').val(colorRGB);
						$('.taed-color .wp-color-result').css('backgroundColor', $this.css(elem[i]));
					}
					formControl.wpColorPicker({
						defaultColor: false,
						change: function (event, ui) {
							var inputColor = ui.color.toString();
							$(document).find('.taed-elem-active').css(elem[i], inputColor);
							// @TODO: Darken the border-color if type is background
							if (elem[i] == 'backgroundColor') {
								$(document).find('.taed-elem-active').css('borderColor', lightenDarkenColor(inputColor, -20));
							}
						}
					});
					break;

					//////////////////////////////
					// Input Range (HTML5) //
					//////////////////////////////
				case 'fontSize':
					$('#fontSizeValue').val($this.css(elem[i]));
					formControl.val(parseInt($this.css(elem[i]), null));
					formControl.on('change input', function (e) {
						e.preventDefault();
						$('#fontSizeValue').val($(this).val() + 'px');
						var lineHeight = Math.floor(parseInt($(this).val().replace('px', ''), null) * 1.5);
						$(document).find('.taed-elem-active').css({
							"fontSize": $(this).val() + 'px',
							"lineHeight": lineHeight + 'px'
						});
					});
					break;

					/////////////////////////
					// Input textarea //
					/////////////////////////
				case 'textEdit':
					if ($this.is('input') || $this.is('textarea')) {
						formControl.val($this.attr('placeholder')).trigger('autosize.resize');
						formControl.on('change keyup', function (e) {
							e.preventDefault();
							$(document).find('.taed-elem-active').attr('placeholder', $(this).val());
						});
					} else if ($this.is('img')) {
						formControl.val($this.attr('alt')).trigger('autosize.resize');
						formControl.on('change keyup', function (e) {
							e.preventDefault();
							$(document).find('.taed-elem-active').attr('alt', $(this).val());
						});
					} else {
						formControl.autosize();
						formControl.val($this.html().replace(/<br>/gi, '\n')).trigger('autosize.resize');
						formControl.on('change keyup', function (e) {
							e.preventDefault();

							/*
							Trigger matchHeight & Autosize
							 */
							$.fn.matchHeight._update();
							formControl.trigger('autosize.resize');

							/*
							Add line breaks
							 */
							var value = $(this).val().replace(/\n/g, '<br>');

							/*
							Make sure to only edit the editable part
							And prevent the icon from being wiped out
							 */
							if ($this.find('span.taed-textEdit').length !== 0) {
								$(document).find('.taed-elem-active span.taed-textEdit').html(value);
							} else {
								$(document).find('.taed-elem-active').html(value);
							}
						});
					}
					break;

					//////////////////////////
					// Input textAlign //
					//////////////////////////
				case 'textAlign':
					var btnAlign = $('.taed-textAlign-select > .mce-btn');

					// Set active button
					btnAlign.removeClass('mce-active');
					$('.taed-textAlign-select > .mce-btn[data-align="' + $this.css(elem[i]) + '"]').addClass('mce-active');

					// onClick apply style
					btnAlign.on('click', function (e) {
						e.preventDefault();
						$(this).addClass('mce-active').siblings().removeClass('mce-active');
						$(document).find('.taed-elem-active').css(elem[i], $(this).data('align'));
					});
					break;

					////////////////////////
					// Input TinyMCE //
					////////////////////////
				case 'tinymce':
					if (tinyMCE && tinyMCE.activeEditor) {
						var edVersion = tinymce.majorVersion;

						if (edVersion < 4) {
							var ed = tinyMCE.getInstanceById('taed-tinymce-textarea');
							ed.setContent('');
							ed.setContent($this.html());
							ed.onKeyUp.add(function () {
								$this.html(ed.getContent());
								$.fn.matchHeight._update();
							});
							ed.onChange.add(function () {
								$this.html(ed.getContent());
								$.fn.matchHeight._update();
							});
						} else {
							var edv4 = tinyMCE.get('taed-tinymce-textarea');
							edv4.setContent('');
							edv4.setContent($this.html());
							edv4.on('change keyup', function (e) {
								$this.html(edv4.getContent());
								$.fn.matchHeight._update();
							});
						}
					}
					break;

				default:
					formControl.val($this.css(elem[i]));
					formControl.on('change keyup', function (e) {
						e.preventDefault();
						$(document).find('.taed-elem-active').css(elem[i], $(this).val());
					});
				}
			});

		});

		//////////////////
		// Modal Size //
		//////////////////
		var modalW = $('.taed-modalwidth'),
			modalH = $('.taed-modalheight');
		$(document).on('click', '.wpbo-inner', function (e) {
			$('.taed-modalsize').addClass('taed-field-active');
			modalW.val(elModal.css('width'));
			modalH.val(elModal.css('height'));
		});
		modalW.on('change', function () {
			elModal.css('width', $(this).val() + 'px');
		});
		modalH.on('change', function () {
			$('.wpbo-inner', elModal).css('height', $(this).val() + 'px');
			$.fn.matchHeight._update();
		});
		$('#taed-modalsize-reset').on('click', function (e) {
			e.preventDefault();
			elModal.css('width', '');
			$('.wpbo-inner', elModal).css('height', '');
			setTimeout(function () {
				modalW.val(elModal.css('width'));
				modalH.val(elModal.css('height'));
			}, 500);
		});

	});

}(jQuery));