//require <jquery.packed.js>

var $ = jQuery;

$(document).ready(function(){

	$('form').on('change keyup keydown', 'input, textarea, select', function (e) {
		$(this).addClass('changed-input');
	});

	var submit_pressed = 0;
	
	jQuery("input[type='submit']").click(function(){
		submit_pressed = 1;
	});	
	
	$(window).on('beforeunload', function () {
		if ($('.changed-input').length && submit_pressed == 0) {
			reset_submit(); //Reset the number of clicks for the submithandler script
			return 'You haven\'t saved your changes.';
		}
	});

});
