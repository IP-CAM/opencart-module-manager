'use strict';

// Factory
teil.service('Module', function ($http, $cookies, $q, ADMIN_LANGUAGE, TOKEN, MODULES_LIST_URL, INSTALLED_MODULES_LIST_URL, MODULES_DETAIL_URL) {

	var languageCode = ADMIN_LANGUAGE || $cookies.language || 'en';

	// List all the modules
	var getAllModules = function() {
		return $http.jsonp(MODULES_LIST_URL + languageCode);
	};

	// Get list of already installed apps
	var getInstalledModules = function() {
		return $http.get(INSTALLED_MODULES_LIST_URL + '&token=' + TOKEN);
	};

	// Get module info
	var getModule = function(moduleСode) {
		var url = MODULES_DETAIL_URL.replace('{module}', moduleСode);

		var moduleDeferred = $q.defer();
		var installedModulesDeferred = $q.defer();

		// Get module info
		$http.jsonp(url + languageCode)
			.success(function(module) {
				moduleDeferred.resolve(module);
			});

		// Get installed modules info
		getInstalledModules().success(function(modules) {
			installedModulesDeferred.resolve(modules);
		});

		// Load all the modules
		return $q.all({
			module: moduleDeferred.promise,
			installedModules: installedModulesDeferred.promise
		});
	};

	// Get module license key
	var getLicenseKey = function(moduleCode, installedModules) {
		if (installedModules[moduleCode] != undefined) {
			return installedModules[moduleCode];
		};
	};

	// Facade
	return {
		all 			: getAllModules,
		my 				: getInstalledModules,
		find 			: getModule,
		getLicenseKey 	: getLicenseKey
	};
});