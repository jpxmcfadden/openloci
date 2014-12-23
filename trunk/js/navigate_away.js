//require <jquery.packed.js>

var $ = jQuery;

$(document).ready(function(){

	$('form').on('change keyup keydown', 'input, textarea, select', function (e) {
		$(this).addClass('changed-input');
	});

	$(window).on('beforeunload', function () {
		if ($('.changed-input').length) {
			return 'You haven\'t saved your changes.';
		}
	});

});
