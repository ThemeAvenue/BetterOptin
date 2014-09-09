/*global jQuery:false */
var DEBUG = false;

jQuery(document).ready(function ($) {

	// Define variables
	var wpboTable = $('#wpbo-stats-general'),
		plotarea = $('#wpbo-stats-graph');

	// General
	$('.wpbo-stats-controls').on('change', 'select', function (event) {
		event.preventDefault();
		var form = $(this).parents('form');
		form.submit();
	});

	$('#wpbo-stats-reset').on('click', function (event) {
		event.preventDefault();
		var conf = confirm($(this).attr('title'));
		if (conf === true) {
			alert("You pressed OK!");
		} else {
			alert("You pressed Cancel!");
		}
	});

	////////////////
	// Flot Ajax //
	////////////////
	var data = {
		'action': 'wpbo_get_graph_data',
		'wpbo_analytics_popup': wpboTable.data('popup'),
		'wpbo_analytics_time': wpboTable.data('timeframe'),
		'wpbo_analytics_period': wpboTable.data('period')
	};
	$.post(ajaxurl, data, function (response) {

		if (DEBUG) {
			console.log(response);
		}

		var json = $.parseJSON(response);

		// Graph Options
		var graphOptions = {
			series: {
				lines: {
					show: true
				},
				points: {
					show: true
				}
			},
			grid: {
				color: '#ccc',
				borderWidth: 1,
				borderColor: '#666',
				hoverable: true
			},
			xaxis: {
				mode: 'time',
				minTickSize: json.scale.minTickSize,
				timeFormat: json.scale.timeformat,
				min: json.min,
				max: json.max
			},
			tooltip: true,
			tooltipOpts: {
				content: "%s: %y"
			}
		};

		// Draw graph
		$.plot(plotarea, [json.impressionsData, json.conversionsData], graphOptions);
		plotarea.removeClass('wpbo-loading');

	});

	/////////////////
	// DataTables //
	/////////////////
	wpboTable.dataTable({
		"paging": false,
		"info": false,
		"bFilter": false,
		"order": [
			[3, "desc"]
		],
		"aoColumnDefs": [{
			'bSortable': false,
			'aTargets': [5]
		}]
	});
	$('#wpbo-stats-today').circliful();

});