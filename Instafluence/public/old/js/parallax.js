$(document).ready(function(){

	var bg1 = $('.bg-1');
	var bg2 = $('.bg-2');
	var bg3 = $('.bg-3');
	var bg4 = $('.bg-4');
	var speed = -4;

	$(window).scroll(function() {
		var windowWidth = $(window).width();
			if(windowWidth > 1024){
				var pos1offSet = 600;
				var pos2offSet = 900;
				var pos3offSet = 265;
				var pos4offSet = 1265;

				var position1 = ((-window.pageYOffset+(pos1offSet*4)) / speed) + "px";
				var position2 = ((-window.pageYOffset+(pos2offSet*4)) / speed) + "px";
				var position3 = ((-window.pageYOffset+(pos3offSet*4)) / speed) + "px";
				var position4 = ((-window.pageYOffset+(pos4offSet*4)) / speed) + "px";

				// Waypoints
				var wp1 = $('#wp1');
				var wp2 = $('#wp2');
				var wp3 = $('#wp3');

				bg1.animate({
					backgroundPositionY: position1
				}, .00001)

				bg2.animate({
				    backgroundPositionY: position2
			    }, .00001)

				bg3.animate({
				    backgroundPositionY: position3
			    }, .00001)

			    bg4.animate({
				    backgroundPositionY: position4
			    }, .00001)

			}
	});

});







