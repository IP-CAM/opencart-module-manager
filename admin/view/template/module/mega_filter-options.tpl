<?php

	if( ! isset( $IDX ) )
		$IDX = 0;

?>

<table class="table table-hover options" id="<?php echo $IDX; ?>_mf-options-table">
	<thead>
		<tr>
			<th class="text-center" width="300">
				<input type="checkbox" class="check-all" style="float:left;" /> <?php echo $text_option_name; ?>
			</th>
			<th class="text-left"><?php echo $text_type; ?></th>
			<th class="text-left"><?php echo $text_display_as_type; ?></th>
			<th class="text-center" width="140"><?php echo $text_collapsed_by_default; ?></th>
			<th class="text-center" width="145"><?php echo $text_display_list_of_items; ?></th>
			<th class="text-center" width="145"><?php echo $text_sort_order_values; ?></th>
			<th class="text-left" width="100"><?php echo $text_sort_order; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $optionItems as $item_id => $item ) { ?>
				<tr>
					<td style="width:290px">
						<input 
							type="checkbox" 
							name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][enabled]"
							<?php echo empty( $_optionsValues[$item['option_id']]['enabled'] ) ? '' : ' checked="checked"'; ?>
							style="vertical-align: middle; margin-top: 0;" 
							value="1"
							id="<?php echo $IDX; ?>_options_data_group_<?php echo $item['option_id']; ?>"
							class="option_enabled" /> <label for="<?php echo $IDX; ?>_options_data_group_<?php echo $item['option_id']; ?>"><?php echo $item['name']; ?></label>
					</td>
					<td>
						<?php echo $item['type']; ?>
					</td>
					<td>
						<input 
							type="radio" 
							name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][type]" 
							value="checkbox" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo empty( $_optionsValues[$item['option_id']]['type'] ) || $_optionsValues[$item['option_id']]['type'] == 'checkbox' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_a" /> <label style="padding-right: 15px" for="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_a"><?php echo $text_type_checkbox; ?></label>
						
						<input 
							type="radio" 
							name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][type]" 
							value="radio" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo ! empty( $_optionsValues[$item['option_id']]['type'] ) && $_optionsValues[$item['option_id']]['type'] == 'radio' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_b" /> <label style="padding-right: 15px" for="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_b"><?php echo $text_type_radio; ?></label>
									
						<input 
							type="radio" 
							name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][type]" 
							value="select" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo ! empty( $_optionsValues[$item['option_id']]['type'] ) && $_optionsValues[$item['option_id']]['type'] == 'select' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_c" /> <label for="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_c"><?php echo $text_type_select; ?></label>	
						<br />
						<input 
							type="radio" 
							name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][type]" 
							value="image" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo ! empty( $_optionsValues[$item['option_id']]['type'] ) && $_optionsValues[$item['option_id']]['type'] == 'image' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_d" /> <label for="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_d"><?php echo $text_type_image; ?></label>
						
						<input 
							type="radio" 
							name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][type]" 
							value="image_list_radio" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo ! empty( $_optionsValues[$item['option_id']]['type'] ) && $_optionsValues[$item['option_id']]['type'] == 'image_list_radio' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_e" /> <label for="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_e"><?php echo $text_type_image_list_radio; ?></label>
						
						<input 
							type="radio" 
							name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][type]" 
							value="image_list_checkbox" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo ! empty( $_optionsValues[$item['option_id']]['type'] ) && $_optionsValues[$item['option_id']]['type'] == 'image_list_checkbox' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_f" /> <label for="<?php echo $IDX; ?>_options_type_<?php echo $item['option_id']; ?>_f"><?php echo $text_type_image_list_checkbox; ?></label>
						<br />
						<select name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][display_live_filter]">
							<option value=""<?php echo empty( $_optionsValues[$item['option_id']]['display_live_filter'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> <?php echo $text_by_default; ?></option>
							<option value="1"<?php echo ! empty( $_optionsValues[$item['option_id']]['display_live_filter'] ) && $_optionsValues[$item['option_id']]['display_live_filter'] == '1' ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> - <?php echo $text_yes; ?></option>
							<option value="-1"<?php echo ! empty( $_optionsValues[$item['option_id']]['display_live_filter'] ) && $_optionsValues[$item['option_id']]['display_live_filter'] == '-1' ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> - <?php echo $text_no; ?></option>
						</select>
					</td>
					<td style="width:140px; text-align: center;">
						<input type="checkbox" name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][collapsed]" value="1" <?php echo empty( $_optionsValues[$item['option_id']]['collapsed'] ) ? '' : ' checked="checked"'; ?> />
					</td>
					<td style="width:140px; text-align: center;">
						<select name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][display_list_of_items]">
							<option value=""<?php echo empty( $_optionsValues[$item['option_id']]['display_list_of_items'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_by_default; ?></option>
							<option value="scroll"<?php echo ! empty( $_optionsValues[$item['option_id']]['display_list_of_items'] ) && $_optionsValues[$item['option_id']]['display_list_of_items'] == 'scroll' ? ' selected="selected"' : ''; ?>><?php echo $text_with_scroll; ?></option>
							<option value="button_more"<?php echo ! empty( $_optionsValues[$item['option_id']]['display_list_of_items'] ) && $_optionsValues[$item['option_id']]['display_list_of_items'] == 'button_more' ? ' selected="selected"' : ''; ?>><?php echo $text_with_button_more; ?></option>
						</select>
					</td>
					<td style="width:140px; text-align: center;">
						<select name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][sort_order_values]">
							<option value=""<?php echo empty( $_optionsValues[$item['option_id']]['sort_order_values'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_by_default; ?></option>
										
							<?php
										
								$sortOrderValues = array(
									'string_asc'	=> $text_string_asc,
									'string_desc'	=> $text_string_desc,
									'numeric_asc'	=> $text_numeric_asc,
									'numeric_desc'	=> $text_numeric_desc
								);
											
								foreach( $sortOrderValues as $k => $v ) {
									echo '<option value="' . $k . '"';
												
									if( ! empty( $_optionsValues[$item['option_id']]['sort_order_values'] ) && $_optionsValues[$item['option_id']]['sort_order_values'] == $k ) {
										echo ' selected="selected"';
									}
												
									echo '>' . $v . '</option>';
								}
										
							?>
						</select>
					</td>
					<td style="width:90px; text-align: center;">
						<input type="text" name="<?php echo $_optionsName; ?>[<?php echo $item['option_id']; ?>][sort_order]" value="<?php echo ! isset( $_optionsValues[$item['option_id']]['sort_order'] ) ? '' : $_optionsValues[$item['option_id']]['sort_order']; ?>" size="3" />
					</td>
				</tr>
		<?php } ?>
	</tbody>
</table>

<script type="text/javascript">		
	jQuery('#<?php echo $IDX; ?>_mf-options-table input[type=checkbox][class=check-all]').change(function(){
		var s = jQuery(this).is(':checked');
		
		jQuery('#<?php echo $IDX; ?>_mf-options-table input[type=checkbox].option_enabled').attr('checked',s).trigger('change');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-options-table input[type=checkbox].option_enabled').each(function(){
		var $parent = jQuery(this).parent().parent();
		
		jQuery(this).bind('change.mf', function(){
			if( jQuery(this).is(':checked') && ! $parent.find('input[type=radio][name^=mega_filter_module]:checked').length ) {
				$parent.find('input[type=radio][name^=mega_filter_module][data-checked]').attr('checked', true).trigger('change');
			} else if( ! jQuery(this).is(':checked') ) {
				$parent.find('input[type=radio][name^=mega_filter_module]').attr('checked', false).trigger('change');
			}
		}).trigger('change.mf');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-options-table').find('input[type=checkbox][id],input[type=radio][id]').bind('change.bold',function(){
		var s		= jQuery(this).is(':checked'),
			id		= jQuery(this).attr('id'),
			type	= jQuery(this).attr('type');
			
		if( type.toLowerCase() == 'radio' )
			jQuery(this).parent().find('label').removeAttr('style');
		
		jQuery('label[for="' + id + '"]').css('font-weight',s?'bold':'normal');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-options-table').find('input[type=checkbox][id]:checked,input[type=radio][id]:checked').trigger('change.bold');
</script>