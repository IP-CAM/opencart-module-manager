<?php echo $header ?>

<div id="container">
	<div class="container">
		<h1>Hello world ;)</h1>

		<?php echo Menu::test() ?>
	</div>

	<div id="content">
		<div id="tabs" class="htabs">
			<a href="#tab-general" class="selected" style="display: inline;">General</a>
			<a href="#tab-standart" style="display: inline;">Standart</a>
			<a href="#tab-pro" style="display: inline;">Pro</a>
		</div>

		<div id="tab-general">
			<h3>General info</h3>

			<table class="form">
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
			</table>

			<div class="buttons">
				<a class="button">Save</a>
				<a class="button">Cancel</a>
			</div>
		</div><!-- end #tab-general -->

		<div id="tab-standart">
			<h3>Standart info</h3>

			<table class="form">
				<tr>
					<td>Radio select</td>
					<td>
						<label>
							<input type="radio">
							Radio 1
						</label>
						<label>
							<input type="radio">
							Radio 2
						</label>
						<label>
							<input type="radio">
							Radio 3
						</label>
					</td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
			</table>

			<div class="buttons">
				<a class="button">Save</a>
				<a class="button">Cancel</a>
			</div>
		</div><!-- end #tab-standart -->

		<div id="tab-pro">
			<h3>Pro version</h3>

			<table class="form">
				<tr>
					<td>Radio select</td>
					<td>
						<label>
							<input type="checkbox">
							Checkbox 1
						</label>
						<label>
							<input type="checkbox">
							Checkbox 2
						</label>
						<label>
							<input type="checkbox">
							Checkbox 3
						</label>
					</td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
				<tr>
					<td>Input 1</td>
					<td><input type="text"></td>
				</tr>
			</table>

			<div class="buttons">
				<a class="button">Save</a>
				<a class="button">Cancel</a>
			</div>
		</div><!-- end #tab-pro -->
	</div>

</div>


<script type="text/javascript">
	$('#tabs a').tabs(); 
</script>

<?php echo $footer ?>