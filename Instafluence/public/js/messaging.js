$(document).ready(function() {
	// TODO: Make these changes in css after we have Natt's css cleanup.
	$('.msg-wrap').css('top', '-100px');
	$('.msg-box').css('color', 'black');
	$('.msg-box').css('background', '#F5FF0F');
	$('.msg-box').css('border', '1px solid rgb(233, 231, 182)');
});

var msgTimeout = null;
var showMsg = function(message) {
	var wrap = $('.msg-wrap');

	if (msgTimeout != null) {
		clearTimeout(msgTimeout);
		wrap.css('top', '-100px');
	}
	
	$('.msg-content').text(message);
	
	if (window.innerWidth > 767) {
		// Desktop view
		wrap.css('top', '51px');
		wrap.animate({top: 78}, 250);
	}
	else {
		// Mobile view
		wrap.css('top', '-26px');
		wrap.animate({top: 0}, 250);
	}
	
	msgTimeout = setTimeout(function() {
		if (window.innerWidth > 767) {
			wrap.animate({top: -78}, 250);
			wrap.css('top', '-100px');
		}
		else {
			wrap.animate({top: -28}, 250);
			wrap.css('top', '-100px');
		}
	}, 5000);
};
