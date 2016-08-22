;(function ( $, window, document, undefined ) {
	
	var pluginName = "pulseWait";
	var toggle = true;
	var visible = false;
	var pulseTimeout = null;
	
	var defaults = {
		text: "Loading..."
	};

	// Plugin constructor
	function Plugin( element, options ) {
		this.element = $(element);
		
		this.options = $.extend( {}, defaults, options) ;

		this._defaults = defaults;
		this._name = pluginName;

		this._init();
	}

	Plugin.prototype = {

		_init: function() {
			this.element.addClass("pulse-wait");
			this.element.text(this.options.text);
			this.element.hide();
		},

		_pulse: function() {
			toggle ? this._grow() : this._shrink();
			toggle = !toggle;

			var self = this;
			pulseTimeout = setTimeout(function() {
				self._pulse();
			}, 750); // The delay here must be coordinated with the css
		},

		_grow: function() {
			this.element.removeClass("shrink");
			this.element.addClass("grow");
		},

		_shrink: function() {
			this.element.removeClass("grow");
			this.element.addClass("shrink");
		},

		show: function() {
			this.hide();
			this.element.show();
			var self = this;
			setTimeout(function() {
				self._pulse();
			}, 50);
			visible = true;
		},

		hide: function() {
			this.element.removeClass("grow");
			this.element.removeClass("shrink");
			clearTimeout(pulseTimeout);
			this.element.hide();
			toggle = true;
			visible = false;
		}
	};

	$.fn[pluginName] = function ( options ) {
		return this.each(function () {
			var plugin;
			
			if (!$.data(this, "plugin_" + pluginName)) {
				$.data(this, "plugin_" + pluginName,
					new Plugin( this, options ));
			}
			else if (typeof options === 'string') {
				plugin = $.data(this, 'plugin_' + pluginName);
				var method = options;
				plugin[method].call(plugin, method);
			}
		});
	};

})( jQuery, window, document );