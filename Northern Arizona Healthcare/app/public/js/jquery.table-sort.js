/**
 * Adds sorting to any html table.
 * 
 * This plugin simply adds the sort column and direction as a url query parameter. It is 
 * up to the backend processor to actually do the sorting and return the sorted results.
 * 
 * Requires the url-params.js file.
 * 
 * To use add the data-tsort-name attribute to the th elements you want to sort by. For 
 * example:
 *      <th data-tsort-name="employeeID">ID</th>
 * 
 * The value of data-tsort-name is what will be used in the URL query parameter along with 
 * the sort direction separated by a colon. Like:
 *      sort=employeeID:asc
 * (Note the colon is actually encoded to %3A)
 * 
 * Initialize like so:
 *      $('#myTable').tableSort();
 *      
 * Optionally provide the default sort column with the options defaultSortCol and defaultSortDir. 
 * for example:
 *      $('#myTable').tableSort(
 *          defaultSortCol: "my-column",
 *          defaultSortDir: 'asc'
 *      );
 */
;(function($){
	$.fn.extend({
		tableSort: function(options) {

			var $tableSort = this;
			
			this.defaultOptions = {
				defaultSortCol: null,
				defaultSortDir: 'asc'
			};

			this.upIcon = $('<span class="glyphicon glyphicon-triangle-top tsort-icon" aria-hidden="true"></span>');
			this.dnIcon = $('<span class="glyphicon glyphicon-triangle-bottom tsort-icon" aria-hidden="true"></span>');
			
			var settings = $.extend({}, this.defaultOptions, options);
			
			// Initialize the plugin on a table. For example: $('#myTable').tableSort();
			return this.each(function() {
				var $table = $(this);

				var setSortColumn = function(th, dir) {

					var sortName = th.attr('data-tsort-name');
					
					if (typeof dir === typeof undefined) {
						dir = th.attr('data-tsort-order');
					}

					if (typeof dir !== typeof undefined && dir !== false) {
						th.find('span.tsort-icon').remove();

						if (dir == 'asc') {
							th.attr('data-tsort-order', 'desc');
							th.append($tableSort.upIcon);
							
							urlParams.sort = sortName + ':asc';
						}
						else {
							th.attr('data-tsort-order', 'asc');
							th.append($tableSort.dnIcon);

							urlParams.sort = sortName + ':desc';
						}
					}
					else {
						$('.tsort-icon').remove();
						$table.find('th').removeAttr('data-tsort-order');

						th.attr('data-tsort-order', 'asc');
						th.append($tableSort.upIcon);
						
						urlParams.sort = sortName + ':asc';
					}
				};

				// Get the current sort from the URL
				var curSortName = null;
				var curSortDir = null;
				var curSort = urlParams['sort'];
				if (typeof curSort !== typeof undefined) {
					//console.log(curSort);
					curSort = curSort[0].split(':');
					curSortName = curSort[0];
					curSortDir = curSort[1];
				}
				
				// Initialize up each column in the table header
				var thead = $table.find('thead');
				thead.find('th[data-tsort-name]').each(function() {
					var th = $(this);
					
					th.addClass('tsort');

					// Add click handler to the column header
					th.click(function () {
						setSortColumn(th);
						window.location = getURI();
					});
				});
				
				// Set the current sort
				var th = null;
				if (curSortName !== null && curSortDir !== null) {
					th = $table.find('th.tsort[data-tsort-name="' + curSortName + '"]');
					setSortColumn(th, curSortDir);
				}
				else {
					if (settings.defaultSortCol !== null) {
						th = $table.find('th.tsort[data-tsort-name="' + settings.defaultSortCol + '"]');
						setSortColumn(th, settings.defaultSortDir);
					}
				}
			});
		}
	});
})(jQuery);