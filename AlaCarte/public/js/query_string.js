/**
 * Added QueryString to jQuery.
 * Usage: 
 *      $.QueryString['blah'] Returns the value of "blah".
 *      $.QueryString Returns an object with all the name=value pairs.
 */
(function($) {
	$.QueryString = (function(a) {
		if (a == "") return {};
		var b = {};
		for (var i = 0; i < a.length; ++i)
		{
			var p=a[i].split('=');
			if (p.length != 2) continue;
			b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
		}
		return b;
	})(window.location.search.substr(1).split('&'))
})(jQuery);

/**
 * Helper function that sets key=value in the query string.
 * Remove a key by leaving out the value param or passing null or ''.
 * If url is not passed then the current url will be used.
 * 
 * @param key The key to set
 * @param value (optional) The value to set to key
 * @param url (optional) The base url
 * 
 * @returns String The url with new query string.
 */
function UpdateQueryString(key, value, url) {
	if (!url) url = window.location.href;
	var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
		hash;

	if (re.test(url)) {
		if (typeof value !== 'undefined' && value !== null && value !== "")
			return url.replace(re, '$1' + key + "=" + value + '$2$3');
		else {
			hash = url.split('#');
			url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
			if (typeof hash[1] !== 'undefined' && hash[1] !== null)
				url += '#' + hash[1];
			return url;
		}
	}
	else {
		if (typeof value !== 'undefined' && value !== null && value !== "") {
			var separator = url.indexOf('?') !== -1 ? '&' : '?';
			hash = url.split('#');
			url = hash[0] + separator + key + '=' + value;
			if (typeof hash[1] !== 'undefined' && hash[1] !== null)
				url += '#' + hash[1];
			return url;
		}
		else
			return url;
	}
}