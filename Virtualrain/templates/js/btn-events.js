$(document).ready(function() {
	var btnPressed = null;
	$('button').on('mousedown touchstart', function() {
		btnPressed = $(this);
		btnPressed.addClass('btn-pressed');
	});
	
	$('body').on('mouseup touchend', function() {
		if(btnPressed != null) {
			btnPressed.removeClass('btn-pressed');
		}
	});
});