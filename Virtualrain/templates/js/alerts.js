// Type is either 'info', 'danger', or 'success'.
var showMsg = function( type, message ) {
	// reset first
	$('.alert-wrapper').hide();
	
	$('.alert-wrapper-' + type + ' span').html(message);
	
	$('.alert-wrapper-' + type).show();
	if( type == 'info' || type == 'success' ) {
		$('.alert-wrapper-' + type).animate({ opacity: 1.0 }, 5000).fadeOut();
	}
};

var hideMsg = function() {
	$('.alert-wrapper').stop(true);
	$('.alert-wrapper').animate({ opacity: 1.0 }, 0).fadeOut();
};

$(document).ready(function() {
	$('.alert-close').click(function() {
		$('.alert-wrapper').hide();
	});
});