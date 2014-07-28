
/**
 * Download new module
 *
 * @return void
 */
function ModuleDownloader($btn, moduleName, token) {
	this.token = token;
	this.$el = $btn;

	this.moduleName = moduleName;

	// If module is downloaded
	this.downloadComplete = false;

	// Simply progress timer id
	this.progressTimerId = 0;

	// Contains the maximum previous value of progress
	this.maxPrevProgress = 0;
};


/**
 * Simply send request to download new module
 *
 * @return void
 */
ModuleDownloader.prototype.download = function() {
	var _this = this;

	// Wend request to load new app(module)
	var promise = $.ajax({
		url: '/admin/index.php?route=teil/home/install&token=' + _this.token,
		type: 'post',
		dataType: 'json',
		data: {
			module_name: _this.moduleName
		},
	})
	.always(function() {
		clearTimeout(_this.progressTimerId);

		_this.downloadComplete = true;
		
		// Animate progress button to the 100%
		_this.$el.html('100%');

		// Add class `done` to the progress btn
		_this.$el.addClass('done');
	});

	return promise;
};


/**
 * Get current progress in percents
 *
 * @return void
 */
ModuleDownloader.prototype.progress = function() {
	var _this = this;

	$.ajax({
		url: '/admin/index.php?route=teil/home/getProgress&token=' + _this.token,
		type: 'post',
		dataType: 'json',
		data: {
			module_name: _this.moduleName
		},
	})
	.done(function() {
		console.log("success");
	})
	.fail(function() {
		console.log("error");
	})
	.always(function(progress) {
		// Validate progress
		progress = _this.validateProgress(progress);

		// Animate progress button
		_this.$el.html(Number(progress) + '%');

		// Clear timer
		clearTimeout(_this.progressTimerId);

		// Create new timer
		// OR animate progress to the 100%
		if ( ! _this.downloadComplete) {
        	_this.progressTimerId = setTimeout($.proxy(ModuleDownloader.prototype.progress, _this), 500);
		} else {
			_this.$el.html('100%');
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