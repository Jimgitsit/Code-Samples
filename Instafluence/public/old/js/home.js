
	var toggle=true;
	$('.menu-container').click(function(){
		if(toggle===true){
			$('.menu-bar').stop().animate({
				top:32,
				width:23,
				left:24,
			},240,function(){
				$('.menu-bar').animate({
					left:24,
					width:19
				},240,function(){
					$('#bar1').rotate({
						animateTo:-132,
						duration:250
					});
					$('#bar2').rotate({
						animateTo:-48,
						duration:250
					});
					$('#bar3').rotate({
						animateTo:-48,
						duration:250
					});
					
				})
			});
			$('.main').animate({
				left:270
			},400);
			$('#side-bar').animate({
				left:0
			},400);
			$('.menu-container').animate({
				left:200
			},400);
			toggle=false;
			return false;
		}
		else if(toggle===false){
			$('#bar1').stop().rotate({
				animateTo:0,
				duration:250
			});
			$('#bar2').stop().rotate({
				animateTo:0,
				duration:250
			});
			$('#bar3').stop().rotate({
				animateTo:0,
				duration:250
			})
			$('.menu-bar').stop().animate({
				width:19
			},251,function(){
				$('#bar1').animate({
					top:24
				},250)
				$('#bar2').animate({
					top:32
				},250)
				$('#bar3').animate({
					top:40
				},250)
			});
			$('.main').animate({
				left:0
			},400)
			$('#side-bar').animate({
				left:-270
			},400)
			$('.menu-container').animate({
				left:5
			},400)
			toggle=true;
			return false;
		};
	});
	
