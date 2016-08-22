console.log("Loading alacarte.js...");

// This handles loading the correct version of jQuery without 
// interfering with any other version loaded from the parent page.
(function(window, document, version, callback) {
	var j, d;
	var loaded = false;
	if (!(j = window.jQuery) || version > j.fn.jquery || callback(j, loaded)) {
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "http://code.jquery.com/jquery-2.1.0.min.js";
		script.onload = script.onreadystatechange = function() {
			if (!loaded && (!(d = this.readyState) || d == "loaded" || d == "complete")) {
				callback((j = window.jQuery).noConflict(1), loaded = true);
				j(script).remove();
			}
		};
		(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script);
	}
})(window, document, "2.1", function($) {

	// The AlaCarte object
	var AlaCarte = {
		nodes: null,
		triggers: null,
		//apiBaseUrl: "http://jimmcgowen.com:8042/api",
		apiBaseUrl: "http://alacarte.dev/api",

		init: function(triggers) {
			console.log("Initializing AlaCarte...");
			console.log("Current triggers:");
			console.log(triggers);

			this.triggers = triggers;

			this.processNodes();
		},
		
		processNodes: function() {
			$(this.nodes).each(function(index, node) {
				//console.log(node.selector);
				// TODO: The selector does not work in a different browser!
				//var el = $(node.selector);
				var el = $('section#main>div#main-content>div#content-wrapper>div#preface>div.block-views-site-index-page-block-1>div.view.view-site-index-page.view-id-site_index_page.view-display-id-block_1>div.view-content>div.views-row.views-row-1.views-row-odd.views-row-first.views-row-last>div.views-field.views-field-field-front-left-column-content>div.field-content>p');
				console.log(el);
				if (el.length == 1) {
					el.attr('data-nodeid', node.nodeId);
					el.text(node.curContent);
				}
			});
		}
	};

	if (!(triggers instanceof Object)) {
		console.error("The triggers object is undefined.");
	}
	else {
		console.log("Requesting nodes...");
		$.ajax({
			url: AlaCarte.apiBaseUrl + "/nodes/get/simple",
			data: triggers,
			success: function (nodes) {
				console.log("Got nodes:");
				console.log(nodes);
				AlaCarte.nodes = nodes;

				$(document).ready(function () {
					console.log("Using jQuery version: " + $.fn.jquery);
					console.log('Document ready');

					AlaCarte.init(triggers);
				});
			},
			dataType: 'json'
		});
	}
});

console.log("alacarte.js loaded.");