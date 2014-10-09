<?php

	if( ! isset( $IDX ) )
		$IDX = 0;

?>

<table class="table table-hover attributes" id="<?php echo $IDX; ?>_mf-attribs-table">
	<thead>
		<tr>
			<th class="text-center" width="250">
				<input type="checkbox" class="check-all" style="float:left" />
				<?php echo $text_group; ?>
			</th>
			<th width="60" class="text-center">
				<?php echo $text_sort_order; ?>
			</th>
			<th class="text-left"><?php echo $text_attribute_name; ?></th>
			<th class="text-center" width="100"><?php echo $text_display_as_type; ?></td>
			<th class="text-center" width="100"><?php echo $text_collapsed_by_default; ?></th>
			<th class="text-center" width="145"><?php echo $text_display_list_of_items; ?></th>
			<th class="text-center" width="100"><?php echo $text_sort_order_values; ?></th>
			<th class="text-center" width="70"><?php echo $text_sort_order; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $items as $item_group_id => $item ) { ?>
			<?php foreach( $item['childs'] as $childKey => $child ) { ?>
				<tr>
					<?php if( ! $childKey ) { ?>
						<td class="vertical-middle" rowspan="<?php echo count( $item['childs'] ); ?>">
							<input type="checkbox" data-group="<?php echo $item_group_id; ?>" style="vertical-align: middle; margin-top: 0;" id="<?php echo $IDX; ?>_data_group_<?php echo $item_group_id; ?>" /> <label for="<?php echo $IDX; ?>_data_group_<?php echo $item_group_id; ?>"><?php echo $item['name']; ?></label>
						</td>
						<td class="vertical-middle text-center" rowspan="<?php echo count( $item['childs'] ); ?>">
							<input type="text" name="<?php echo $_attribsName; ?>[<?php echo $item_group_id; ?>][sort_order]" value="<?php echo ! isset( $_attribsValues[$item_group_id]['sort_order'] ) ? '' : $_attribsValues[$item_group_id]['sort_order']; ?>" size="3" />
						</td>
					<?php } ?>
					<td class="vertical-middle">
						<input 
							type="checkbox" 
							name="<?php echo $_attribsName; ?>[<?php echo $child['attribute_group_id']; ?>][items][<?php echo $child['attribute_id']; ?>][enabled]"
							data-group-child="<?php echo $child['attribute_group_id']; ?>" 
							<?php echo empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['enabled'] ) ? '' : ' checked="checked"'; ?>
							style="vertical-align: middle; margin-top: 0;" 
							value="1"
							id="<?php echo $IDX; ?>_attribs_data_group_child_<?php echo $child['attribute_id']; ?>"
							class="attribs_enabled" /> <label for="<?php echo $IDX; ?>_attribs_data_group_child_<?php echo $child['attribute_id']; ?>"><?php echo $child['name']; ?></label>
					</td>
					<td>
						<?php $tmpTypes = array( 'checkbox', 'radio', 'select', 'image', 'image_list_radio', 'image_list_checkbox' ); ?>
						<select name="<?php echo $_attribsName; ?>[<?php echo $child['attribute_group_id']; ?>][items][<?php echo $child['attribute_id']; ?>][type]">
							<?php $idxx = 0; foreach( $tmpTypes as $tmpType ) { ?>
								<option value="<?php echo $tmpType; ?>"<?php echo ( empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']] ) && ! $idxx ) || ( ! empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['type'] ) && $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['type'] == $tmpType ) ? ' selected="selected"' : ''; ?>>
									<?php echo ${'text_type_' . $tmpType}; ?>
								</option>
							<?php $idxx++; } ?>
						</select>
						<br />
						<select name="<?php echo $_attribsName; ?>[<?php echo $child['attribute_group_id']; ?>][items][<?php echo $child['attribute_id']; ?>][display_live_filter]">
							<option value=""<?php echo empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_live_filter'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> <?php echo $text_by_default; ?></option>
							<option value="1"<?php echo ! empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_live_filter'] ) && $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_live_filter'] == '1' ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> - <?php echo $text_yes; ?></option>
							<option value="-1"<?php echo ! empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_live_filter'] ) && $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_live_filter'] == '-1' ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> - <?php echo $text_no; ?></option>
						</select>
					</td>
					<td class="text-center">
						<input type="checkbox" name="<?php echo $_attribsName; ?>[<?php echo $child['attribute_group_id']; ?>][items][<?php echo $child['attribute_id']; ?>][collapsed]" value="1" <?php echo empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['collapsed'] ) ? '' : ' checked="checked"'; ?> />
					</td>
					<td class="text-center">
						<select name="<?php echo $_attribsName; ?>[<?php echo $child['attribute_group_id']; ?>][items][<?php echo $child['attribute_id']; ?>][display_list_of_items]">
							<option value=""<?php echo empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_list_of_items'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_by_default; ?></option>
							<option value="scroll"<?php echo ! empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_list_of_items'] ) && $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_list_of_items'] == 'scroll' ? ' selected="selected"' : ''; ?>><?php echo $text_with_scroll; ?></option>
							<option value="button_more"<?php echo ! empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_list_of_items'] ) && $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['display_list_of_items'] == 'button_more' ? ' selected="selected"' : ''; ?>><?php echo $text_with_button_more; ?></option>
						</select>
					</td>
					<td class="text-center">
						<select name="<?php echo $_attribsName; ?>[<?php echo $child['attribute_group_id']; ?>][items][<?php echo $child['attribute_id']; ?>][sort_order_values]">
							<option value=""<?php echo empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['sort_order_values'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_by_default; ?></option>
										
							<?php
									
								$sortOrderValues = array(
									'string_asc'	=> $text_string_asc,
									'string_desc'	=> $text_string_desc,
									'numeric_asc'	=> $text_numeric_asc,
									'numeric_desc'	=> $text_numeric_desc
								);
											
								foreach( $sortOrderValues as $k => $v ) {
									echo '<option value="' . $k . '"';
												
									if( ! empty( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['sort_order_values'] ) && $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['sort_order_values'] == $k ) {
										echo ' selected="selected"';
									}
												
									echo '>' . $v . '</option>';
								}
									
							?>
						</select>
					</td>
					<td class="text-center">
						<input type="text" name="<?php echo $_attribsName; ?>[<?php echo $child['attribute_group_id']; ?>][items][<?php echo $child['attribute_id']; ?>][sort_order]" value="<?php echo ! isset( $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['sort_order'] ) ? '' : $_attribsValues[$child['attribute_group_id']]['items'][$child['attribute_id']]['sort_order']; ?>" size="3" />
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<script type="text/javascript">		
	jQuery('#<?php echo $IDX; ?>_mf-attribs-table input[type=checkbox][class=check-all]').change(function(){
		var s = jQuery(this).is(':checked');
		
		jQuery('#<?php echo $IDX; ?>_mf-attribs-table input[type=checkbox][data-group]').attr('checked',s).trigger('change');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-attribs-table input[type=checkbox][data-group]').change(function(){
		var s = jQuery(this).is(':checked'),
			group = jQuery(this).attr('data-group');
	
		jQuery('#<?php echo $IDX; ?>_mf-attribs-table input[data-group-child="' + group + '"]').attr('checked',s).trigger('change');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-attribs-table').find('input[type=checkbox][id],input[type=radio][id]').bind('change.bold',function(){
		var s		= jQuery(this).is(':checked'),
			id		= jQuery(this).attr('id'),
			type	= jQuery(this).attr('type');
			
		if( type.toLowerCase() == 'radio' )
			jQuery(this).parent().find('label').removeAttr('style');
		
		jQuery('label[for="' + id + '"]').css('font-weight',s?'bold':'normal');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-attribs-table').find('input[type=checkbox][id]:checked,input[type=radio][id]:checked').trigger('change.bold');
</script>