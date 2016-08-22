$(document).ready(function() {

	$(".select2").select2({
		minimumResultsForSearch: 40,
		allowClear: true,
		closeOnSelect: false
	});

	$('.social-table img, .social-table a').tooltip();

	var currentPage = 1;
	$('.profile-pages li').click(function() {
		var page = $(this).find('a').attr('data-page');

		if (page == currentPage) {
			return false;
		}

		$('#profile_page'+currentPage).hide();
		$('#profile_page'+page).show();

		$("div.profile-pages li").each(function() {
			$(this).removeClass('active');
		});
		//console.log(page);
		$('div.profile-pages li:nth-child('+page+')').addClass('active');

		currentPage = page;

		return false;
	});

	// FOR DEBUGGING
	//$(".profile-pages li:nth-child(4)").click();

	$(".continue-btn, .save-profile-btn").click(function() {

		var next = +currentPage + 1;
		//console.log(next);
		$(".profile-pages li:nth-child(" + next + ")").click();

		window.scrollTo(0, 0);
		$("html, body").animate({
			scrollTop: $(".profile-pages").offset().top - 1000
		}, 0)
	});

	$('.save-profile-btn').click(function() {
		var btn = $(this);
		var data = $('#profileForm').serialize();
		if (btn.attr('data-type') == 'finish') {
			data += '&completion=true';
		}

		//console.log(data);
		var url = $('#profileForm').attr('action');

		//showMsg('Saving profile...', false);

		$.post(url, data, function(response) {
			//console.log(response);
			if (btn.attr('data-type') == 'finish' && response.showFinish == true) {
				window.location = '/profile/finished/'
			}
			else {
				showMsg('Profile saved.', false);
			}
		}, 'json');
	});

	var hash = window.location.hash.substr(1);
	if (hash.length > 0 && hash != '_=_') {
		$('.profile-pages li:nth-child(' + hash +  ')').click();
	}

	$("#vineConnectBtn").click(function() {
		var connectBtn = $(this);
		connectBtn.prop("disabled", true);

		var data = {
			username: $("#vineModal #email").val(),
			password: $("#vineModal #password").val()
		};

		//console.log(data);

		$.post("/auth/vine/", data, function(response) {
			//console.log(response);
			connectBtn.prop("disabled", false);

			if (response.success == false) {
				alert(response.msg);
				return;
			}

			$('#vineModal').modal('hide');

			window.location.href = window.location.href;
		}, 'json');
	});

	$("#snapchatSaveBtn").click(function() {
		var connectBtn = $(this);
		connectBtn.prop("disabled", true);

		var data = {
			username: $("#snapchatModal #username").val(),
			followers: $("#snapchatModal #views").val()
		};

		$.post("/auth/snapchat/", data, function(response) {
			// console.log(response);
			connectBtn.prop("disabled", false);

			if (response.success == false) {
				alert(response.msg);
				return;
			}

			$('#snapchatModal').modal('hide');

			window.location.href = window.location.href;
		});
	});

	/*
	var saveTimeout;
	$('#initial_total_followers').on('change', function(e) {
		clearTimeout(saveTimeout);

		var input = $(this);
		var itf = input.val().trim();

		saveTimeout = setTimeout(function() {
			input.closest('.form-group').removeClass('has-error');

			var data = {
				initial_total_followers: itf
			}

			$.post('/profile/saveInitialTotalFollowers/', data, function(response) {
				//console.log(response);
				if (response.success == false) {
					input.closest('.form-group').addClass('has-error');
					return;
				}
				input.val(response.formattedNumber);
			}, 'json');
		}, 100);
	});
	*/

	$('#facebookConnectBtn').click(function() {
		var data = {
			pages: new Array(),
			accountIndex: $(this).attr('data-account-index')
		};

		$('#facebookModal').find('input:checkbox').each(function () {
			if (this.checked) {
				data.pages.push($(this).val())
			}
		});

		$.post('/auth/setFacebookPages/', data, function(response) {
			window.location = '/profile/';
		});
	});

	$('#facebookCancelBtn').click(function() {
		window.location = '/profile/';
	});

	var queryVars = getQueryVars();

	if (queryVars.fbm == 1) {
		$('#facebookModal').modal('show');
	}

	$('.social-btn').click(function(e) {
		var anchor = $(this);

		// These are temporarily disabled
		var title = anchor.find('.title').text();
		if (title == "LinkedIn" || title == 'Tumblr' || title == 'WordPress') {
			e.preventDefault();
			alert(title + ' is currently unavailable. Please try again later.');
			return;
		}

		if (title == 'Pinterest') {
			e.preventDefault();
			$('#pinterestModal').modal('show');
			$('#username').focus();
			return;
		} else if (title == 'Snapchat') {
			e.preventDefault();
			$('#snapchatModal').modal('show');
			return;
		}

		if (anchor.attr('data-connected') == 'true') {
			e.preventDefault();

			var title = anchor.find('.title').text();
			var modal = $('#addAccountModal');
			modal.find('.network').text(title);
			modal.modal('show');

			modal.find('#addAccountContinueBtn').click(function() {
				modal.modal('hide');
				window.location = anchor.attr('href');
			})
		}
	});

	$('#pinterestConnectBtn').click(function() {
		var data = {
			username: $('#username').val()
		};

		$('#pinterestModal').modal('hide');

		$.post('/auth/pinterest/', data, function(response) {
			if (response.success == false) {
				showMsg('Pinterest username not found.');
			}
			else {
				window.location = '/profile/';
			}
		}, 'json');
	});
});
