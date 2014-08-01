'use strict';


(function() {

	function FlexibleProductsController($rootScope, $scope, $http, TOKEN) {
		
		$scope.insertProductName = '';
		$scope.loading = true;

		// Setting for autocomplete
		$scope.autocompleteSetting = {
		    options: {
		        delay: 500,
		        source: function(request, response) {
		        	$('#insertProductName').addClass('loading');

		            $.ajax({
		                url: 'index.php?route=module/teil_flexible_products/autocomplete&token=' + TOKEN + '&filter_name=' + encodeURIComponent(request.term),
		                dataType: 'json',
		                success: function(json) {
		                	$('#insertProductName').removeClass('loading');

		                    response($.map(json, function(item) {
		                        return {
		                            value: item.name,
		                            id: item.product_id,
		                            thumb: item.thumb
		                        }
		                    }));
		                }
		            });
		        },
		        select: function(event, ui) {
		            $scope.products.push({
		                product_id: ui.item.id,
		                name: ui.item.value,
		                thumb: ui.item.thumb
		            });

		            var data = $.map($scope.products, function(el) {
		                return el.product_id;
		            });

		            $scope.selected_ids = data.join(',');
		        	$scope.insertProductName = '';

		            return false;
		        },
		        focus: function(event, ui) {
		            return false;
		        }
		    }
		};

		// Setting for autocomplete for EDIT product
		$scope.autocompleteEditSetting = {
		    options: {
		        delay: 500,
		        source: function(request, response) {
		        	$('#insertProductName').addClass('loading');
		        	
		        	var updatingProduct = this.element.closest('.flexible-product-item');
		        	$scope.updatingProduct = angular.element(updatingProduct).scope();

		            $.ajax({
		                url: 'index.php?route=module/teil_flexible_products/autocomplete&token=' + TOKEN + '&filter_name=' + encodeURIComponent(request.term),
		                dataType: 'json',
		                success: function(json) {
		                	$('#insertProductName').removeClass('loading');

		                    response($.map(json, function(item) {
		                        return {
		                            value: item.name,
		                            id: item.product_id,
		                            thumb: item.thumb
		                        }
		                    }));
		                }
		            });
		        },
		        select: function(event, ui) {
		        	angular.extend($scope.updatingProduct.product, {
		        		product_id: ui.item.id,
		        		name: ui.item.value,
		        		thumb: ui.item.thumb
		        	});

		         	var data = $.map($scope.products, function(el) {
		                return el.product_id;
		            });

		            $scope.selected_ids = data.join(',');
		        	$scope.updatingProduct.product.insert_value = '';
		        	$scope.updatingProduct.product.is_editing = false;

		            return false;
		        },
		        focus: function(event, ui) {
		            return false;
		        }
		    }
		};
		
		// Settings for sortable
		$scope.sortableSetting = {
			placeholder: "ui-state-highlight",
			stop: function(e, ui) {
				var data = $.map($('#flexible-product input.product-id'), function(element){
					return $(element).attr('value');
				});

				$scope.selected_ids = data.join(',');
				$scope.$apply();

				// Animate
				var next = ui.item.next();
                next.css({'-moz-transition':'none', '-webkit-transition':'none', 'transition':'none'});
                setTimeout(next.css.bind(next, {'-moz-transition':'border-top-width 0.1s ease-in', '-webkit-transition':'border-top-width 0.1s ease-in', 'transition':'border-top-width 0.1s ease-in'}));
			},
		};

		// Fetch setting
		$http
			.get('/admin/index.php?route=module/teil_flexible_products/settings&token=' + TOKEN)
			.then(function(resp) {
				$scope.products = resp.data.products || [];
				$scope.selected_ids = resp.data.selected_ids;

				$scope.loading = false;
			});

		// Edit product
		$scope.edit = function(product) {
			product.is_editing = !product.is_editing;
		};

		// Remove element
		$scope.remove = function(productId) {
			angular.forEach($scope.products, function(el, index) {
				if (el.product_id == productId) {
					$scope.products.splice(index, 1)
				};
			});

			var data = $.map($scope.products, function(el){
				return el.product_id;
			});
			
			$scope.selected_ids = data.join(',');
		};

		// Save form
		$scope.save = function(e) {
			$scope.loading = true;

			$http({
                method: 'POST',
                url: '/admin/index.php?route=module/teil_flexible_products/store&token=' + TOKEN,
                data: $('#form').serialize(),
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            })
            .then(function(resp) {
				$scope.loading = false;

				if (!status) $scope.resolveErrors(resp);
			});	

			e.preventDefault()
		};

		// Resolve errors
		$scope.resolveErrors = function(resp) {
			$scope.errors = resp.data;
		};

	};

	window.teil.controller('FlexibleProductsController', FlexibleProductsController);

})();