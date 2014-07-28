'use strict';


// angular.extend(scope.module, {
//     title: 'Redeclared',
//     test: 'hello'
// });

window.teil.controller(
	'CommonController',
	[
		'$scope', 
		'$http', 
		'$compile', 
		'$timeout', 
		'$rootScope', 
		'Module',
		function CommonController($scope, $http, $compile, $timeout, $rootScope, Module) {

			// Subscribe events
			$rootScope.$on('modules.installed.update', function() {
				Module.my().success($scope.myModulesLoaded);
			});

			// Number of total installed modules
			$scope.totalInstalledModules = 1;
			$scope.loading = true;

			// Parse all the modules
			$scope.modulesLoaded = function(resp) {
				$scope.modules = resp;
				
				Module.my().success($scope.myModulesLoaded);
			};

			// Get list of already installed apps
			$scope.myModulesLoaded = function(resp) {
				var modules = resp;

				// Check if some of modules are installed (set them `installed` = true)
				$scope.checkForInstalled(modules);

				// Start watching
				$scope.setUpWatchers();

				// Remove loading
				$scope.loading = false;
			};


			// Request for modules
			Module.all().success($scope.modulesLoaded);

			// Watch modules change
			$scope.setUpWatchers = function() {
				// Recalculate already installed modules
				$scope.$watch('modules', function(val) {
					var totalInstalled = 0;

					angular.forEach($scope.modules, function(el, index) {
						if (el.installed) {
							totalInstalled++;
						};
					});

					$scope.totalInstalledModules = totalInstalled;
				}, true);
			};

			// Check if some of modules are installed (set them `installed` = true)
			$scope.checkForInstalled = function(modules) {
				angular.forEach($scope.modules, function(el, index) {

					// Rollback
					$scope.modules[index].hasUpdate = false;

					// If there is module in our local index -> it is installed
					if (modules[el.code] != undefined) {
						$scope.modules[index].installed = true;
						$scope.modules[index].key = modules[el.code].key;

						$scope.totalInstalledModules++;
						
						// Check for version
						if (modules[el.code].version < el.version) {
							$scope.modules[index].hasUpdate = true;
						};
					};
				});
			};

			// Open popup with detail info of module
			$scope.openPopup = function(module) {
				$scope.selectedModule = module;

				// Create popup
				var popup = angular.element('<module-popup module="module"></module-popup>');
				$compile(popup)($scope);

				// Append popup to the body
				angular.element('body').append(popup);
			};
		}
]);