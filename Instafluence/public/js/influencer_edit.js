$(document).ready(function() {

	$('.tool-tip').tooltip();

	if (window.location.hash != '') {
		var tab = window.location.hash.replace('#', '');
		$('#userDetailTabs a[href="#' + tab + '"]').tab('show');
	}

	$('#userDetailTabs a').click(function() {
		window.location.hash = $(this).attr('href');
	});

	$(".select2").select2({
		minimumResultsForSearch: 40,
		allowClear: true,
		closeOnSelect: false
	});

	$(".select2-no-clear").select2({
		minimumResultsForSearch: 40,
		closeOnSelect: false
	});

	$('#selectSocialNetwork').change(function() {
		$('#urlBlock').hide();
		$('#usernameBlock').hide();

		$('#urlSocialName').text($(this).val());

		var opt = $(this).find(":selected");
		var type = opt.attr('data-type');
		switch (type) {
			case 'url': {
				$('#urlBlock').show();
				$('#addNetworkURL').focus();
				break;
			}
			case 'username':
			default: {
				$('#usernameBlock').show();
				$('#addNetworkUsername').focus();
				break;
			}
		}
	});

	$('#addNetworkUsername, #addNetworkURL').on('change keyup', function(e) {
		//console.log($(this).val());
		if ($(this).val().trim() == '') {
			$('#addNetworkBtn').prop('disabled', true);
		}
		else {
			$('#addNetworkBtn').prop('disabled', false);
		}
	});

	$('#addNetworkBtn').click(function() {
		$('#addNetworkBtn').prop('disabled', true);
		$('#addNetworkBusy').show();
		console.log($('#selectSocialNetwork').find(":selected"));

		var data = {
			email: $('#userHeader').find('#email').text(),
			network: $('#selectSocialNetwork').val(),
			type: $('#selectSocialNetwork').find(":selected").attr('data-type')
		};

		if (data.type == 'url') {
			data.url_or_username = $('#addNetworkURL').val();
		}
		else if (data.type == 'username') {
			data.url_or_username = $('#addNetworkUsername').val();
		}
		console.log(data);

		$.post('/influenceredit/addPublicNetwork/', data, function(response) {
			console.log(response);
			$('#addNetworkBusy').hide();
			$('#addNetworkModal').modal('hide');
			if (response.success == true) {
				window.location.reload();
			}
			else {
				showMsg(response.msg);
			}

			$('#addNetworkBtn').prop('disabled', false);
		}, 'json');
	});

	//$('#social-table *[data-toggle="tooltip"]').tooltip();
	$('#socialTable *[data-toggle="popover"]').css('cursor', 'pointer').popover();

	$("#refresh").click(function() {
		$('#refresh').prop('disabled', true);
		$('#refreshBusy').show();

		var data = {
			email: $("#email").text()
		}

		$.post('/influenceredit/updateSocial/', data, function(response) {
			$('#refreshBusy').hide();
			$('#refresh').prop('disabled', false);
			window.location.reload();
		});
	});

	$('.rating').rating();
	$('.rating-input').each(function() {
		var el = $(this);
		el.addClass('rating-custom');
		el.find('i').click(function() {

			setTimeout(function() {
				//console.log(el.find('input').val());

				var url = "/admin/saveRating/";
				var data = {
					email: el.find('input').attr('data-user'),
					rating: el.find('input').val()
				}

				$.post(url, data, function(response) {
					//console.log(response);
				});
			}, 100);
		});
	});

	var saveNote = function(value, settings) {
		// Save the note
		//console.log('save');
		//console.log(settings);

		var data = {
			email: settings.submitdata.email,
			noteId: settings.element.attr('data-note-id'),
			markup: value
		};

		$.post('/influenceredit/saveNote/', data, function(response) {
			console.log(response);

			if (settings.element.attr('data-note-id') == '') {
				//console.log('new id: ' + response.id);
				settings.element.attr('data-note-id', response.id);
			}
		}, 'json');

		return(value);
	};

	var initEditable = function(el, cancelRemoves) {
		el.editable(saveNote, {
			id        : el.attr('data-note-id'),
			element   : el,
			type      : 'textarea',
			cancel    : 'Cancel',
			submit    : 'Save',
			indicator : '<img src="img/refresh.gif">',
			tooltip   : 'Click to edit...',
			onblur    : 'ignore',
			//data      : 'This is the content of the note that we have made about this influencer.',
			cssclass  : 'note-editable',
			submitdata : {
				email: el.attr('data-email'),
				noteId: el.attr('data-note-id')
			}
		});

		el.click(function() {
			var content = $(this);
			var date = content.parent().find('.note-date');
			date.hide();

			content.find('.note-editable button').click(function() {
				// TODO: This doesn't work on new notes for some reason
				date.show();
				//date.css('display', 'inline');
			});

			content.find('.note-editable button[type="cancel"]').click(function() {
				if (cancelRemoves) {
					content.parent().remove();
				}
			});

			content.find('.note-editable button[type="submit"]').click(function(e) {
				if (content.find('textarea').val().trim() == '') {
					e.preventDefault();

					// Delete the note from the db
					//console.log('delete');

					var id = content.attr('data-note-id');
					if (id != '') {
						var data = {
							noteId: content.attr('data-note-id')
						};

						$.post('/influenceredit/deleteNote/', data, function(response) {
							//sconsole.log(response);
						}, 'json');
					}

					content.parent().remove();
				}

				cancelRemoves = false;
			});
		});
	}

	$('.editable').each(function() {
		initEditable($(this), false);
	});

	$('#addNoteBtn').click(function() {
		var newNote = $('.note-template').clone();
		newNote.removeClass('note-template');
		$('.note-list').append(newNote);
		newNote.show();
		var el = newNote.find('.editable');
		initEditable(el, true);
		el.click();
	});

	$('#inviteBtn').click(function() {
		var data = {
			email: $('#email').text()
		};

		$('#inviteEmail').text(data.email);

		$.get('/influenceredit/getInviteEmailText', data, function(respone) {
			$('#inviteEmailMsg').text(respone.text);
			$('#inviteMsgModal').modal('show');
		}, 'json');
	});

	$('#inviteSendBtn').click(function() {
		var data = {
			'email': $('#email').text(),
			'text': $('#inviteEmailMsg').val()
		};

		//console.log(data);

		$.post('/influenceredit/sendInvite/', data, function(response) {
			window.location.reload();
		}, 'json');
	});

	$('.expand-arrow').click(function() {
		var img = $(this);
		var network = img.attr('data-network');
		var accountIndex = img.attr('data-account-index');
		var status = img.attr('data-status');

		var rows = $('.expanded-row[data-network="' + network + '"][data-account-index="' + accountIndex + '"]');
		if (status == "closed") {
			rows.fadeIn();
			img.attr('data-status', 'open');
			img.attr('src', '/img/expand-down.png');
		}
		else {
			rows.fadeOut();
			img.attr('data-status', 'closed');
			img.attr('src', '/img/expand-left.png');
		}

	});

	$('#removeNetworkBtn').click(function() {
		var el = $(this);
		var data = {
			email: $('#email').text(),
			network: el.attr('data-network'),
			account_index: el.attr('data-account-index')
		};

		$.post("/influenceredit/removePublicNetwork/", data, function(response) {
			window.location.reload();
			//el.closest('tr').remove();
		}, 'json');
	});

	$('.remove-public-btn').click(function() {
		var network = $(this).attr('data-network');
		var accIndex = $(this).attr('data-account-index');

		$('#removeNetworkModal #networkNmae').text(network.charAt(0).toUpperCase() + network.slice(1));
		var modalBtn = $('#removeNetworkBtn');
		modalBtn.attr('data-network', network);
		modalBtn.attr('data-account-index', accIndex);

		$('#removeNetworkModal').modal('show');
	});

	$('#influencerTags').select2({
		width: '100%',
		containerCssClass: 'select2-tags',
		dropdownCssClass: 'select2-tags-dropdown',
		tokenSeparators: [",", " "],
		tags: function() {
			if ($('#allTags').val() != '') {
				var tags = $('#allTags').val().split(',');
				return tags;
			}
			else {
				return [];
			}
		}
	}).on('change', function(e) {
		// console.log(e);
		if ('added' in e) {// Update the dropdown list
			var newTag = e.added.text;
			var allTags = [];
			if ($('#allTags').val() != '') {
				allTags = $('#allTags').val().split(',');
			}
			if ($.inArray(newTag, allTags) == -1) {
				allTags.push(newTag);
				allTags = allTags.join(',');
				$('#allTags').val(allTags);
				$(this).trigger('chosen:updated');
			}
		}

		setTagClickhandler();

		var data = {
			email: $('#email').text(),
			tags: e.val
		};
		$.post('/influenceredit/saveTags/', data, function(response) {
			console.log(response);
		});
	});

	var setTagClickhandler = function() {
		$('.select2-tags .select2-search-choice').click(function() {
			setTimeout(function() {
				$('#influencerTags').select2('open');
			}, 100);
		});
	};
	setTagClickhandler();

	var setCPM = function(price, followers, el) {
		if (followers > 0) {
			var cpm = price / (followers / 1000);
			if (cpm == 0) {
				el.closest('.cpm-wrap').hide();
			}
			else {
				el.text('$' + cpm.toFixed(2)).closest('.cpm-wrap').show();
			}
		}
		else {
			el.closest('.cpm-wrap').hide();
		}
	};

	$('.price-input .price').each(function() {
		var input = $(this);
		var price = input.val();
		var isNumber = (/^\s*\d+\s*$/i).test(price);
		//console.log(isNumber);
		if (!isNumber) {
			price = 0;
		}

		var followers = input.attr('data-followers');
		var estF = input.closest('.social-row').find('.est-followers');
		if (estF.length != 0 ) {
			followers = estF.val();
		}
		setCPM(price, followers, input.closest('tr').find('.cpm'));
	});

	var priceTimeout;
	$('.price-input .price').on('change keyup', function(evt) {
		//console.log('blah');
		var input = $(this);
		clearTimeout(priceTimeout);
		priceTimeout = setTimeout(function() {
			var price = input.val().trim();
			if (price == '') {
				price = 0;
			}

			var isNumber = (/^\s*\d+\s*$/i).test(price);
			if (!isNumber) {
				price = 0;
			}

			var f = input.attr('data-followers');
			var estF = input.closest('.social-row').find('.est-followers');
			if (estF.length != 0 ) {
				f = estF.val();
			}

			setCPM(price, f, input.closest('tr').find('.cpm'));

			if (isNumber) {
				var data = {
					email: $('#email').text(),
					type: input.attr('data-type'),
					network: input.attr('data-network'),
					accountIndex: input.attr('data-account-index'),
					price: price
				};

				$.post('/influenceredit/savePrice/', data);
			}
		}, 500);
	});

	var estFollowersTimeout;
	$('.est-followers').on('change keyup', function(evt) {
		var input = $(this);
		clearTimeout(estFollowersTimeout);
		estFollowersTimeout = setTimeout(function() {
			var f = input.val().trim();
			if (f == '') {
				f = 0;
			}

			var isNumber = (/^\s*\d+\s*$/i).test(f);
			if (!isNumber) {
				f = 0;
			}

			if (isNumber) {
				input.closest('.social-row').find('.price-input .price').trigger('change');

				var data = {
					email: $('#email').text(),
					type: input.attr('data-type'),
					network: input.attr('data-network'),
					accountIndex: input.attr('data-account-index'),
					est_followers: f
				};

				$.post('/influenceredit/saveEstFollowers/', data);
			}

		}, 500);
	});

	// \/******* EDIT EMAIL *********\/
	$('#emailWrap').hover(
		function() {
			$('#emailEditBtn').fadeIn(100);
		},
		function() {
			$('#emailEditBtn').fadeOut(100);
	});

	$('#emailEditBtn').click(function() {
		$('#emailEdit').val($('#email').text());

		$('#emailWrap').hide();
		$('#emailEditWrap').show();
		$('#emailEdit').focus();
	});

	$('#emailEditCancelBtn').click(function() {
		$('#emailWrap').show();
		$('#emailEditWrap').hide();
	});

	//console.log(window.location.href.split('?')[0]);

	$('#emailEditSaveBtn').click(function() {
		var data = {
			oldEmail: $('#email').text(),
			newEmail: $('#emailEdit').val()
		};

		$.post('/influenceredit/changeEmail/', data, function(response) {
			console.log(response);
			if (response.success == false) {
				alert(response.msg);
				$('#emailEdit').focus();
			}
			else {
				console.log('here');
				window.location = window.location.href.split('?')[0] + '?id=' + data.newEmail;
			}
		}, 'json');
	});
	// ^******* EDIT EMAIL *********^

	$('#nameWrap').hover(
		function() {
			$('#deleteInfluencerBtn').fadeIn(100);
		},
		function() {
			$('#deleteInfluencerBtn').fadeOut(100);
	});

	$('#deleteInfluencerBtn').click(function() {
		$('#deleteInfluencerModal').modal('show');

	});

	$('#deleteInfluencerModalBtn').click(function() {
		var data = {
			email: $('#email').text()
		};

		$.post('/influenceredit/deleteInfluencer/', data, function(response) {
			if (response.success) {
				window.opener.refreshList();
				window.close();
			}
		}, 'json');
	});
});
