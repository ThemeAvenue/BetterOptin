<link rel="stylesheet" type="text/css" href="<?php echo WPBO_URL; ?>bower_components/shepherd/css/shepherd-theme-arrows.css">
<style type="text/css">
body {
	overflow-x: hidden;
}
.taed-sidebar {
	right: 0;
	opacity: 0;
}
.taed-sidebar-active {
	opacity: 1;
}
.shepherd-step {
	z-index: 100;
}
</style>
<script type="text/javascript" src="<?php echo WPBO_URL; ?>bower_components/shepherd/shepherd.min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
	var tour;

	tour = new Shepherd.Tour({
		defaults: {
			classes: 'shepherd-theme-arrows',
			scrollTo: false
		}
	});

	tour.addStep('wpbo-step1', {
		text: 'Hover on elements to see which one are editable.<br> Then click on the one you want to edit.',
		title: 'Welcome to the Visual Editor',
		attachTo: '.wpbo-modal left',
		advanceOn: '[data-editable] click',
		showCancelLink: true,
		buttons: [{
			text: 'Next',
			events: {
				'click': function () {
					$('.wpbo-lead:first').click();
					setTimeout(function () {
						Shepherd.activeTour.next();
					}, 500);
				}
			}
		}]
	});

	tour.addStep('wpbo-step2', {
		text: [
			'The sidebar appears once<br> you clicked an editable element.',
			'You can now edit this element, or any other.'
		],
		title: 'Edition Mode',
		attachTo: '.taed-sidebar left',
		showCancelLink: true,
		buttons: [{
			text: 'Back',
			classes: 'shepherd-button-secondary',
			action: tour.back
		}, {
			text: 'Next',
			action: tour.next
		}]
	});

	tour.addStep('wpbo-step3', {
		text: 'Click on one of these button to save changes,<br> cancel or reset to the original template.',
		title: 'You\'re done! Congrats',
		attachTo: '.taed-buttons left',
		buttons: [{
			text: 'Back',
			classes: 'shepherd-button-secondary',
			action: tour.back
		}, {
			text: 'Close',
			action: tour.next
		}]
	});

	tour.start();

	tour.on('complete', function () {
		var data = {
			'action': 'wpbo_tour_completed'
		};
		$.post(ajaxurl, data);
	});

	tour.on('cancel', function () {
		var data = {
			'action': 'wpbo_tour_completed'
		};
		$.post(ajaxurl, data);
	});
});
</script>