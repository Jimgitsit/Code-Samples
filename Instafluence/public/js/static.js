$(document).ready(function() {
	$('#gateway, #gateway li').css('height',''+$(window).height()+'');
	// $('#gateway li').click(function(){

	// 	if( $(this).hasClass('app') ){
	// 		$('#app-trigger, #app-tab').addClass('active');
	// 	}
	// 	else if( $(this).hasClass('brand') ){
	// 		$('#brand-trigger, #brand-tab').addClass('active');
	// 	}
	// 	else if( $(this).hasClass('influencer') ){
	// 		$('#influencer-trigger, #influencer-tab').addClass('active');
	// 	}
	// 	$('#gateway').addClass('close');

	// });

	// $('nav ul li a').click(function (e) {
	//   e.preventDefault();
	//   $(this).tab('show');
	// });

	$('.modal-body ul li a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});

	var modal_tab = '';
	$('*[data-target="#myModal"]').click(function() {
		modal_tab = $('header').attr('class');
	});

	$('#myModal').on('show.bs.modal', function(e) {
		//console.log(modal_tab);
		if (modal_tab == 'Influencer')
			$('.modal-body ul li a[href="#influencer-form"]').tab('show');
	});

	/*
	$('#app-trigger, #gateway li.app').click(function(){
		//$('.navbar').css('background','#ff5d5f');
		$('html, body').animate({ scrollTop: 0 }, 0);
	});
	
	$('#brand-trigger, #gateway li.brand').click(function(){
		//$('.navbar').css('background','#5dc7d6');
		$('html, body').animate({ scrollTop: 0 }, 0);
	});
	
	$('#influencer-trigger, #gateway li.influencer').click(function(){
		//$('.navbar').css('background','#0ec5a4');
		$('html, body').animate({ scrollTop: 0 }, 0);
	});
	*/
	

	//form items
	$('#brand-contact-form').validate({
		rules : {
			first_name : {
				minlength : 2,
				required : true
			},
			email : {
				required : true,
				email : true
			},
			last_name : {
				minlength : 2,
				required : true
			},
			company : {
				minlength : 2,
				required : true
			}
		}
	});

	/*
	$('#influencer-contact-form').validate({
		rules: {
			first_name : {
				minlength : 2,
				required : true
			},
			email : {
				required : true,
				email : true
			},
			last_name : {
				minlength : 2,
				required : true
			},
			phone : {
				required : true
			}
		}
	});
	
	$('#_00NG00000067KEU').rules('add', {
		required : true
	});
	
	$('#_00NG00000067IgZ').rules('add', {
		required : true
	});
	
	$('#_00NG00000067KEZ').rules('add', {
		required : true
	});
	
	$('#_00NG00000067Ige').rules('add', {
		required : true
	});
	*/
	
	$('#forgotPw').click(function() {
		var forgotPw = $(this);
		forgotPw.hide();
		$('#forgotPwMsg').text('Processing...');
		
		var data = {
			reset_password: true,
			email: $('#email').val().trim()
		}
		//console.log(data);
		
		if (data.email == '') {
			$('.auth-error').text("Please enter your email address.");
			forgotPw.show();
			$('#forgotPwMsg').text('');
			return;
		}
		
		$.post('/login/', data, function(response) {
			console.log(response);
			if (response.success == false) {
				$('.auth-error').text("Could not find a user with that email address.");
				forgotPw.show();
				$('#forgotPwMsg').text('');
			}
			else {
				console.log('here');
				$('#forgotPwMsg').text("An email has been sent to " + response.email + " with further instructions.");
			}
		}, 'json');
	});
	
	$('.thepopover').popover();
});