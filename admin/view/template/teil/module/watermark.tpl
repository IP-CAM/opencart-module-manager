<?php echo $header ?>

<?php require DIR_APPLICATION . '/view/template/teil/teil-head.php' ?>
<link rel="stylesheet" type="text/css" href="/admin/view/stylesheet/teil/modules/watermark/style.css">


<div id="content" class="watermark" ng-app="teil">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        	<?php echo $breadcrumb['separator']; ?>
    		<a href="<?php echo $breadcrumb['href']; ?>">
    			<?php echo $breadcrumb['text']; ?>
    		</a>
        <?php } ?>
    </div>

    <div class="box">
        <div class="heading">
            <h1><img alt="" src="view/image/module.png"><?php echo $text_title ?></h1>

            <div class="buttons">
                <a class="button" onclick="$('#form').submit();"><?php echo $text_save ?></a>
                <a class="button" href="<?php echo $cancel; ?>"><?php echo $text_cancel ?></a>
            </div>
        </div>

        <div class="content" ng-controller="WatermarkController">
			
        	<div class="half">
				<div class="pult-container">
					<table class="form">
	        			<tr>
	        				<td><?php echo $text_insert_image ?>:</td>
	        				<td>
	        					<div class="image"><img ng-src="{{ watermarkEditorUrl }}" alt="" id="thumb-logo" />
								<input type="hidden" name="config_logo" id="logo" value="{{ watermarkUrl }}" />
								<br />
								<a onclick="image_upload('logo', 'thumb-logo');"><?php echo $text_new_image ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb-logo').attr('src', '<?php echo $no_image; ?>'); $('#logo').attr('value', ''); $('#watermark-image-url').val('<?php echo $no_image; ?>').trigger('change');"><?php echo $text_clear_image ?></a></div>
	        				</td>
	        			</tr>

	        			<tr>
	        				<td><?php echo $text_image_width ?></td>
	        				<td><input type="text" value="" ng-model="image.width" placeholder="auto"></td>
	        			</tr>

	        			<tr>
	        				<td><?php echo $text_image_height ?></td>
	        				<td><input type="text" value="" ng-model="image.height" placeholder="auto"></td>
	        			</tr>
	        		</table>

					<h3><?php echo $text_offset_controll ?>:</h3>
					<select ng-options="item.value for item in watermarkPositions" ng-model="selectedPotision"></select>

		        	<div id="controll-pult">
			        	<div class="box-top">
			        		<label><?php echo $text_top ?></label>
		        			<input type="number" value="" ng-model="offset.top">
			        	</div>

			        	<div class="box-right">
			        		<label><?php echo $text_right ?></label>
		        			<input type="number" value="" ng-model="offset.right">
			        	</div>

			        	<div class="box-bottom">
		        			<input type="number" value="" ng-model="offset.bottom">
			        		<label><?php echo $text_bottom ?></label>
			        	</div>

			        	<div class="box-left">
			        		<label><?php echo $text_left ?></label>
		        			<input type="number" value="" ng-model="offset.left">
			        	</div>
		        	</div><!-- end #controll-pult -->
				</div>
        	</div>

        	<button ng-click="save()">save</button>

        	<div class="half">
        		<h3><?php echo $text_live_preview ?>:</h3>

	        	<div class="watermark-image-preview" id="watermark-image-preview">
					<img 
						id="preview-image" 
						ng-src="{{ watermarkEditorUrl }}" 
						class="{{ selectedPotision.key }}" 
						style="
							width: {{ image.width }}px;
							height: {{ image.height }}px;

							top: {{ offset.top }}{{ offset.measurement }};
							bottom: {{ offset.bottom }}{{ offset.measurement }};
							left: {{ offset.left }}{{ offset.measurement }};
							right: {{ offset.right }}{{ offset.measurement }};
						">

	        	</div>
        	</div>

			<!-- Hidden -->
			<input type="text" id="watermark-image-url" ng-model="watermarkEditorUrl" class="hide-me">


        </div>
    </div>

</div><!-- end #content -->


<?php require DIR_APPLICATION . '/view/template/teil/teil-scripts.php' ?>
<script src="/admin/view/javascript/teil/modules/watermark/controller.js"></script>


<script type="text/javascript"><!--
function image_upload(field, thumb) {
	$('#dialog').remove();
	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	
	$('#dialog').dialog({
		title: 'Image manager',
		close: function (event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).val()),
					dataType: 'text',
					success: function(data) {
						$('#' + thumb).replaceWith('<img src="' + data + '" alt="" id="' + thumb + '" />');
						$('#watermark-image-url').val(data).trigger('change');
					}
				});
			}
		},	
		bgiframe: false,
		width: 800,
		height: 400,
		resizable: false,
		modal: false
	});
};
//--></script> 

<?php echo $footer ?>