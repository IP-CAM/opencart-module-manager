<?php echo $header; ?>

<?php echo $column_left; ?>

<?php echo $column_right; ?>
<div id="content">

<?php echo $content_top; ?>

  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>

  <h1><?php echo $heading_title; ?></h1>

  <style type="text/css">
    .disabled {
      opacity: 0.5;
    }
    .disabled .counter {
      display: none;
    }
  </style>

  <div class="filter" ng-app="filterApp" ng-controller="FilterCtrl">

  <div class="filter-container" ng-show="data">
    <div 
      class="filterGroup" 
      style="border: 1px dashed #e2e2e2; padding: 10px;" 
      ng-repeat="attrGroup in data.attributes" 
    >
      <h4>{{ attrGroup.name }}</h4>

      <label 
        ng-repeat="attr in attrGroup.items" 
        class="filtarable-{{ attrGroup.id }}-{{ attr.code }}" 
        data-attr-id="{{ attrGroup.id }}" 
        data-attr-text="{{ attr.text }}" 
      >
        <input type="checkbox" on-change="attribute"> {{ attr.text }} <span class="counter">({{ attr.total }})</span><br>
      </label>

    </div>

  </div>

  </div>

<br><br><br><br>


<script src="/admin/view/javascript/teil/bower_components/angular/angular.js"></script>
<script type="text/javascript">

  window.filterApp = angular.module('filterApp', []);

  filterApp.directive('onChange', function() {
    return {
      restrict: 'A',
      link: function(scope, el, attrs) {
        angular.element(el).on('change', scope.$parent.makeFilter);
      }
    }
  });

  filterApp.controller('FilterCtrl', function FilterCtrl($scope, $http, $timeout) {
    $scope.data = false;
    $scope.filterData = false;
    $scope.pageParams = <?php echo json_encode(PageParams::parseURL()) ?>;
    $scope.pageCategories = <?php echo json_encode($categories) ?>;

    console.log($scope.filters);

    // Get all the attribtes, options etc.
    $scope.getFilterData = function() {
      $http({
        url: '/index.php?route=product/category/info',
        method: 'post',
        responseType: 'json',
        data: $.param({
          page_params: $scope.pageParams,
          page_categories: $scope.pageCategories
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
        .success(function(resp) {
          $scope.data = resp;

          $scope.makeFilter();
        });
    };

    // Get filtered data
    $scope.makeFilter = function() {
      $http({
        url: '/index.php?route=product/category/filter',
        method: 'post',
        responseType: 'json',
        data: $.param({
          page_params: $scope.pageParams,
          page_categories: $scope.pageCategories,
          attributes: $scope.listAttributes()
        }),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      })
        .success(function(resp) {
          $scope.filterData = resp;

          $scope.setDisabled();
        });
    };

    // Create list of selected attributes
    $scope.listAttributes = function() {
      if ($scope.data.attributes == undefined) return [];

      var result = {},
          label = false,
          selectedAttributes = $('.filterGroup label input:checked');

      angular.forEach(selectedAttributes, function(el) {
        label = $(el).parent();

        var attrId = label.data('attr-id'),
            attrText = label.data('attr-text');

        if (result[attrId] == undefined) result[attrId] = [];

        result[attrId].push(attrText);
      });

      return result;
    };

    // Merges filter data
    $scope.setDisabled = function() {
      var result = {};

      // Disable all the attributes
      $scope.disableAttributes();

      // Loop and then recount and enable filtered attributes
      angular.forEach($scope.data.attributes, function(attr, attrId) {

        // Loop throught filtered attribute results
        angular.forEach($scope.filterData.attributes, function(filteredAttr, filteredAttrId) {

          if (attrId == filteredAttrId) {

            // Loop throught attributes
            angular.forEach(attr.items, function(attribute) {

              // Loop throught filtered results attribute texts
              angular.forEach(filteredAttr, function(filteredAttrTotal, filteredAttrCode) {

                // Remove disabled state from filtered item
                if (attribute.code == filteredAttrCode) {
                  attribute.total = filteredAttrTotal;
                  $scope.enableAttribute(".filtarable-" + attrId + "-" + attribute.code);

                  return true;
                };

              });
            });

            return true;
          };
        });
      });

    };

    // Set disabled to all the attributes
    $scope.disableAttributes = function() {
      $('.filterGroup label')
        .addClass('disabled')
        .find('input')
        .attr('disabled', true);
    };

    // Enable attribute by its unique classname
    $scope.enableAttribute = function(labelAttrClass) {
      $(".filterGroup label" + labelAttrClass)
        .removeClass('disabled')
        .find('input')
        .removeAttr('disabled');
    };


    $scope.getFilterData();

  });

</script>









  <?php if ($thumb || $description) { ?>
  <div class="category-info">
    <?php if ($thumb) { ?>
    <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
    <?php } ?>
    <?php if ($description) { ?>
    <?php echo $description; ?>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($categories) { ?>
  <h2><?php echo $text_refine; ?></h2>
  <div class="category-list">
    <?php if (count($categories) <= 5) { ?>
    <ul>
      <?php foreach ($categories as $category) { ?>
      <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
      <?php } ?>
    </ul>
    <?php } else { ?>
    <?php for ($i = 0; $i < count($categories);) { ?>
    <ul>
      <?php $j = $i + ceil(count($categories) / 4); ?>
      <?php for (; $i < $j; $i++) { ?>
      <?php if (isset($categories[$i])) { ?>
      <li><a href="<?php echo $categories[$i]['href']; ?>"><?php echo $categories[$i]['name']; ?></a></li>
      <?php } ?>
      <?php } ?>
    </ul>
    <?php } ?>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($products) { ?>
  <div class="product-filter">
    <div class="display"><b><?php echo $text_display; ?></b> <?php echo $text_list; ?> <b>/</b> <a onclick="display('grid');"><?php echo $text_grid; ?></a></div>
    <div class="limit"><b><?php echo $text_limit; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($limits as $limits) { ?>
        <?php if ($limits['value'] == $limit) { ?>
        <option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
    <div class="sort"><b><?php echo $text_sort; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($sorts as $sorts) { ?>
        <?php if ($sorts['value'] == $sort . '-' . $order) { ?>
        <option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
  </div>
  <div class="product-compare"><a href="<?php echo $compare; ?>" id="compare-total"><?php echo $text_compare; ?></a></div>
  <div class="product-list">
    <?php foreach ($products as $product) { ?>
    <div>
      <?php if ($product['thumb']) { ?>
      <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" title="<?php echo $product['name']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
      <?php } ?>
      <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
      <div class="description"><?php echo $product['description']; ?></div>
      <?php if ($product['price']) { ?>
      <div class="price">
        <?php if (!$product['special']) { ?>
        <?php echo $product['price']; ?>
        <?php } else { ?>
        <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
        <?php } ?>
        <?php if ($product['tax']) { ?>
        <br />
        <span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
        <?php } ?>
      </div>
      <?php } ?>
      <?php if ($product['rating']) { ?>
      <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
      <?php } ?>
      <div class="cart">
        <input type="button" value="<?php echo $button_cart; ?>" onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button" />
      </div>
      <div class="wishlist"><a onclick="addToWishList('<?php echo $product['product_id']; ?>');"><?php echo $button_wishlist; ?></a></div>
      <div class="compare"><a onclick="addToCompare('<?php echo $product['product_id']; ?>');"><?php echo $button_compare; ?></a></div>
    </div>
    <?php } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php } ?>
  <?php if (!$categories && !$products) { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php } ?>
  <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
function display(view) {
	if (view == 'list') {
		$('.product-grid').attr('class', 'product-list');
		
		$('.product-list > div').each(function(index, element) {
			html  = '<div class="right">';
			html += '  <div class="cart">' + $(element).find('.cart').html() + '</div>';
			html += '  <div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
			html += '  <div class="compare">' + $(element).find('.compare').html() + '</div>';
			html += '</div>';			
			
			html += '<div class="left">';
			
			var image = $(element).find('.image').html();
			
			if (image != null) { 
				html += '<div class="image">' + image + '</div>';
			}
			
			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}
					
			html += '  <div class="name">' + $(element).find('.name').html() + '</div>';
			html += '  <div class="description">' + $(element).find('.description').html() + '</div>';
			
			var rating = $(element).find('.rating').html();
			
			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}
				
			html += '</div>';
						
			$(element).html(html);
		});		
		
		$('.display').html('<b><?php echo $text_display; ?></b> <?php echo $text_list; ?> <b>/</b> <a onclick="display(\'grid\');"><?php echo $text_grid; ?></a>');
		
		$.totalStorage('display', 'list'); 
	} else {
		$('.product-list').attr('class', 'product-grid');
		
		$('.product-grid > div').each(function(index, element) {
			html = '';
			
			var image = $(element).find('.image').html();
			
			if (image != null) {
				html += '<div class="image">' + image + '</div>';
			}
			
			html += '<div class="name">' + $(element).find('.name').html() + '</div>';
			html += '<div class="description">' + $(element).find('.description').html() + '</div>';
			
			var price = $(element).find('.price').html();
			
			if (price != null) {
				html += '<div class="price">' + price  + '</div>';
			}
			
			var rating = $(element).find('.rating').html();
			
			if (rating != null) {
				html += '<div class="rating">' + rating + '</div>';
			}
						
			html += '<div class="cart">' + $(element).find('.cart').html() + '</div>';
			html += '<div class="wishlist">' + $(element).find('.wishlist').html() + '</div>';
			html += '<div class="compare">' + $(element).find('.compare').html() + '</div>';
			
			$(element).html(html);
		});	
					
		$('.display').html('<b><?php echo $text_display; ?></b> <a onclick="display(\'list\');"><?php echo $text_list; ?></a> <b>/</b> <?php echo $text_grid; ?>');
		
		$.totalStorage('display', 'grid');
	}
}

view = $.totalStorage('display');

if (view) {
	display(view);
} else {
	display('list');
}
//--></script> 

<?php echo $footer; ?>