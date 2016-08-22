$(document).ready(function() {

	//******************************************//
	//			   Shopping Cart    	    	//
	//******************************************//

	// Clear Cart
	$('.clear-cart-btn').click(function(){
		var active = $('#active-btns').val() == 1;
		if( active == true ){
			var data = {
				action: 'clear_cart_ajax'
			};
			$.post("/item", data, function(response) {
				if( response == 'success' ) {
					$('.cart-item').each(function(){
						$(this).remove();
					});
					$('#cart .total').hide();
					$('#cart .message').show();
				}
				$('.footer .cart p').hide();
			});
		}
	});
	
	// Clear selected
	$('.clear-items-btn').click(function() {
		console.log('here');
		var selectedItems = new Array();
		$('.item-checkbox').each(function(index, item) {
			if(this.checked) {
				selectedItems.push($(this).parent().attr('data-index'));
			}
		});
		
		var active = $('#active-btns').val() == 1;
		if( active == true ) {
			if( selectedItems.length > 0 ) {
				var data = {
					action: 'clear_selected_ajax',
					ids: selectedItems
				};

				$.post("/item", data, function(response) {
					if( response.success == true ) {
						$.each( selectedItems, function(){
							$(".cart-item[data-index=" + this + "]").remove();
							$(".finalize-cart-item[data-index=" + this + "]").remove();
							$('.total-price-with-tax').html(response.new_sub_total);
						});
						
						if($('#cart li').size() == 0) {
							$('#cart .total').hide();
							$('#finalize .total').hide();
							$('#cart .message').show();
							$('#finalize .message').show();
							$('.footer .cart p').hide();
						}
						else {
							$('.footer .cart p').text(response.new_count);
						}
					}
				}, 'json');
			}
		}
		
		$(this).hide();
	});
	
	// Close cart button
	$('.close-cart-btn').click(function() {
		$('.cart').click();
	});
	
	$('.finalize-clear-items-btn').click(function() {
		console.log('here');
		var selectedItems = new Array();
		$('.finalize-item-checkbox').each(function(index, item) {
			if(this.checked) {
				selectedItems.push($(this).parent().attr('data-index'));
			}
		});
		
		var active = $('#active-btns').val() == 1;
		if( active == true ) {
			if( selectedItems.length > 0 ) {
				var data = {
					action: 'clear_selected_ajax',
					ids: selectedItems
				};

				$.post("/item", data, function(response) {
					if( response.success == true ) {
						$.each( selectedItems, function(){
							$(".cart-item[data-index=" + this + "]").remove();
							$(".finalize-cart-item[data-index=" + this + "]").remove();
							$('.total-price-with-tax').html(response.new_sub_total);
						});
						
						if($('#cart li').size() == 0) {
							$('#cart .total').hide();
							$('#finalize .total').hide();
							$('#cart .message').show();
							$('#finalize .message').show();
							$('.footer .cart p').hide();
						}
						else {
							$('.footer .cart p').text(response.new_count);
						}
					}
				}, 'json');
			}
		}
		
		$(this).hide();
	});
	
	$('.side-cart-qty').focus(function() {
		var input = $(this);
		input.select();
		console.log(input.offset().top);
		$('#cart').animate({
			scrollTop: ($('#cart').scrollTop() + input.offset().top) - 22
		}, 500);
	});

	$('.side-cart-qty').mouseup(function(e) {
		return false;
	});
	
	$('.update-qty-btn').click(function() {
		// Send the new quantity and get a new sub-total back
		var items = Array();
		$('.cart-item').each(function() {
			items[$(this).attr('data-index')] = $(this).find('.side-cart-qty').val();
		});
		//console.log(items);
		var data = {
			action: 'update_cart_qty_ajax',
			items: items
		};
		
		$.post('/item', data, function(response) {
			//console.log(response);
			$('.total-price-with-tax').text(response.new_sub_total);
			$('#totalTable, .sub-total').show();
			
		}, 'json');
		
		$('.update-qty-btn').hide();
	});

	// No items in cart Text
	if($('#cart li').size() == 0) {
		$('.message').show();
	}
	else {
		$('.message').hide();
	}

	//******************************************//
	//			   Item Added To Cart	    	//
	//******************************************//
	
	// Update Text
	var fadeText = function(){
		$('.updateText').animate({
			opacity:0
		},2000, function(){
			$('.updateText').remove();
		});
	};

	var updateText = $('.updateText');
	if ( updateText != null ) {
		setTimeout(fadeText, 1000);
	}

	//******************************************//
	//			Search Related Stuff    	   	//
	//******************************************//

	// Search Text
	if( $('#productList li').size() == 0 ){
		$('.message2').show();
	};

	$('.search-within-check').click(function() {
		var check = $(this);
		if(check.hasClass('checked')) {
			check.removeClass('checked');
			$('#searchWithin').val('false');
		}
		else {
			check.addClass('checked');
			$('#searchWithin').val('true');
		}
		$('#searchInput').focus();
	});
	
	$('#searchForm').submit(function(e) {
		if($('#searchInput').val().trim() == '') {
			e.preventDefault();
		}
	});
});