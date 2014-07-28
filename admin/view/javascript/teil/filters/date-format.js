'use strict';

window.teil.filter('dateFormat', function dateFormat($filter) {
	return function(text) {
		var date = Date.parse(text);

		return $filter('date')(date, "yyyy-mm-dd");
	}
});