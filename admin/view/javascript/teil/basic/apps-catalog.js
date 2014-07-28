/**
 * Apps catalog
 */
function AppCatalog(token) {
	AppCatalog.prototype.token = token;

	// Template for NOT installed apps
	AppCatalog.prototype.appTemplateNotInstalled = '#app-template-not-installed';
	AppCatalog.prototype.$appTemplateNotInstalled = $(AppCatalog.prototype.appTemplateNotInstalled);

	// Template for installed apps
	AppCatalog.prototype.appTemplateInstalled = '#app-template-installed';
	AppCatalog.prototype.$appTemplateInstalled = $(AppCatalog.prototype.appTemplateInstalled);
	
	// Template for just installed app (single app)
	AppCatalog.prototype.appTemplateInstalledSingle = '#app-template-installed-single';
	AppCatalog.prototype.$appTemplateInstalledSingle = $(AppCatalog.prototype.appTemplateInstalledSingle);
	

	AppCatalog.prototype.$container = $('#apps-container');
};


/**
 * Default settings
 */
AppCatalog.prototype.token = null;
AppCatalog.prototype.appTemplate = null;
AppCatalog.prototype.$appTemplate = null;
AppCatalog.prototype.$container = null;


/**
 * Initialization of the number slider
 *
 * @return void
 */
AppCatalog.prototype.init = function() {
	this.loadApps().done(this.appsLoaded);
};


/**
 * Load apps
 *
 * @return mixed
 */
AppCatalog.prototype.loadApps = function() {
	var promise = $.ajax({
		url: 'http://dev.website-builder.ru/modules?callback=?',
		type: 'get',
		dataType: 'jsonp',
		crossDomain: true
	});

	return promise;
};


/**
 * Get list of already installed apps
 *
 * @return void
 */
AppCatalog.prototype.loadMyApps = function() {
	var promise = $.ajax({
		url: '/admin/index.php?route=teil/home/my&token=' + AppCatalog.prototype.token,
		type: 'post',
		dataType: 'json'
	});

	return promise;
};


/**
 * Triggers when apps(list of all apps) are fetched
 *
 * @return void
 */
AppCatalog.prototype.appsLoaded = function(allAppsJson) {
	var filtered;

	console.log(allAppsJson);

	AppCatalog.prototype.apps = allAppsJson;

	// List of nstalled apps (my apps) are loaded
	AppCatalog.prototype.loadMyApps().done(function(myAppsJson) {
		// Filter apps, and get `not installed` list of apps
		filtered = AppCatalog.prototype.filter(allAppsJson, myAppsJson);

		AppCatalog.prototype.render(filtered);
	});
};


/**
 * Bind events
 *
 * @return void
 */
AppCatalog.prototype.bindEvents = function() {
	this.$container.find('.download-app-action').off('click').on('click', this.installModule);
	this.$container.find('.remove-app-action').off('click').on('click', this.removeModule);
};


/**
 * Show apps on the screen
 *
 * @return void
 */
AppCatalog.prototype.render = function(json) {
	var notInstalledTemplateSource = AppCatalog.prototype.$appTemplateNotInstalled.html(),
		installedTemplateSource = AppCatalog.prototype.$appTemplateInstalled.html();

	var appTemplate, html;

	// Append already installed apps
	appTemplate = Handlebars.compile(installedTemplateSource);
	html = appTemplate(json.installed);
	AppCatalog.prototype.$container.find('.app-list-installed').html(html);

	// Append not installed apps
	appTemplate = Handlebars.compile(notInstalledTemplateSource);
	html = appTemplate(json.notInstalled);
	AppCatalog.prototype.$container.find('.app-list-not-installed').html(html);

	AppCatalog.prototype.bindEvents();
};


/**
 * Install new app
 *
 * @return void
 */
AppCatalog.prototype.installModule = function(e) {
	var $this = $(this);

	$this.attr('disabled', 'disabled').addClass('loading');

	AppCatalog.prototype.downloadModule(
		$this,
		$this.data('module-name')
	);

	e.preventDefault();
};


/**
 * Remove app
 *
 * @return void
 */
AppCatalog.prototype.removeModule = function(e) {
	var $this = $(this);

	$.ajax({
		url: '/admin/index.php?route=teil/home/remove&token=' + AppCatalog.prototype.token,
		type: 'post',
		dataType: 'json',
		data: {
			module_name: $this.data('module-name')
		}
	})
	.done(function() {
		$this.closest('li').addClass('app-hidden');
	})
	.fail(function() {
		console.log("error");
	});
	

	e.preventDefault();
};


/**
 * Perform download a new app(module)
 *
 * @return void
 */
AppCatalog.prototype.downloadModule = function($btn, moduleSystemName) {
	var moduleDownloader = new ModuleDownloader($btn, moduleSystemName, this.token);
	
	// Download module
	moduleDownloader.download()
		.done(function() {
			AppCatalog.prototype.moduleDownloaded(moduleSystemName);
		})
		.fail(function() {
			console.log("Module `" + moduleSystemName + "` failed");
		});
	
	// Get download progress of the module
	moduleDownloader.progress();
};


/**
 * Module is successfully installed
 *
 * @return void
 */
AppCatalog.prototype.moduleDownloaded = function(moduleSystemName) {
	// Get module info by its name
	var module = this.findAppBySystemName(moduleSystemName);

	// Append installed app
	var installedTemplateSource = this.$appTemplateInstalledSingle.html(),
		appTemplate = Handlebars.compile(installedTemplateSource),
		html = appTemplate(module);

	this.$container.find('.app-list-installed').append(html);
	
	// Animate just created app
	var timerId = setTimeout(function() {
		AppCatalog.prototype.$container.find('li.app-hidden').removeClass('app-hidden');

		clearTimeout(timerId);
	}, 200);

	// Rebind all the events
	this.bindEvents();
};


/**
 * Here we are going to divide all json apps to:
 * - already installed (my)
 * - avalible to be installed (apps)
 *
 * @return void
 */
AppCatalog.prototype.filter = function(allAppsJson, myAppsJson) {
	var installed = { apps: [] },
		notInstalled = { apps: [] };

	// If there is no installed apps 
	// we will simply push all apps into `uninstalled` list
	if (myAppsJson.apps.length <= 0) {
		notInstalled = allAppsJson;
	};

	$.each(allAppsJson.apps, function(index, module) {
		$.each(myAppsJson.apps, function(moduleSystemName, moduleServiceProvider) {
			
			if (module.code == moduleSystemName) {
				installed.apps.push(module);

				module.is_installed = true;
				notInstalled.apps.push(module);
			} else {
				notInstalled.apps.push(module);
			};

		});
	});
console.log(myAppsJson);
	return {
		installed: installed,
		notInstalled: notInstalled,
		all: allAppsJson
	};
};


/**
 * Get app(module) by its system name
 *
 * @return mixed
 */
AppCatalog.prototype.findAppBySystemName = function(moduleSystemName) {
	var allApps = AppCatalog.prototype.apps, needleApp;

	$.each(allApps.apps, function(index, module) {
		if (module.code == moduleSystemName) {
			needleApp = module;

			return false;
		};
	});

	return needleApp;
};