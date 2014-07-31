'use strict';


(function() {

	function FlexibleProductsController($scope, $http, TOKEN) {
		
		$scope.insertProductName = '';
		$scope.products = [];
		$scope.loading = true;

		$scope.autocompleteSetting = {
			options: {
			delay: 500,
		      source: function(request, response) {
		        $.ajax({
		          url: 'index.php?route=module/teil_flexible_products/autocomplete&token=' + TOKEN + '&filter_name=' +  encodeURIComponent(request.term),
		          dataType: 'json',
		          success: function(json) {   
		            response($.map(json, function(item) {
		              return {
		                label: item.name,
		                value: item.product_id,
		                thumb: item.thumb
		              }
		            }));
		          }
		        });
		      }, 
		      select: function(event, ui) {
		        $scope.products.push({
	        		product_id: ui.item.value,
	        		name: ui.item.label,
	        		thumb: ui.item.thumb
		        });

		        var data = $.map($scope.products, function(el) {
		          return el.product_id;
		        });
		                
		        $scope.selected_ids = data.join(',');
		        $scope.insertProductName = '';
		              
		        return false;
		      },
		      focus: function(event, ui) {return false;}
		    }
		};
		
		$scope.sortableSetting = {
			stop: function(e, ui) {
				var data = $.map($('#flexible-product input.product-id'), function(element){
					return $(element).attr('value');
				});

				$('#selected-flexible-product-ids').attr('value', data.join());
			}
		};

		$http
			.get('/admin/index.php?route=module/teil_flexible_products/settings&token=' + TOKEN)
			.then(function(resp) {
				$scope.products = resp.data.products;
				$scope.selected_ids = resp.data.selected_ids;

				$scope.loading = false;
			});

	};

	window.teil.controller('FlexibleProductsController', FlexibleProductsController);

})();