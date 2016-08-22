String.prototype.trimRight = function(charlist) {
	if (charlist === undefined)
		charlist = "\s";

	return this.replace(new RegExp("[" + charlist + "]+$"), "");
};

String.prototype.trimLeft = function(charlist) {
	if (charlist === undefined)
		charlist = "\s";

	return this.replace(new RegExp("^[" + charlist + "]+"), "");
};

String.prototype.trim = function(charlist) {
	return this.trimLeft(charlist).trimRight(charlist);
};

String.prototype.ucfirst = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
};

// http://eltimn.github.io/jquery-bs-alerts/
$('.bs-alert').bsAlerts({
	'fade': 5000
});
function bsAlert(message, priority, fade) {
	if (typeof fade == 'undefined') {
		fade = 0;
	}
	$(document).trigger("add-alerts", {
		message: message,
		priority: priority, // error, warning, info, success
		fade: fade
	});
}

var initSelect2Ajax = function(sel, type, initialValue, allowNone) {
	var endpoint = '';
	var idField = '';
	var nameField = '';
	var typePlural = '';
	var onlyParents = false;
	switch (type) {
		case 'parent-business':
			onlyParents = true;
			endpoint = 'get-business-by-id';
			idField = 'businessID';
			nameField = 'displayName';
			typePlural = 'businesses';
			break;
		case 'business':
			endpoint = 'get-business-by-id';
			idField = 'businessID';
			nameField = 'displayName';
			typePlural = 'businesses';
			break;
		case 'employee':
			endpoint = 'get-employee-by-id';
			idField = 'employeeID';
			nameField = ['firstName', 'lastName'];
			typePlural = 'employees';
			break;
		case 'provider':
			endpoint = 'get-provider-by-npi';
			idField = 'providerNPI';
			nameField = ['firstName', 'lastName'];
			typePlural = 'providers';
			break;
		case 'costCenter':
			endpoint = 'get-business-by-cost-center';
			idField = 'costCenter';
			nameField = ['costCenter', 'displayName'];
			typePlural = 'businesses';
			break;
	}
	
	//console.log('init');
	
	sel.select2({
		width: '100%',
		ajax: {
			method: 'POST',
			url: "/api/search",
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					scope: [typePlural],
					'search-phrase': params.term,
					'max-results': 25,
					'include-inactive': true,
					'only-parents': onlyParents
				};
			},
			processResults: function (data, params) {
				//console.log(data);
				
				params.page = params.page || 1;

				data.total_count = data[typePlural].length;
				data.items = [];
				
				if ($(sel.find('option[value=""]')).length != 0) {
					data.items.push({id: '', text: "None"});
				}
				
				for (var i in data[typePlural]) {

					var text = '';
					if (Array.isArray(nameField)) {
						for ( var ni in nameField) {
							text += data[typePlural][i][nameField[ni]] + ' ';
						}
					}
					else {
						text = data[typePlural][i][nameField];
					}
					
					var item = {
						id: data[typePlural][i][idField],
						text: text
					};
					data.items.push(item);
				}
				
				return {
					results: data.items
				};
			}//,
			//cache: true
		},
		//escapeMarkup: function (markup) { return markup; },
		minimumInputLength: 1
	});
	
	sel.change(function() {
		setSelectOptionColor($(this));
	});

	//sel.empty().trigger('change');
	if (initialValue != null || typeof initialValue != 'undefined') {
		var opt = $('<option selected>loading...</option>').val(initialValue.id);
		sel.append(opt).trigger('change');
		$.ajax({
			method: 'GET',
			url: '/api/' + endpoint + '/' + initialValue.id,
			dataType: 'json'
		}).done(function (response) {
			//console.log(response);
			var text = '';
			if (Array.isArray(nameField)) {
				for ( var ni in nameField) {
					text += response[nameField[ni]] + ' ';
				}
			}
			else {
				text = response[nameField];
			}
			opt.text(text).val(response[idField]);
			opt.removeData();
			sel.trigger('change');
			setSelectOptionColor(sel);
		}).fail(function(response) {
			//sel.empty().trigger('change');
			opt.text('None').val('');
			opt.removeData();
			sel.trigger('change');
			setSelectOptionColor(sel);
		});
	}
	else {
		if (allowNone) {
			var opt = $('<option selected>None</option>').val('');
			sel.append(opt).trigger('change');
		}
	}
};

var setSelectOptionColor = function(sel) {
	if (sel.hasClass('select2-ajax')) {
		var id = sel.attr('id');
		var el = $('#select2-' + id + '-container');
		if (el.text() == 'None') {
			el.css('color', '#999');
		}
		else {
			el.css('color', '#555');
		}
	}
	else {
		if (sel.val() == '') {
			sel.css('color', '#999');
		}
		else {
			sel.css('color', '#555');
		}

		sel.change(function () {
			if ($(this).val() == '') {
				$(this).css('color', '#999');
			}
			else {
				$(this).css('color', '#555');
			}
		});
	}
};

$(document).ready(function() {
	$('.select').each(function() {
		setSelectOptionColor($(this));
	});
});