/**
 * Global variable urlParams is an object of the query string parameters from the URL.
 * Multiple values separated by commas are supported in any parameter. Therefore 
 * each property in urlParams is an array.
 * 
 * For example a URL like: http://blah.com?param1=value1,value2&param2=value3 will 
 * give an array like so:
 *      urlPrams == {
 *          param1: [
 *              value1,
 *              value2
 *          ],
 *          param2: [
 *              value3
 *          ]
 *      }
 */
var urlParams;
(window.onpopstate = function () {
	var match,
		pl     = /\+/g,  // Regex for replacing addition symbol with a space
		search = /([^&=]+)=?([^&]*)/g,
		decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
		query  = window.location.search.substring(1);

	urlParams = {};
	while (match = search.exec(query)) {
		var values = decode(match[2]).split(',');
		urlParams[decode(match[1])] = [];
		$.each(values, function(i, value) {
			urlParams[decode(match[1])][i] = value;
		});
	}

	//console.log(urlParams);
})();

/**
 * Returns the current URI with the query parameters defined in urlParams.
 * 
 * To add a parameter simply add to the urlParams global:
 *      urlParams.param1 = 'value1'; // for a single value
 *      urlParams.param1 = 'value1,value2'; // for multiple values
 * and call getURI()
 * 
 * Subsequently to remove a parameter remove it from the urlParams global:
 *      delete urlParams.param1
 * and call getURI()
 * 
 * @returns {string}
 */
var getURI = function() {
	var uri = window.location.protocol + '//' + window.location.hostname;
	if (window.location.port != '') {
		uri += ':' + window.location.port;
	}
	uri += window.location.pathname;
	
	if (Object.keys(urlParams).length > 0) {
		uri += '?';

		$.each(urlParams, function(name, value) {
			uri += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
		});

		uri = uri.slice(0, -1);
	}
	
	return uri;
};