'use strict';


teil.directive('modulePopup', function ($http, TOKEN, ModuleDownloader, Module, $timeout, CONFIG_ADMIN_EMAIL, STORE_KEY_URL, $rootScope) {

	var controller = function ($scope) {
		$scope.userLicenseKey = '';
		$scope.purchasedTypePrice = 0;
		$scope.storeKeyStatus = true;
		$scope.showEnterKeyField = false;

		// Open popup
		$.magnificPopup.open({
			removalDelay: 300,
			midClick: true,
			items: {
				src: '#module-popup',
				type: 'inline'
			},
			callbacks: {
				beforeOpen: function() {
					this.st.mainClass = 'mfp-zoom-in';
				},
				afterClose: function() {
					$('#module-popup').remove();
				}
			}
		});

		// Load detail module info
		$scope.load = function() {
			var module, installedModules;

			Module.find($scope.module.code)
				.then(function(resp) {
					module = resp.module;
					installedModules = resp.installedModules;

					// Set if module is installed from prev state
					module.installed = $scope.module.installed;

					angular.extend($scope.module, Module.getLicenseKey($scope.module.code, installedModules));
					angular.extend($scope.module, module);

					$scope.init();
				});
		};

		// Initialize application
		$scope.init = function() {
			$scope.loading = false;
			$scope.isActiveType = false;
			$scope.isTrialKey = false;

			// Validate key.
			$scope.validateKey();
			
			// Set default selected option
			$scope.selectedType = $scope.module.types[0];

			angular.forEach($scope.module.types, function(el, index) {
				if (el.active) {
					$scope.selectedType = $scope.module.types[index];
					
					$scope.module.module_type_name 
						= $scope.module.module_type_name_free 
							= $scope.module.types[index].name;

					return false;
				};
			});

			var activeTypeID = 0;

			$scope.validateTrialKey();
			$scope.validateKey();

			angular.forEach($scope.module.types, function(el, index) {
				// if (el.active && $scope.isKeyValid) {
				if (el.active) {
					activeTypeID = el.id;
				};
			});

			angular.forEach($scope.module.types, function(el, index) {
				
				// Remove active state if local key != inserted_key
				if (! $scope.isKeyValid && el.active) {
					el.active = false;
				};

				// Check for better types
				if (activeTypeID > 0 && el.id <= activeTypeID) {
					el.hasBetterType = true;
				};

				// Check if module type is free
				if (el.real_price <= 0) {
					el.isFree = true;
				};

				// Set active state to free key if local key != inserted_key
				if (! $scope.isKeyValid && el.isFree) {
					el.active = true;
					$scope.module.module_type_name_free = el.name;
					
					$scope.isKeyFree = true;
				};

				// Check if user can extend license
				if (!el.isFree && el.active || !el.isFree && el.hasBetterType) {
					el.extendable = true;
				};

				$scope.module.types[index] = el;
			});

			console.log($scope);
		};


		// Validate trial key
		$scope.validateTrialKey = function() {
			// Because free keys always have zero price
			// if ($scope.selectedType.real_price > 0) {
				if ($scope.module.is_demo_key && ! $scope.module.purchased && ! $scope.module.regular_payment) {
					$scope.isTrialKey = true;
				};

				if ($scope.module.is_demo_key && ! $scope.module.purchased && $scope.module.regular_payment) {
					$scope.isTrialKey = true;
				};
			// };
		};


		// Validate license key
		$scope.validateKey = function() {
			// We will compare local key, that is located in module folder
			// and real key from out server
			$scope.isKeyValid = false;
			$scope.isKeyValidTrial = false;
			$scope.isKeyFree = false;
			$scope.isInsertedKeysMatch = false;

			if ($scope.module.purchased && $scope.module.purchased_key == $scope.module.key) {
				$scope.isKeyValid = true;
			};

			// Or if we have trial key
			if ( ! $scope.module.purchased && $scope.module.key == 'DEMO') {
				$scope.isKeyValid = true;
				$scope.isKeyValidTrial = true;
			};

			// Validate key time
			if ( ! $scope.module.days_left) {
				$scope.isKeyValid = false;
			};

			if ($scope.module.purchased_key == 'FREE') {
				$scope.isKeyFree = true;
				$scope.isKeyValid = true;
			};

			// Check if inserted keys mathc (in database and local)
			if ($scope.module.inserted_key == $scope.module.key) {
				$scope.isInsertedKeysMatch = true;
			};
		};

		// Perform action on button click (install or remove module)
		$scope.action = function(e, target) {
			var target = target || false;
			var $btn = angular.element(e.currentTarget);
			
			// Set loading state
			// $scope.loading = true;

			if ($scope.module.installed && !target || target == 'remove') {
				$scope.removeModule($btn);
			} else {
				$scope.downloadModule($btn);
			};
		};

		// Download module
		$scope.downloadModule = function($btn) {
			var downloader = ModuleDownloader.make($btn, $scope.module.code, TOKEN);
			
			// Perform download
			downloader.download()
				.success(function() { $scope.moduleFinished(true) })
				.error(function() { console.log("Module `" + $scope.module.code + "` failed") });

			// Get install process progress
			downloader.progress();
		};

		// Remove module from th system
		$scope.removeModule = function($btn) {
			var downloader = ModuleDownloader.make($btn, $scope.module.code, TOKEN);
			downloader.remove()
				.success(function() { $scope.moduleFinished(false) })
				.error(function() { console.log("Module `" + $scope.module.code + "` failed") });
		};

		// Module was installed successfully
		$scope.moduleFinished = function(installed) {
			var changeModuleInfoTimer = $timeout(function() {
				// Set module attribute `installed` to be true
				angular.forEach($scope.$parent.modules, function(el, i) {
					if ($scope.module.code == el.code) {
						$scope.module.installed = installed;
					};
				});

				// Pull module info
				$scope.load();

				// Update installed modules info
				$rootScope.$broadcast('modules.installed.update');

				// Clear timeout
				$timeout.cancel(changeModuleInfoTimer);
			}, 2800);
		};

		// Open billing page
		$scope.purchase = function(e, moduleTypeId) {
			var $btn = angular.element(e.currentTarget);

			var purchasingData = {
				module_code: $scope.module.code,
				module_type: moduleTypeId,
				domain: window.location.hostname,
				email: CONFIG_ADMIN_EMAIL
			};

			window.open(
				'http://dev.website-builder.ru/pay?' + $.param(purchasingData),
				'_blank'
			);

			// Open `enter license key` layer
			if ($scope.module.installed) {
				$scope.showEnterKeyField = true;
			};
		};


		// Store new license key
		$scope.storeKey = function() {
			$scope.loading = true;

			// Store key
			$http({
				url: STORE_KEY_URL + '&token=' + TOKEN,
				method: 'post',
				responseType: 'json',
				data: $.param({
					module_code: $scope.module.code,
					key: $scope.userLicenseKey
				}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			})
			.then(function(resp) {
				$scope.storeKeyStatus = resp.data.status;
				$scope.loading = false;

				if ($scope.storeKeyStatus) {
					$scope.module.key = $scope.userLicenseKey;
					
					$scope.showEnterKeyField = false;
					$scope.userLicenseKey = '';

					$scope.load();
				};
			});
		};

		// Get price of current module type
		$scope.getPurchasedTypePrice = function() {
			angular.forEach($scope.module.types, function(el, index) {
				if (el.active) {
					$scope.purchasedTypePrice = el.price;
				};
			});
		};

		// Toggle `enter new key` overlay
		$scope.toggleEnterKeyOverlay = function() {
			$scope.showEnterKeyField = !$scope.showEnterKeyField;
		};
	};

	// Set selected module
	var link = function($scope) {
		$scope.module = $scope.$parent.selectedModule;
		$scope.validateKey();

		$scope.loading = true;
		$scope.load();
	};

	return {
		restrict: 'E',
		replace: true,
		link: link,
		scope: {
			module: '='
		},
		templateUrl: '/admin/view/javascript/teil/templates/directives/module-popup.html',
		controller: controller
	};
});