function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

var isApply = false;
var dataTable = null;

$(document).ready(function() {
	var isReset = false;
	var searchTerm = '';

	var inchesToFeetAndInches = function(inches) {
		var result = {};
		result.feet = Math.floor(inches / 12);
		result.inches = (inches % 12);
		return result;
	};

	var docHeight = $(window).height();
	//console.log("docHeight = " + docHeight);

	// *** Accordion *** //
	var allPanels = $('.accordion > dd').hide();
	var currPanel = null;
	$('.accordion > dt > a').click(function() {
		var nextPanel = $(this).parent().next();

		if(currPanel == null || (nextPanel.attr("id") != currPanel.attr("id"))) {
			allPanels.slideUp(200);
			nextPanel.slideDown(200);
		}

		currPanel = nextPanel;
		return false;
	});

	// *** DataTable setup ***
	var getFilters = function() {
		var filters = {};

		if(isReset) {
			filters.isReset = true;
			isReset = false;
			//console.log(filters);
			return filters;
		}

		if(isApply) {
			filters.isApply = true;
			isApply = false;
		}

		filters.limit = $('#filter-limit').val();
		filters.offset = $('#filter-offset').val();
		filters.onlyWithPrice = $('#onlyWithPrice').is(':checked');
		filters.onlySelectedAccountFollowers = $('#onlySelectedAccountFollowers').is(':checked');

		// Get the profile filters
		var profileFilters = $("#filterForm").find("[data-filter-type='profile']");
		if(profileFilters.length > 0) {
			filters.profileFilters = {};
			$(profileFilters).each(function(i, e) {
				e = $(e);
				var name = e.attr('name');
				var value = e.val();

				if(value != null) {
					if(e.attr("data-input-type") == "ab") {
						var a = $("#filterForm").find("#" + name + "_a")[0];
						var b = $("#filterForm").find("#" + name + "_b")[0];

						if ($(a).is(":checked") && !$(b).is(":checked")) {
							filters.profileFilters[name] = [$(a).val()];
						}
						else if (!$(a).is(":checked") && $(b).is(":checked")) {
							filters.profileFilters[name] = [$(b).val()];
						}
						else if ($(a).is(":checked") && $(b).is(":checked")) {
							filters.profileFilters[name] = [$(a).val(),	$(b).val()];
						}
					}
					if(e.attr("data-input-type") == "yesno") {
						// YESNO CHECK (yes/no checkboxes)
						var yes = $("#filterForm").find("#" + name + "_yes")[0];
						var no = $("#filterForm").find("#" + name + "_no")[0];

						if ($(yes).is(":checked") && !$(no).is(":checked")) {
							filters.profileFilters[name] = [true];
						}
						else if (!$(yes).is(":checked") && $(no).is(":checked")) {
							filters.profileFilters[name] = [false];
						}
						else if ($(yes).is(":checked") && $(no).is(":checked")) {
							filters.profileFilters[name] = [true,false];
						}
					}
					else if(e.attr("data-input-type") == "range") {
						// RANGE (slider)
						//value = $(e).slider('getValue');
						//console.log(value);
						if(value != "") {
							filters.profileFilters[name] = {};
							var split = value.split(",");
							filters.profileFilters[name].min = split[0];
							filters.profileFilters[name].max = split[1];
						}
					}
					else if(e.attr("data-input-type") == "multi") {
						// MULTI (with static fields)
						var filter = {};
						$(value).each(function(i, val) {
							filter[val] = true;
						});

						filters.profileFilters[name] = filter;
					}
					else if($.isArray(value)) {
						// MULTI (with single fields)
						filters.profileFilters[name] = value;
					}
					else if(e.attr("data-input-type") == "range_manual") {
						var min = $("#filterForm").find("#" + name + "-min").val();
						var max = $("#filterForm").find("#" + name + "-max").val();
						//console.log(name + '=' + min + ':' + max);

						filters[name] = {};
						filters[name].min = min;
						filters[name].max = max;
					}
					else if($.type(value) == 'string') {
						// SINGLE
						value = value.trim();
						if(value != "") {
							filters.profileFilters[name] = value;
						}
					}
				}

			});
		}

		// Get the social filters
		var socialFilters = $("#filterForm").find("[data-filter-type='social']");
		if(socialFilters.length > 0) {
			filters.socialFilters = {};
			$(socialFilters).each(function(i, e) {
				e = $(e);
				var name = e.attr('name');
				if(e.is(":checked")) {
					filters.socialFilters[name] = true;
				}
			});
		}

		//console.log(filters);
		return filters;
	};

	$('#exportFilter').on('click', function() {
		var btn = $(this);
		btn.html('Exporting...').attr('disabled', 'disabled');

		var exported_info = $('#exportInfluencerForm').serialize();

		$.ajax({
			url: '/admin/exportData/',
			method: 'POST',
			data: exported_info,
			success: function(response) {
				// console.log(response);
				window.open(response, '_blank');
				btn.html('Export').removeAttr('disabled');
				$('#exportInfluencersModal').modal('hide');
			}
		});
	});

	// Source for the data table is server side
	var skip = 0;
	dataTable = $('#influencers-table').DataTable({
		sScrollY: docHeight - 164,
		//bPaginate: true,
		//pages: 1,
		dom: 'rti',
		//scroller: {
		//	loadingIndicator: false
		//},
		deferRender: true,
		stateSave: true,
		//bFilter: false,
		//bInfo: false,
		//autoWidth: false,
		processing: true,
		serverSide: true,
		order: [[3, "desc"]],
		ajax: {
			url: "/admin/getDataNew/",
			data: function(data) {
				if (dataTable != null) {
					// Abort previous call
					var settings = dataTable.settings();
					settings[0].jqXHR.abort();
				}
				// Before request for table data
				data.filters = getFilters();
				//earchTerm: ' + searchTerm);
				if (searchTerm == '') {
					searchTerm = $("#search").val();
				}
				data.searchTerm = searchTerm;

				// data.limit = 500;
				// data.filters.offset = skip;

				//skip += 50;
				//console.log(data);
			}
		},
		drawCallback: function( settings ) {
			// After request for table data
			// console.log(settings);
			$("#totalInfluencers").text(numberWithCommas(settings.json.total_influencers));
			$("#totalReach").text(numberWithCommas(settings.json.total_reach));
			initRatings();
			$('#influencers-table').dataTable().fnAdjustColumnSizing( false );

			//settings.json.data = settings.json.data[0];
			//console.log(settings.json.data);

			var msg = $('.dataTables_scrollBody').find('.result-limit-msg');
			if (msg.length > 0) {
				msg.remove();
			}

			if (settings.json.truncated) {
				$('.dataTables_scrollBody').append('<div class="result-limit-msg">showing '+settings.json.max_results+' results offset by '+settings.json.results_offset+'</div>');
			}

			// $('#loadMore').on('click', function() {
			// 	skip += settings.json.max_results;
			// 	dataTable.ajax.reload();
			// });
		},
		columns: [
			{ "data": "email" },
			{ "data": "name", width: "50%", target: 2, render: function(data, type, full, meta) {
				//return data;
				var imgs = "";
				//console.log(full);
				$(full.social).each(function(i, e) {
					imgs += '<img title="' + e + '" src="/img/social_icons/' + e + '.png" class="social-icon pull-right">';
				});
				return '<span class="name pull-left">' + data + '</span>' + imgs;
			}},
			{ "data": "rating", width: "75px", render: function(data, type, full, meta) {
				//return data;
				var rating = '<input data-user="' + full.email + '" value="' + full.rating + '" type="number" class="rating pull-right" data-max="5" data-min="1" data-empty-value="0" />';
				return rating;
			}},
			{ "data": "followers", render: function(data, type, full, meta) {
				data = numberWithCommas(data);
				return data;
			}},
			{ "data": "delta_count", render: function(data, type, full, meta) {
				if (data == -1000) {
					data = '?';
				}
				else {
					if (data > 0) {
						data = '<img src="/img/growth-arrow-up.png">' + data + '%';
					}
					else if (data < 0) {
						data = '<img src="/img/growth-arrow-down.png">' + Math.abs(data) + '%';
					}
					else {
						data = data + '%';
					}
				}
				return data;
			}},
			/*
			{ "data": "delta_percent", render: function(data, type, full, meta) {
				//data = numberWithCommas(data);
				return data + '%';
			}},
			*/

			/*
			{ "data": "following", render: function(data, type, full, meta) {
				data = numberWithCommas(data);
				return data;
			}},
			{ "data": "daily_avg", render: function(data, type, full, meta) {
				data = numberWithCommas(data);
				return data;
			}},
			{ "data": "monthly_avg", render: function(data, type, full, meta) {
				data = numberWithCommas(data);
				return data;
			}},
			{ "data": "total_posts", render: function(data, type, full, meta) {
				data = numberWithCommas(data);
				return data;
			}},
			*/
			{ "data": "created", render: function(data, type, full, meta) {
				return data;
			}}
		]
	});

	// Hide the email column (it's only used to redirect to the edit page)
	dataTable.column(0).visible(false);

	var initRatings = function() {
		$('.rating').rating();
		$('.rating-input').each(function() {
			var el = $(this);
			el.addClass('rating-in-list');
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
	}

	var searchTimeout;
	$("#search").on('keyup', function() {
		clearTimeout(searchTimeout);

		searchTerm = this.value;

		searchTimeout = setTimeout(function() {
			dataTable.draw();
		}, 1000);
	});

	// Default column order
	//dataTable.column(1).order('asc');

	// Handle row click
	$('#influencers-table tbody').on( 'click', 'tr', function () {
		var rowData = dataTable.row(this).data();
		//console.log(rowData.email);

		//window.location = "/influenceredit/?id=" + encodeURI(rowData.email);

		// Open in a separate window
		/*
		var params = [
			'height=' + screen.height,
			'width=' + 1200,
			'left=' + (screen.width - 1200) / 2
		].join(',');
		window.open("/influenceredit/?id=" + encodeURI(rowData.email), rowData.email, params);
		*/

		// Open in a new tab
		window.open("/influenceredit/?id=" + encodeURI(rowData.email), '_blank');
	});

	// Set the height of the accordion
	$('.accordion dd').css('height', (docHeight - 335) + 'px');

	// Open the first accordion group
	currPanel = $('.accordion > dt:first-child > a').parent().next();
	currPanel.slideDown(0);

	// *** Saved filters dropdown *** //
	/*
	$('.filter-group .dropdown-menu li a').click(function() {
		var filterName = $(this).text();
		//$('#filter-input').val(filterName);

		//window.location = "/influencers/?sf=" + encodeURI(filterName);
	});
	*/

	$("#filterSaveBtn").click(function() {
		$(".filter-saving-text").show();

		var url = "/admin/saveFilterSet/";
		var data = {
			name: $("#filter-input").val(),
			filters: getFilters()
		};

		console.log(data);

		$.post(url, data, function(response) {
			//console.log(response);
			if(response.success == false && response.errorCode != 1) {
				//console.log('here');
				// TODO: Handle the error
				alert(response.error);
			}
			else {
				// TODO: Instead of redirecting why not just update the data table with the filters
				if(response.redirect != null) {
					window.location = response.redirect;
				}
				//dataTable.ajax.reload();
				$(".filter-saving-text").text("saved").fadeOut(3000);
			}
		}, 'json');
	});

	// *** Window resize handler ***
	$(window).resize(function() {
		var docHeight = $(window).height();
		//console.log("docHeight = " + docHeight);

		// Set the height of the accordion
		$('.accordion dd').css('height', (docHeight - 335) + 'px');

		// Set the height of the data table
		$('.dataTables_scrollBody').css('height', docHeight - 164);
	});

	// *** Filters ***
	$(".select2").select2();

	$("#followersSlider").slider().on("slide", function() {
		var values = $(this).val().split(",");
		$("#followersMin").text(values[0]);
		$("#followersMax").text(values[1]);
	});

	$("#ageSlider").slider().on("slide", function() {
		var values = $(this).val().split(",");
		$("#ageMin").text(values[0]);
		$("#ageMax").text(values[1]);
	});

	$("#heightSlider").slider().on("slide", function() {
		var values = $(this).val().split(",");
		var minFi = inchesToFeetAndInches(values[0]);
		var maxFi = inchesToFeetAndInches(values[1]);
		//console.log(minFi);
		$("#heightMin").text(minFi.feet + "' " + minFi.inches + '"');
		$("#heightMax").text(maxFi.feet + "' " + maxFi.inches + '"');
	});

	$("#shoeSizeSlider").slider().on("slide", function() {
		var values = $(this).val().split(",");


		$("#shoeSizeMin").text(parseFloat(values[0]).toFixed(1));
		$("#shoeSizeMax").text(parseFloat(values[1]).toFixed(1));
	});

	$("#pantWaist").slider().on("slide", function() {
		var values = $(this).val().split(",");
		$("#pantWaistMin").text(values[0]);
		$("#pantWaistMax").text(values[1]);
	});

	$("#pantLength").slider().on("slide", function() {
		var values = $(this).val().split(",");
		$("#pantLengthMin").text(values[0]);
		$("#pantLengthMax").text(values[1]);
	});

	// Select/deselect all social networks checkbox
	var checkToggle = false;
	$("#socialCheckAll").click(function() {
		var checks = $("[data-filter-type='social']");
		$(checks).each(function() {
			var check = $(this);
			if(checkToggle) {
				check.prop("checked", false);
			}
			else {
				check.prop("checked", true);
			}
		});

		checkToggle = !checkToggle;

		/*
		if(checkToggle) {
			$("#socialCheckText").text("None");
		}
		else {
			$("#socialCheckText").text("All");
		}
		*/
	});

	// *** Filter buttons ***
	$("#applyFilters").click(function() {
		isApply = true;
		dataTable.ajax.reload();
	});

	$("#resetFilters").click(function() {
		if(confirm("Are you sure you want to reset all filters?")) {
			var sliders = $("#filterForm").find("[data-input-type='range']");
			$(sliders).each(function() {
				//console.log(slider);
				var e = $(this);
				var min = e.data('slider').min;
				var max = e.data('slider').max;
				//var test = e.data('slider').getValue();
				var value = [min, max];
				e.slider('setValue', value);

				var wrapper = e.closest(".slide-wrapper");

				var input = wrapper.find("input");
				input.val("");

				$('#zip').val("");

				var minSpan = wrapper.find(".range-text-min");
				var maxSpan = wrapper.find(".range-text-max");

				if(e.attr("id") == "heightSlider") {
					var minFi = inchesToFeetAndInches(min);
					var maxFi = inchesToFeetAndInches(max);
					min = minFi.feet + "' " + minFi.inches + "\"";
					max = maxFi.feet + "' " + maxFi.inches + "\"";
				}

				minSpan.text(String(min));
				maxSpan.text(String(max));
			});

			$(".select2").select2('val', '');

			$("input[type='checkbox']").prop("checked", false);

			$(".followers-input-min").val("0");
			$(".followers-input-max").val("100000000");

			$('#filter-limit').val("500");
			$('#filter-offset').val("0");

			isReset = true;
			dataTable.ajax.reload();
		}
	});



	// EXPORT FEATURE //
	$('#exportInfluencersBtn').click(function() {
		$('#exportInfluencersModal').modal('show');
	});

	$('#exportInfluencersModal').find('#exportBtn').click(function() {
		console.log(dataTable);
	});



	$('#addInfluencerBtn').click(function() {
		$('#newInfluencerModal').modal('show');
	});

	$('#newInfluencerForm').validate({
		rules: {
			email: {
				required: true,
				email: true,
				remote: '/admin/emailInUse/'
			}
		},
		messages: {
			email: {
				remote: 'This email is already in use.'
			}
		}
	});

	var newUserId = '';
	$('#newInfluencerModal').find('#continueBtn').click(function() {
		var form = $('#newInfluencerForm');
		if (form.valid()) {
			var data = form.serialize();
			$.post('/admin/addInfluencer/', data, function (response) {
				console.log(response);
				if (!response.success) {
					alert(response.msg);
				}
				else {
					$('#newInfluencerModal').modal('hide');
					//window.open("/influenceredit/?id=" + encodeURI(form.find('#email').val()), '_blank');
					newUserId = form.find('#email').val();
					$('#newInfluencerDoneModal').modal('show');
				}
			}, 'json');
		}
	});

	$('#newInfluencerDoneModal').find('#viewBtn').click(function() {
		$('#newInfluencerDoneModal').modal('hide');
		window.open("/influenceredit/?id=" + encodeURI(newUserId), '_blank');
	});

	/*
	var scrollReset = true;
	var scrollTimer = null;
	$('.dataTables_scrollBody').bind('scroll', function() {
		console.log($(this).scrollTop() + ' scrollReset = ' + scrollReset);
		clearTimeout(scrollTimer);
		if($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - 10) {
			scrollTimer = setTimeout(function() {
				skip += 50;
				//dataTable.draw();
				scrollReset = true;
			}, 50);
		}
		else if ($(this).scrollTop() > 1 && $(this).scrollTop() < 10) {
			if (scrollReset == false) {
				scrollTimer = setTimeout(function () {
					skip -= 50;
					//dataTable.draw();
					scrollReset = true;
				}, 50);
			}
			scrollReset = false;
		}
	})
	*/

});

// Called from child windows to refresh the list
var refreshList = function() {
	isApply = true;
	dataTable.ajax.reload();
};
