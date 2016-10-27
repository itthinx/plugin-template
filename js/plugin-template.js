/*!
 * plugin-template.js
 *
 * Copyright (c) 2016 www.itthinx.com
 *
 * @author itthinx
 * @package plugin-template
 * @since 1.0.0
 */

/**
 * Parameter object.
 * doPost is used to flag if the query can be posted. It is false
 * if we are going somewhere else using document.location.
 */
var ixPluginTemplate = {
	doPost : true, 
	blinkerTimeouts : [],
	blinkerTimeout : 5000
};

(function($) {

/**
 * POST the result query and display the results.
 * 
 * The args parameter object allows to indicate:
 * - no_results : alternative text to show when no results are obtained
 * 
 * @param string containerId
 * @param string resultsId
 * @param string url
 * @param object args
 */
ixPluginTemplate.getResults = function( containerId, resultsId, url, args ) {

	if (!ixPluginTemplate.doPost) {
		return;
	}

	if ( typeof args === "undefined" ) {
		args = {};
	}

	var $results = $( "#"+resultsId ),
		$blinker = $( "#"+containerId ),
		blinkerTimeout = ixPluginTemplate.blinkerTimeout;

		$blinker.addClass('blinker');
		if ( blinkerTimeout > 0 ) {
			ixPluginTemplate.blinkerTimeouts["#"+containerId] = setTimeout(function(){$blinker.removeClass('blinker');}, blinkerTimeout);
		}
		var params = {
			"action" : "plugin_template"
		};
		$.post(
			url,
			params,
			function ( data ) {
				var results = '';
				if ( ( data !== null ) && ( data.length > 0 ) ) {
					var result_type = null,
						current_type = null;
					
					// Search results table start.
					results += '<table class="search-results">';
					for( var key in data ) {

						results += '<tr class="entry">';

						results += '<td class="result-info">';
						results += '<a href="' + data[key].url + '" title="' + data[key].content + '">';
						results += '<span class="title">' + data[key].content + '</span>';
						results += '</a>';
						results += '</td>';
						results += '</tr>';
					}
					results += '</table>';
					// Search results table end.
				} else {
					if ( typeof args.no_results !== "undefined" ) {
						if ( args.no_results.length > 0 ) {
							results += '<div class="no-results">';
							results += args.no_results;
							results += '</div>';
						}
					}
				}
				$results.show().html( results );
				$blinker.removeClass('blinker');
				if ( blinkerTimeout > 0 ) {
					clearTimeout(ixPluginTemplate.blinkerTimeouts["#"+containerId]);
				}
			},
			"json"
		);
};

})(jQuery);
