/*global jQuery:false */

(function ($) {
	'use strict';

	$(function () {

		/////////////////////////
		// jQuery Validation //
		// http://devblog.rayonnant.net/2013/09/bootstrap-3-jquery-validator-defaults.html
		/////////////////////////
		if (jQuery.validator) {
			jQuery.validator.setDefaults({
				debug: true,
				errorClass: 'has-error',
				validClass: 'has-success',
				ignore: "",
				highlight: function (element, errorClass, validClass) {
					$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
				},
				unhighlight: function (element, errorClass, validClass) {
					$(element).closest('.form-group').removeClass('has-error').addClass('has-success');
					$(element).closest('.form-group').find('.help-block').text('');
				},
				errorPlacement: function (error, element) {
					$(element).closest('.form-group').find('.help-block').text(error.text());
				},
				submitHandler: function (form) {
					if ($(form).valid()) {
						form.submit();
					}
				}
			});
		}

		//////////////////////////
		// MailCheck Function //
		// (can be used on blur or on submit)
		//////////////////////////
		var inputEmail = $('.optform #inputEmail');
		var domains = ['hotmail.com', 'gmail.com', 'aol.com'];
		var topLevelDomains = ["com", "net", "org"];
		$('#email-suggestions').on('click', 'a', function (e) {
			e.preventDefault();
			inputEmail.val($(this).text());
			$('#email-suggestions').html('');
		});

		function optMailCheck() {
			inputEmail.mailcheck({
				suggested: function (element, suggestion) {
					$('#email-suggestions').hide().html('Did you mean ' + '<a href="#">' + suggestion.full + '</a>?').fadeIn();
				},
				empty: function (element) {
					inputEmail[0].checkValidity();
				}
			});
		}

		// Validate the form
		$('.optform').validate();
		$('.optform').submit(function () {
			// prevent multiple form submissions
			if ($(this).valid()) {
				$('.optform [type="submit"]').prop('disabled', true);
				return true;
			}
			// trigger mailcheck on submit (if input email has value)
			if ($('#inputEmail').val()) {
				optMailCheck();
				return false;
			}
			return false;
		});

		// If input exists, we add custom validation rules
		if ($('#inputName').length > 0) {
			var inputName = $("#inputName");
			inputName.rules("add", {
				rangelength: [3, 20],
				messages: {
					required: "You must provide your name.",
					rangelength: "The user name must be between 3 and 20 characters in length."
				}
			});
		}
		if ($('#inputEmail').length > 0) {
			$('.optform #inputEmail').on('blur', function () {
				optMailCheck();
			});
		}

	});

}(jQuery));