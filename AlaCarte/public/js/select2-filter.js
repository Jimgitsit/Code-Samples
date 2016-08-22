var initSelectFilters = function() {
	$(".select2-filter").each(function() {
		var sel = $(this);
		$(this).select2({
			width : "style",
			minimumResultsForSearch: Infinity
		});
		var id = $(this).attr("id");
		var prefix = $(this).attr("data-prefix");
		var container = $("#select2-" + id + "-container");
		var text = container.text();
		
		container.html('<span class="filter-prefix">' + prefix + ':</span><span class="filter-text"> ' + text + '</span>');
		
		$(this).change(function() {
			var text = container.text();
			container.html('<span class="filter-prefix">' + prefix + ':</span><span class="filter-text"> ' + text + '</span>');
			setTimeout(function() {
				$('.select2-container-active').removeClass('select2-container-active');
				$(':focus').blur();
			}, 1);
		});

		var filterSel = $(this).next(".select2-container--default").find(".select2-selection--single");

		filterSel.css("border", "1px solid transparent").css("background-color", "transparent");
		filterSel.hover(function(e) {
			if (e.type === "mouseenter") {
				$(this).css({"border": "1px solid #aaa", "background-color": "#e9e9e9"});
				//console.log('in');
				//console.log(container.find("span.filter-text"));
				container.find("span.filter-text").css("font-weight", "bold");
				container.find("span.filter-text").css("color", "red !important");
			}
			else {
				$(this).css({"border": "1px solid transparent", "background-color": "transparent"});
				//console.log('out');
				container.find("span.filter-text").css("font-weight", "normal");
				container.find("span.filter-text").css("color", "#7c7c7c !important");
			}
		});
		
		// TODO: Fix
		$("#select2-" + id + "-results").hover(function(e) {
			if (e.type === "mouseleave") {
				sel.select2("close");
			}
		});

		$(document).on('mousedown touchstart', function() {
			//sel.select2("close");
		});
	});
};

$(document).ready(function () {
	initSelectFilters();
});