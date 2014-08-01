<?php echo $header ?>
<style type="text/css">
    
    .sortable-product-list .flexible-product-item { display:block; position:relative; padding:12px 6px; z-index:1; }
    .sortable-product-list .flexible-product-item:after {  display:block; position:absolute; height:100%; width:100%; top:0px; left:0px; content:' '; border-radius:6px; z-index:-1; }

    .sortable-product-list .flexible-product-item { -moz-transition:border-top-width 0.1s ease-in; -webkit-transition:border-top-width 0.1s ease-in; border-top:0px solid rgba(0,0,0,0); }
    .marker { opacity:0.0; }
</style>
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
        <div class="bs-callout bs-callout-warning" ng-show="errors.status === false" ng-cloak>
            <h4>Fix some errors</h4>

            <ul>
                <li ng-repeat="error in errors.error_image">{{ error }}</li>
                <li ng-if="errors.error_warning">{{ errors.error_warning }}</li>
            </ul>

            <?php if ( ! empty($warning)): ?>
                <p><?php echo $warning ?></p>
            <?php endif ?>
        </div>

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
			
            <form ng-submit="save($event)" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

                <div class="product-insert">
                    <input 
                        ng-model="insertProductName" 
                        ui-autocomplete="autocompleteSetting" 
                        type="text" 
                        name="product" 
                        value="" 
                        class="" 
                        placeholder="<?php echo $text_insert_text_placeholder ?>" 
                        id="insertProductName" 
                        ng-class="{'loading': inputLoading}" 
                        autofocus 
                    />
                </div>

                <table class="form">
                    <tr>
                        <td colspan="2">
                            <div id="flexible-product" class="sortable-product-list" ui-sortable="sortableSetting" ng-cloak>

                                <h3 class="text-center" ng-if="products.length != undefined && !products" ng-cloak>No products selected</h3>

                                <div 
                                    id="flexible-product{{ product.product_id }}" 
                                    class="flexible-product-item" 
                                    ng-repeat="product in products" 
                                    ng-class="{'change-product-view': product.is_editing}" 
                                >
                                    <div class="flexible-main-image">
                                        <img ng-src="{{ product.thumb }}">
                                    </div>

                                    <div class="flexible-main-name">{{ product.name }}</div>

                                    <div ng-click="remove(product.product_id)" class="remove-icon"><span class="glyphicon glyphicon-remove"></span></div>
                                    <div ng-click="edit(product)" class="edit-icon"><span class="glyphicon glyphicon-edit"></span></div>

                                    <input 
                                        type="text" 
                                        value="" 
                                        class="new-product-input" 
                                        name="changeproduct" 
                                        ng-model="product.insert_value" 
                                        ui-autocomplete="autocompleteEditSetting" 
                                        data-product-id="{{ product.id }}" 
                                    >
                                    <input type="hidden" class="product-id" value="{{ product.product_id }}" />
                                </div>

                            </div>
                            
                        <input type="text" class="hide-me" ng-model="selected_ids" name="products" id="selected-flexible-product-ids" value="" />
                        </td>
                    </tr>
                </table>

                <table id="module" class="list">
                  <thead>
                    <tr>
                      <td class="left"><?php echo $entry_limit; ?></td>
                      <td class="left"><?php echo $entry_image; ?></td>
                      <td class="left"><?php echo $entry_layout; ?></td>
                      <td class="left"><?php echo $entry_position; ?></td>
                      <td class="left"><?php echo $entry_status; ?></td>
                      <td class="right"><?php echo $entry_sort_order; ?></td>
                      <td></td>
                    </tr>
                  </thead>
                  <?php $module_row = 0; ?>
                  <?php foreach ($modules as $module) { ?>
                  <tbody id="module-row<?php echo $module_row; ?>">
                    <tr>
                      <td class="left"><input type="text" name="positions[<?php echo $module_row; ?>][limit]" value="<?php echo $module['limit']; ?>" size="1" /></td>
                      <td class="left <?php if (isset($error_image[$module_row])) echo 'has-error'; ?>"><input type="text" name="positions[<?php echo $module_row; ?>][image_width]" value="<?php echo $module['image_width']; ?>" size="3" />
                        <input type="text" name="positions[<?php echo $module_row; ?>][image_height]" value="<?php echo $module['image_height']; ?>" size="3" /></td>
                      <td class="left"><select name="positions[<?php echo $module_row; ?>][layout_id]">
                          <?php foreach ($layouts as $layout) { ?>
                          <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                          <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                          <?php } else { ?>
                          <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                          <?php } ?>
                          <?php } ?>
                        </select></td>
                      <td class="left"><select name="positions[<?php echo $module_row; ?>][position]">
                          <?php if ($module['position'] == 'content_top') { ?>
                          <option value="content_top" selected="selected"><?php echo $text_content_top; ?></option>
                          <?php } else { ?>
                          <option value="content_top"><?php echo $text_content_top; ?></option>
                          <?php } ?>
                          <?php if ($module['position'] == 'content_bottom') { ?>
                          <option value="content_bottom" selected="selected"><?php echo $text_content_bottom; ?></option>
                          <?php } else { ?>
                          <option value="content_bottom"><?php echo $text_content_bottom; ?></option>
                          <?php } ?>
                          <?php if ($module['position'] == 'column_left') { ?>
                          <option value="column_left" selected="selected"><?php echo $text_column_left; ?></option>
                          <?php } else { ?>
                          <option value="column_left"><?php echo $text_column_left; ?></option>
                          <?php } ?>
                          <?php if ($module['position'] == 'column_right') { ?>
                          <option value="column_right" selected="selected"><?php echo $text_column_right; ?></option>
                          <?php } else { ?>
                          <option value="column_right"><?php echo $text_column_right; ?></option>
                          <?php } ?>
                        </select></td>
                      <td class="left"><select name="positions[<?php echo $module_row; ?>][status]">
                          <?php if ($module['status']) { ?>
                          <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                          <option value="0"><?php echo $text_disabled; ?></option>
                          <?php } else { ?>
                          <option value="1"><?php echo $text_enabled; ?></option>
                          <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                          <?php } ?>
                        </select></td>
                      <td class="right"><input type="text" name="positions[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
                      <td class="left"><a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button"><?php echo $button_remove; ?></a></td>
                    </tr>
                  </tbody>
                  <?php $module_row++; ?>
                  <?php } ?>
                  <tfoot>
                    <tr>
                      <td colspan="6"></td>
                      <td class="left"><a onclick="addModule();" class="button"><?php echo $button_add_module; ?></a></td>
                    </tr>
                  </tfoot>
                </table>
            </form>

        </div>
    </div>

</div><!-- end #content -->


<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {  
    html  = '<tbody id="module-row' + module_row + '">';
    html += '  <tr>';
    html += '    <td class="left"><input type="text" name="positions[' + module_row + '][limit]" value="5" size="1" /></td>';
    html += '    <td class="left"><input type="text" name="positions[' + module_row + '][image_width]" value="80" size="3" /> <input type="text" name="positions[' + module_row + '][image_height]" value="80" size="3" /></td>';   
    html += '    <td class="left"><select name="positions[' + module_row + '][layout_id]">';
    <?php foreach ($layouts as $layout) { ?>
    html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
    <?php } ?>
    html += '    </select></td>';
    html += '    <td class="left"><select name="positions[' + module_row + '][position]">';
    html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
    html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
    html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
    html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
    html += '    </select></td>';
    html += '    <td class="left"><select name="positions[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
    html += '      <option value="0"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
    html += '    <td class="right"><input type="text" name="positions[' + module_row + '][sort_order]" value="" size="3" /></td>';
    html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
    html += '  </tr>';
    html += '</tbody>';
    
    $('#module tfoot').before(html);
    
    module_row++;
}
//--></script> 


<?php require DIR_APPLICATION . '/view/template/teil/teil-scripts.php' ?>
<script src="/admin/view/javascript/teil/bower_components/angular-ui-sortable/sortable.min.js"></script>
<script src="/admin/view/javascript/teil/bower_components/ui-autocomplete/autocomplete.js"></script>
<script src="/admin/view/javascript/teil/modules/FlexibleProducts/controller.js"></script>


<?php echo $footer ?>