'use strict';


// Declare app level module which depends on filters, and services
window.teil = angular.module('teil', ['ngCookies', 'pascalprecht.translate'])
	.constant('VERSION', '0.2')
	.constant('TOKEN', angular.element('#token').val())
	.constant('ADMIN_LANGUAGE', angular.element('#admin-language').val())
	

	.value('DIR_TEIL_MODULES', angular.element('#dir-teil-modules').val())

	.value('CONFIG_ADMIN_EMAIL', angular.element('#admin-email').val())
	
	.value('SELF_CHECK_VERSION', 'http://dev.website-builder.ru/version?callback=JSON_CALLBACK')

	.value('MODULES_LIST_URL', 'http://dev.website-builder.ru/modules?callback=JSON_CALLBACK&language_code=')
	.value('MODULES_DETAIL_URL', 'http://dev.website-builder.ru/modules/{module}?jsonp=Y&callback=JSON_CALLBACK&language_code=')
	.value('INSTALLED_MODULES_LIST_URL', '/admin/index.php?route=teil/home/my')
	.value('SELF_UPDATE_URL', '/admin/index.php?route=teil/home/selfupdate')
	.value('STORE_KEY_URL', '/admin/index.php?route=teil/home/store')

	.config(function($translateProvider, ADMIN_LANGUAGE) {
		$translateProvider.preferredLanguage(ADMIN_LANGUAGE);
	});