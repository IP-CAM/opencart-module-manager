<?php echo $header; ?>


<!-- Styles -->
<link rel="stylesheet" type="text/css" href="/admin/view/stylesheet/teil/modules-list.css">
<link rel="stylesheet" href="/admin/view/javascript/teil/bower_components/sass-bootstrap-glyphicons/css/bootstrap-glyphicons.css">

<!-- Mignify popup -->
<link rel="stylesheet" href="/admin/view/javascript/teil/bower_components/magnific-popup/dist/magnific-popup.css">

<!-- Animate.css -->
<link rel="stylesheet" href="/admin/view/javascript/teil/bower_components/animate.css/animate.css">

<!-- Token -->
<input type="hidden" value="<?php echo $token ?>" id="token">
<input type="hidden" value="<?php echo $this->config->get('config_email') ?>" id="admin-email">
<input type="hidden" value="<?php echo DIR_TEIL_MODULES ?>" id="dir-teil-modules">
<input type="hidden" value="<?php echo $this->config->get('config_admin_language') ?>" id="admin-language">


<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_product; ?></td>
            <td><input type="text" name="product" value="" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><div id="featured-product">
                <?php $class = 'odd'; ?>
                <?php foreach ($products as $product) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div id="featured-product<?php echo $product['product_id']; ?>" class="<?php echo $class; ?>"><?php echo $product['name']; ?> <img src="view/image/delete.png" alt="" />
                  <input type="hidden" value="<?php echo $product['product_id']; ?>" />
                </div>
                <?php } ?>
              </div>
              <input type="hidden" name="featured_product" value="<?php echo $featured_product; ?>" /></td>
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
              <td class="left"><input type="text" name="featured_module[<?php echo $module_row; ?>][limit]" value="<?php echo $module['limit']; ?>" size="1" /></td>
              <td class="left"><input type="text" name="featured_module[<?php echo $module_row; ?>][image_width]" value="<?php echo $module['image_width']; ?>" size="3" />
                <input type="text" name="featured_module[<?php echo $module_row; ?>][image_height]" value="<?php echo $module['image_height']; ?>" size="3" />
                <?php if (isset($error_image[$module_row])) { ?>
                <span class="error"><?php echo $error_image[$module_row]; ?></span>
                <?php } ?></td>
              <td class="left"><select name="featured_module[<?php echo $module_row; ?>][layout_id]">
                  <?php foreach ($layouts as $layout) { ?>
                  <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                  <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
              <td class="left"><select name="featured_module[<?php echo $module_row; ?>][position]">
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
              <td class="left"><select name="featured_module[<?php echo $module_row; ?>][status]">
                  <?php if ($module['status']) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
              <td class="right"><input type="text" name="featured_module[<?php echo $module_row; ?>][sort_order]" value="<?php echo $module['sort_order']; ?>" size="3" /></td>
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
</div>
<script type="text/javascript"><!--
$('input[name=\'product\']').autocomplete({
	delay: 500,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.product_id
					}
				}));
			}
		});
	}, 
	select: function(event, ui) {
		$('#featured-product' + ui.item.value).remove();
		
		$('#featured-product').append('<div id="featured-product' + ui.item.value + '">' + ui.item.label + '<img src="view/image/delete.png" alt="" /><input type="hidden" value="' + ui.item.value + '" /></div>');

		$('#featured-product div:odd').attr('class', 'odd');
		$('#featured-product div:even').attr('class', 'even');
		
		data = $.map($('#featured-product input'), function(element){
			return $(element).attr('value');
		});
						
		$('input[name=\'featured_product\']').attr('value', data.join());
					
		return false;
	},
	focus: function(event, ui) {
      	return false;
   	}
});

$('#featured-product div img').live('click', function() {
	$(this).parent().remove();
	
	$('#featured-product div:odd').attr('class', 'odd');
	$('#featured-product div:even').attr('class', 'even');

	data = $.map($('#featured-product input'), function(element){
		return $(element).attr('value');
	});
					
	$('input[name=\'featured_product\']').attr('value', data.join());	
});
//--></script> 
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {	
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><input type="text" name="featured_module[' + module_row + '][limit]" value="5" size="1" /></td>';
	html += '    <td class="left"><input type="text" name="featured_module[' + module_row + '][image_width]" value="80" size="3" /> <input type="text" name="featured_module[' + module_row + '][image_height]" value="80" size="3" /></td>';	
	html += '    <td class="left"><select name="featured_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="featured_module[' + module_row + '][position]">';
	html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
	html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
	html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
	html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
	html += '    </select></td>';
	html += '    <td class="left"><select name="featured_module[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
    html += '      <option value="0"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
	html += '    <td class="right"><input type="text" name="featured_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
	html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#module tfoot').before(html);
	
	module_row++;
}
//--></script> 


<!-- Mignific popup -->
<script src="/admin/view/javascript/teil/bower_components/magnific-popup/dist/jquery.magnific-popup.min.js"></script>

<!-- Angular core -->
<script src="/admin/view/javascript/teil/bower_components/angular/angular.js"></script>
<script src="/admin/view/javascript/teil/bower_components/angular-route/angular-route.min.js"></script>
<script src="/admin/view/javascript/teil/bower_components/angular-cookies/angular-cookies.min.js"></script>
<script src="/admin/view/javascript/teil/bower_components/angular-translate/angular-translate.min.js"></script>
<script src="/admin/view/javascript/teil/bower_components/angular-animate/angular-animate.min.js"></script>

<!-- Init -->
<script src="/admin/view/javascript/teil/init.js"></script>

<!-- Localization -->
<script src="/admin/view/javascript/teil/localization/en.js"></script>
<script src="/admin/view/javascript/teil/localization/ru.js"></script>

<!-- Controllers -->
<script src="/admin/view/javascript/teil/controllers/CommonController.js"></script>
<script src="/admin/view/javascript/teil/controllers/SelfController.js"></script>

<!-- Directives -->
<script src="/admin/view/javascript/teil/directives/ng-enter.js"></script>
<script src="/admin/view/javascript/teil/directives/module-min-price.js"></script>
<script src="/admin/view/javascript/teil/directives/module-popup.js"></script>
<script src="/admin/view/javascript/teil/directives/module-thumb.js"></script>
<script src="/admin/view/javascript/teil/directives/btn-progress.js"></script>
<script src="/admin/view/javascript/teil/directives/capitalize.js"></script>
<script src="/admin/view/javascript/teil/directives/progress-block-btn.js"></script>

<!-- Services -->
<script src="/admin/view/javascript/teil/services/module-downloader.js"></script>
<script src="/admin/view/javascript/teil/services/module.js"></script>

<!-- Filters -->
<script src="/admin/view/javascript/teil/filters/date-format.js"></script>


<?php echo $footer; ?>