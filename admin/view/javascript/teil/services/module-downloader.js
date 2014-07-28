'use strict';

// Factory
teil.factory('ModuleDownloader', function ($http) {

	// Module downloader class
	function ModuleDownloader($btn, moduleCode, token) {
		this.token = token;
		this.$el = $btn;

		this.moduleCode = moduleCode;

		// If module is downloaded
		this.downloadComplete = false;

		// Simply progress timer id
		this.progressTimerId = 0;

		// Contains the maximum previous value of progress
		this.maxPrevProgress = 0;
	};


	// Return new instance of the module
	ModuleDownloader.make = function($btn, moduleCode, token) {
		return new ModuleDownloader(
			$btn,
			moduleCode,
			token
		);
	};

	// Simply send request to download new module
	ModuleDownloader.prototype.download = function() {
		var _this = this;

		// Set starting progress
		_this.$el.attr('progress', 10);

		// Wend request to load new app(module)
		var promise = $http({
			url: '/admin/index.php?route=teil/home/install&token=' + _this.token,
			method: 'post',
			responseType: 'json',
			data: $.param({module_code: _this.moduleCode}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		})
		.success(function() {
			clearTimeout(_this.progressTimerId);

			_this.downloadComplete = true;
			
			// Animate progress button to the 100%
			_this.$el.attr('progress', 100);

			// Add class `done` to the progress btn
			_this.$el.addClass('done');
		});

		return promise;
	};


	// Remove module from the system
	ModuleDownloader.prototype.remove = function() {
		var _this = this;

		// Set starting progress
		_this.$el.attr('progress', 10);

		// Wend request to load new app(module)
		var promise = $http({
			url: '/admin/index.php?route=teil/home/remove&token=' + _this.token,
			method: 'post',
			responseType: 'json',
			data: $.param({module_code: _this.moduleCode}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		})
		.success(function() {
			_this.$el.attr('progress', 100);
		});

		return promise;
	};


	// Get current progress in percents
	ModuleDownloader.prototype.progress = function() {
		var _this = this;

		$http({
			url: '/admin/index.php?route=teil/home/getProgress&token=' + _this.token,
			method: 'post',
			responseType: 'json',
			data: $.param({module_code: _this.moduleCode}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		})
		.success(function() {
			console.log("success");
		})
		.error(function() {
			console.log("error");
		})
		.then(function(progress) {

			// Validate progress
			progress = _this.validateProgress(progress.data);

			// Animate progress button
			_this.$el.attr('progress');

			// Clear timer
			clearTimeout(_this.progressTimerId);

			// Create new timer
			// OR animate progress to the 100%
			if ( ! _this.downloadComplete) {
	        	_this.progressTimerId = setTimeout($.proxy(ModuleDownloader.prototype.progress, _this), 500);
			} else {
				_this.$el.attr('progress', 100);
			};
		});
	};


	/**
	 * Validate number of progress
	 *
	 * Sometimes it happens that progress is like this:
	 * 0, 10, 16, 18, 45, 62, 0, 0, 0, 75
	 *
	 * We will convert it to:
	 * 0, 10, 16, 18, 45, 62, 62, 62, 62, 75
	 *
	 * @return Number
	 */
	ModuleDownloader.prototype.validateProgress = function(progress) {
		if (this.maxPrevProgress <= progress) {
			this.maxPrevProgress = progress;
		};

		if (this.maxPrevProgress > progress) {
			progress = this.maxPrevProgress;
		};

		return progress;
	};

	// Return facade
	return {
		make: ModuleDownloader.make
	};
});