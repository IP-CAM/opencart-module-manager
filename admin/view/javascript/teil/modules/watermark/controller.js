'use strict';


(function() {

	function WatermarkController($scope, $http, TOKEN) {
		$scope.watermarkUrl = '';
		$scope.watermarkEditorUrl = '';
		$scope.watermarkPositions = [];
		$scope.selectedPotision = {};

		$scope.image = {
			width: '',
			height: ''
		};

		$scope.offset = {
			top: 0,
			bottom: 0,
			left: 0,
			right: 0,
			measurement: 'px'
		};

		// Fetch watermark positions
		$http
			.get('/admin/index.php?route=module/watermark/settings&token=' + TOKEN)
			.then(function(resp) {
				$scope.watermarkPositions = resp.data.positions;
				$scope.selectedPotision = resp.data.position;
				
				angular.extend($scope.watermarkPositions, resp.data.positions);

				$scope.watermarkUrl = resp.data.image;
				$scope.watermarkEditorUrl = resp.data.editor_image;
				$scope.image.height = resp.data.image_height;
				$scope.image.width = resp.data.image_width;
				
				angular.extend($scope.offset, resp.data.offset);

				$scope.selectedPotision = resp.data.positions[0];

				if (resp.data.position) {
					angular.extend($scope.selectedPotision, resp.data.position);
				};

				$scope.startWatching();
			});

		$scope.startWatching = function() {
			$scope.$watch('selectedPotision', function(val) {
				var imageWidth = angular.element('#preview-image').width(),
					imageHeight = angular.element('#preview-image').height();

				var containerWidth = angular.element('#watermark-image-preview').width(),
					containerHeight = angular.element('#watermark-image-preview').height();

				if (val.key.match(/^top-/i)) {
					$scope.offset.top = 0;
				};

				if (val.key.match(/^middle-/i)) {
					$scope.offset.top = (containerHeight - imageHeight) / 2;
				};

				if (val.key.match(/^bottom-/i)) {
					$scope.offset.top = containerHeight - imageHeight;
				};

				if (val.key.match(/-left$/i)) {
					$scope.offset.left = 0;
				};

				if (val.key.match(/-center$/i)) {
					$scope.offset.left = (containerWidth - imageWidth) / 2;
				};

				if (val.key.match(/-right$/i)) {
					$scope.offset.left = containerWidth - imageWidth;
				};
			}, true);
		};


		$scope.save = function() {
			$http({
				url: '/admin/index.php?route=module/watermark/store&token=' + TOKEN,
				method: 'post',
				responseType: 'json',
				data: $.param({
					image: angular.element('#logo').val(),
					image_width: $scope.image.width,
					image_height: $scope.image.height,
					position: $scope.selectedPotision,
					offset: $scope.offset
				}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			})
			.then(function(resp) {
				console.log(resp);
			});
		};

	};

	window.teil.controller('WatermarkController', WatermarkController);

})();