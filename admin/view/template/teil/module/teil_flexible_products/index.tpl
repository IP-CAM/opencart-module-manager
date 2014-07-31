<?php echo $header ?>

<?php require DIR_APPLICATION . '/view/template/teil/teil-head.php' ?>
<link rel="stylesheet" type="text/css" href="/admin/view/stylesheet/teil/modules/teil_flexible_products/style.css">


<div id="content" class="watermark" ng-app="teil">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        	<?php echo $breadcrumb['separator']; ?>
    		<a href="<?php echo $breadcrumb['href']; ?>">
    			<?php echo $breadcrumb['text']; ?>
    		</a>
        <?php } ?>
    </div>

    <div class="box" ng-controller="FlexibleProductsController">
        <div class="heading">
            <h1><img alt="" src="view/image/module.png"><?php echo $heading_title ?></h1>

            <div class="buttons">
                <a class="button" onclick="$('#form').submit();">Save</a>
                <a class="button" href="<?php echo $cancel; ?>">Cancel</a>
            </div>
        </div>

        <div class="content has-loading overlay-bg" ng-class="{'overlay-bg': loading}">

            <div id="loading" ng-class="{'show-loading popin': loading}" class="clock-loading">
                <img src="/admin/view/image/teil/loading_clock.png" class="loading-icon">
            </div>
			
			<table class="form">
    			<tr>
    				<td>Test:</td>
    				<td>Demo</td>
    			</tr>
    		</table>


            <table class="form">
                <tr>
                    <td>Product</td>
                    <td><input ui-autocomplete="autocompleteSetting" ng-model="insertProductName" type="text" name="product" value="" /></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div id="flexible-product" class="sortable-product-list" ui-sortable="sortableSetting" ng-cloak>

                            <div id="flexible-product{{ product.product_id }}" class="flexible-product-item" ng-repeat="product in products">
                                <div class="flexible-main-image">
                                    <img ng-src="{{ product.thumb }}">
                                </div>

                                <div class="flexible-main-name">{{ product.name }}</div>

                                <div class="remove-icon"><span class="glyphicon glyphicon-remove"></span></div>
                                <div class="edit-icon"><span class="glyphicon glyphicon-edit"></span></div>
                                <input type="text" value="" class="new-product-input" name="changeproduct">
                                <input type="hidden" class="product-id" value="{{ product.product_id }}" />
                            </div>

                        </div>
                        
                        <input type="hidden" name="flexible_product" value="{{ selected_ids }}" id="selected-flexible-product-ids" />
                    </td>
                </tr>
            </table>

        </div>
    </div>

</div><!-- end #content -->


<?php require DIR_APPLICATION . '/view/template/teil/teil-scripts.php' ?>
<script src="/admin/view/javascript/teil/bower_components/angular-ui-sortable/sortable.min.js"></script>
<script src="/admin/view/javascript/teil/bower_components/ui-autocomplete/autocomplete.js"></script>
<script src="/admin/view/javascript/teil/modules/FlexibleProducts/controller.js"></script>


<?php echo $footer ?>