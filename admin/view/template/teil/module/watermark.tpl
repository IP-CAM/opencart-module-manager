<?php echo $header ?>



<div id="content">
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
            <h1><img alt="" src="view/image/module.png">Watermarks</h1>

            <div class="buttons">
                <a class="button" onclick="$('#form').submit();">Save</a>
                <a class="button" href="<?php echo $cancel; ?>">Cancel</a>
            </div>
        </div>

        <div class="content">
            <form action="<?php echo $action; ?>" enctype="multipart/form-data" id="form" method="post" name="form">
                <table class="list" id="module">
                	<tr>
                		<th></th>
                	</tr>
                </table>
            </form>
        </div>
    </div>
</div>


<?php require DIR_APPLICATION . '/view/template/teil/teil-scripts.php' ?>

<?php echo $footer ?>