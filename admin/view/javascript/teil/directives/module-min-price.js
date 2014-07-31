'use strict';


teil.directive('moduleMinPrice', function () {
	var controller = function ($scope) {

		// Show needle price message
		$scope.formatPrice = function() {
			var minPrice = $scope.module.min_price;

			if (minPrice == 0) {
				return 'FREE';
			};

			return 'from $' + minPrice;
		};
	};

	return {
		restrict: 'E',
		replace: true,
		scope: {
			module: '='
		},
		templateUrl: '/admin/view/javascript/teil/templates/directives/module-min-price.html',
		controller: controller
	};
});