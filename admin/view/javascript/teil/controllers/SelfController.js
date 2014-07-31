'use strict';

window.teil.controller(
	'SelfController',
	[
		'$scope', 
		'$http',
		'$timeout',
		'VERSION',
		'SELF_CHECK_VERSION',
		'TOKEN',
		'SELF_UPDATE_URL',
		function SelfController($scope, $http, $timeout, VERSION, SELF_CHECK_VERSION, TOKEN, SELF_UPDATE_URL) {
			
			$scope.showUpdate = false;
			$scope.disableButton = false;
			$scope.progress = 0;

			// Check current version
			$http.jsonp(SELF_CHECK_VERSION)
				.then(function(resp) {
					$scope.version = resp.data.version;

					if ($scope.version > VERSION) {
						$scope.showUpdate = true;
					};
				});

			// Perform self update
			$scope.update = function() {
				// Set loading to the module list scope
				$scope.forseLoading();
				$scope.disableButton = true;
				$scope.progress = 15;

				// Update script
				$http.get(SELF_UPDATE_URL + '&token=' + TOKEN)
					.then(function() {
						$scope.progress = 100;

						$timeout($scope.reload, 1200);
					});
			};

			// Simply reload page
			$scope.reload = function() {
				window.location.reload();
			};

			// Forse loading
			$scope.forseLoading = function() {
				var scope = angular.element($('#module-list-container')).scope().loading = true;
			};
		}
]);