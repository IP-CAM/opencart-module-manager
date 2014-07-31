<?php echo $header ?>

<?php require DIR_APPLICATION . '/view/template/teil/teil-head.php' ?>
<link rel="stylesheet" type="text/css" href="/admin/view/stylesheet/teil/modules/module_name/style.css">


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
            <h1><img alt="" src="view/image/module.png"><?php echo $heading_title ?></h1>

            <div class="buttons">
                <a class="button" onclick="$('#form').submit();">Save</a>
                <a class="button" href="<?php echo $cancel; ?>">Cancel</a>
            </div>
        </div>

        <div class="content" ng-controller="ModuleName" ng-init="init()">
			
			<table class="form">
    			<tr>
    				<td>Test:</td>
    				<td>Demo</td>
    			</tr>
    		</table>

        </div>
    </div>

</div><!-- end #content -->


<?php require DIR_APPLICATION . '/view/template/teil/teil-scripts.php' ?>
<script src="/admin/view/javascript/teil/modules/module_name/controller.js"></script>


<?php echo $footer ?>