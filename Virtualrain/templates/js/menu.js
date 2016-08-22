$(document).ready(function(){
	
	
	//******************************************//
	//		 			REUSABLE				//
	//******************************************//
	
	// Used in ( login.html, thankyou.html )
	// Centers the div vertically depending on the size of the screen
	var div= $('.center');
	div.css("top", ($(window).height() - div.height())/2  + 'px');
	
	// Set screen height for bottom navigation to stick when opening keyboard
	/*
	var screenHeight = parseInt($(window).height())-46;
	$('.screen').css('height',''+screenHeight+'px');
	*/

	//******************************************//
	//					RANDOM					//
	//******************************************//
	
	// Used in ( cart.html )
	// Used to prevent 'double click' when opening the cart / preventing 'clear cart' until activated
	var activate = function(){
		$('#active-btns').val("1");
	};
	var deactivate = function(){
		$('#active-btns').val("0");
	};
	
	//******************************************//
	//		 BOTTOM NAVIGATION ANIMATIONS		//
	//******************************************//
	
	// Menu Button
	var closed = true;
	var account = function(toggle){
			if( toggle == true){
				$('.side-bar').animate({
					left:0
				},300);
				$('.footer').animate({
					left:248
				},300);
				$('.screen').animate({
					left:248
				},300);
				$('.search-bar').animate({
					left:248
				},300);
				$('.top-nav').animate({
					left:248
				},300);
				
				closed = false;
			}
			else if( toggle == false ){
				$('.side-bar').stop().animate({
				left:-248
				},300);
				$('.footer').stop().animate({
					left:0
				},300);
				$('.screen').stop().animate({
					left:0
				},300);
				$('.search-bar').stop().animate({
					left:0
				},300);
				$('.top-nav').stop().animate({
					left:0
				},300);
				
				closed = true;
			}
		};
	
	$('.account').on('click', function(event) {
		event.stopPropagation();
		account(closed);
	});	
	
	
	
	// Search Button
	$('.search-bar').hide();
	var closed2 = true;
	var searchbar = function(toggle){
			if( toggle == true){
				$('.search-bar').show();
				$('.search-bar').animate({
					bottom:46
				},200);
				closed2 = false;
				var temp = $('#searchInput').val();
				$('#searchInput').focus().val('').val(temp);
			}
			else if( toggle == false ){
				$('.search-bar').animate({
					bottom:-43
				},200,function() {
					$('.search-bar').hide();
				});
				closed2 = true;
			}
		};

	$('.search').on('click', function(event) {
		event.stopPropagation();
		searchbar(closed2);
	});


	
	// Cart Button
	var closed4 = true;
	var cart = function(toggle){
			if( toggle == true){
				$('.side-bar').animate({
				left:-258
				},300);
				$('.footer').animate({
					left:-258
				},300);
				$('.screen').animate({
					left:-258
				},300);
				$('.search-bar').animate({
					left:-258
				},300);
				$('.top-nav').animate({
					left:-258
				},300);
				$('#cart').animate({
					right:0
				},300);
				$('.back').animate({
					left:263
				},300);
				
				setTimeout(activate, 610);
				closed4 = false;
			}
			else if( toggle == false ){
				$('.side-bar').animate({
				left:-258
				},300);
				$('.footer').animate({
					left:0
				},300);
				$('.screen').animate({
					left:0
				},300);
				$('.search-bar').animate({
					left:0
				},300);
				$('.top-nav').animate({
					left:0
				},300);
				$('#cart').animate({
					right:-258
				},300);
				$('.back').animate({
					left:5
				},300);
				
				closed4 = true;
				deactivate();
			}
		};

	$('.cart').on('click', function(event) {
		event.stopPropagation();
		cart(closed4);
	});
	
	//Allow Scrolling on older android devices
	/*
$(function(){
	    $('.screen').slimScroll({
	        height: ''+screenHeight+'px'
	    });
	});
*/


/*
	$('body').on('touchmove', function (e) {
	    var searchTerms = '.screen',
	        $target = $(e.target),
	        parents = $target.parents(searchTerms);
	
	    if (parents.length || $target.hasClass(searchTerms)) {
	        // ignore as we want the scroll to happen
	        // (This is where we may need to check if at limit)
	    } else {
	        e.preventDefault();
	    }
	});
*/
	
});
	
	

	
	
	