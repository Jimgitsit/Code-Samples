console.log("Loading alacarte.js...");

$(document).ready(function() {
	console.log("Using jQuery version: " + $.fn.jquery);
	console.log('Document ready');

	// The AlaCarte object
	var AlaCarte = {
		triggers: null,
		apiBaseUrl: "http://jimmcgowen.com:8042/api",

		init: function(triggers) {
			console.log("Initializing AlaCarte...");

			if (!(triggers instanceof Object)) {
				console.error("The triggers object is undefined.");
			}

			console.log("Current triggers:");
			console.log(triggers);

			this.triggers = triggers;

			// Get changes for this domain, path, and triggers from AlaCarte
			console.log("Requesting nodes...");
			$.get(this.apiBaseUrl + "/nodes/get", null, function(response) {
				console.log("Got nodes:");
				console.log(response);

				// Process the change nodes

			}, 'json');
		}
	};
	
	AlaCarte.init(triggers);
});

console.log("alacarte.js loaded.");