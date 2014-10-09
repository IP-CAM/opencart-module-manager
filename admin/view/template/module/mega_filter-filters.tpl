<?php

	if( ! isset( $IDX ) )
		$IDX = 0;

?>

<div style="padding:5px">
	<input 
		type="checkbox" 
		name="<?php echo $_filtersName; ?>[based_on_category]"
		<?php echo ! empty( $_filtersValues['based_on_category'] ) ? ' checked="checked"' : ''; ?>
		style="vertical-align: middle; margin-top: -3px;" 
		id="<?php echo $IDX; ?>_data_based_on_category"
		value="1" /> <label for="<?php echo $IDX; ?>_data_based_on_category"><?php echo $text_display_based_on_category; ?></label>
</div>

<table class="table table-hover filters" id="<?php echo $IDX; ?>_mf-filters-table">
	<thead>
		<tr>
			<th class="text-center" width="300">
				<input type="checkbox" class="check-all" style="float:left;" /> <?php echo $text_filter_name; ?>
			</th>
			<th class="text-left"><?php echo $text_display_as_type; ?></th>
			<th class="text-center" width="140"><?php echo $text_collapsed_by_default; ?></th>
			<th class="text-center" width="145"><?php echo $text_display_list_of_items; ?></th>
			<th class="text-center" width="145"><?php echo $text_sort_order_values; ?></th>
			<th class="text-left" width="100"><?php echo $text_sort_order; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $filterItems as $item_id => $item ) { ?>
				<tr>
					<td style="width:290px">
						<input 
							type="checkbox" 
							name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][enabled]"
							<?php echo empty( $_filtersValues[(int)$item['filter_group_id']]['enabled'] ) ? '' : ' checked="checked"'; ?>
							style="vertical-align: middle; margin-top: 0;" 
							value="1"
							id="<?php echo $IDX; ?>_filters_data_group_<?php echo (int)$item['filter_group_id']; ?>"
							class="filter_enabled" /> <label for="<?php echo $IDX; ?>_filters_data_group_<?php echo (int)$item['filter_group_id']; ?>"><?php echo $item['name']; ?></label>
					</td>
					<td>
						<input 
							type="radio" 
							name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][type]" 
							value="checkbox" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo empty( $_filtersValues[(int)$item['filter_group_id']]['type'] ) || $_filtersValues[(int)$item['filter_group_id']]['type'] == 'checkbox' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_filters_type_<?php echo (int)$item['filter_group_id']; ?>_a" /> <label style="padding-right: 15px" for="<?php echo $IDX; ?>_filters_type_<?php echo (int)$item['filter_group_id']; ?>_a"><?php echo $text_type_checkbox; ?></label>
						
						<input 
							type="radio" 
							name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][type]" 
							value="radio" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo ! empty( $_filtersValues[(int)$item['filter_group_id']]['type'] ) && $_filtersValues[(int)$item['filter_group_id']]['type'] == 'radio' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_filters_type_<?php echo (int)$item['filter_group_id']; ?>_b" /> <label style="padding-right: 15px" for="<?php echo $IDX; ?>_filters_type_<?php echo (int)$item['filter_group_id']; ?>_b"><?php echo $text_type_radio; ?></label>
									
						<input 
							type="radio" 
							name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][type]" 
							value="select" 
							style="vertical-align: middle; margin-top: 0;" 
							<?php echo ! empty( $_filtersValues[(int)$item['filter_group_id']]['type'] ) && $_filtersValues[(int)$item['filter_group_id']]['type'] == 'select' ? ' checked="checked" data-checked="checked"' : ''; ?>
							id="<?php echo $IDX; ?>_filters_type_<?php echo (int)$item['filter_group_id']; ?>_c" /> <label for="<?php echo $IDX; ?>_filters_type_<?php echo (int)$item['filter_group_id']; ?>_c"><?php echo $text_type_select; ?></label>	
						<br />
						<select name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][display_live_filter]">
							<option value=""<?php echo empty( $_filtersValues[(int)$item['filter_group_id']]['display_live_filter'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> <?php echo $text_by_default; ?></option>
							<option value="1"<?php echo ! empty( $_filtersValues[(int)$item['filter_group_id']]['display_live_filter'] ) && $_filtersValues[(int)$item['filter_group_id']]['display_live_filter'] == '1' ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> - <?php echo $text_yes; ?></option>
							<option value="-1"<?php echo ! empty( $_filtersValues[(int)$item['filter_group_id']]['display_live_filter'] ) && $_filtersValues[(int)$item['filter_group_id']]['display_live_filter'] == '-1' ? ' selected="selected"' : ''; ?>><?php echo $text_display_live_filter; ?> - <?php echo $text_no; ?></option>
						</select>
					</td>
					<td style="width:140px; text-align: center;">
						<input type="checkbox" name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][collapsed]" value="1" <?php echo empty( $_filtersValues[(int)$item['filter_group_id']]['collapsed'] ) ? '' : ' checked="checked"'; ?> />
					</td>
					<td style="width:140px; text-align: center;">
						<select name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][display_list_of_items]">
							<option value=""<?php echo empty( $_filtersValues[(int)$item['filter_group_id']]['display_list_of_items'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_by_default; ?></option>
							<option value="scroll"<?php echo ! empty( $_filtersValues[(int)$item['filter_group_id']]['display_list_of_items'] ) && $_filtersValues[(int)$item['filter_group_id']]['display_list_of_items'] == 'scroll' ? ' selected="selected"' : ''; ?>><?php echo $text_with_scroll; ?></option>
							<option value="button_more"<?php echo ! empty( $_filtersValues[(int)$item['filter_group_id']]['display_list_of_items'] ) && $_filtersValues[(int)$item['filter_group_id']]['display_list_of_items'] == 'button_more' ? ' selected="selected"' : ''; ?>><?php echo $text_with_button_more; ?></option>
						</select>
					</td>
					<td style="width:140px; text-align: center;">
						<select name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][sort_order_values]">
							<option value=""<?php echo empty( $_filtersValues[(int)$item['filter_group_id']]['sort_order_values'] ) ? ' selected="selected"' : ''; ?>><?php echo $text_by_default; ?></option>
										
							<?php
										
								$sortOrderValues = array(
									'string_asc'	=> $text_string_asc,
									'string_desc'	=> $text_string_desc,
									'numeric_asc'	=> $text_numeric_asc,
									'numeric_desc'	=> $text_numeric_desc
								);
											
								foreach( $sortOrderValues as $k => $v ) {
									echo '<option value="' . $k . '"';
												
									if( ! empty( $_filtersValues[(int)$item['filter_group_id']]['sort_order_values'] ) && $_filtersValues[(int)$item['filter_group_id']]['sort_order_values'] == $k ) {
										echo ' selected="selected"';
									}
												
									echo '>' . $v . '</option>';
								}
										
							?>
						</select>
					</td>
					<td style="width:90px; text-align: center;">
						<input type="text" name="<?php echo $_filtersName; ?>[<?php echo (int)$item['filter_group_id']; ?>][sort_order]" value="<?php echo ! isset( $_filtersValues[(int)$item['filter_group_id']]['sort_order'] ) ? '' : $_filtersValues[(int)$item['filter_group_id']]['sort_order']; ?>" size="3" />
					</td>
				</tr>
		<?php } ?>
	</tbody>
</table>

<script type="text/javascript">		
	jQuery('#<?php echo $IDX; ?>_mf-filters-table input[type=checkbox][class=check-all]').change(function(){
		var s = jQuery(this).is(':checked');
		
		jQuery('#<?php echo $IDX; ?>_mf-filters-table input[type=checkbox].filter_enabled').attr('checked',s).trigger('change');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-filters-table input[type=checkbox].filter_enabled').each(function(){
		var $parent = jQuery(this).parent().parent();
		
		jQuery(this).bind('change.mf', function(){
			if( jQuery(this).is(':checked') && ! $parent.find('input[type=radio][name^=mega_filter_module]:checked').length ) {
				$parent.find('input[type=radio][name^=mega_filter_module][data-checked]').attr('checked', true).trigger('change');
			} else if( ! jQuery(this).is(':checked') ) {
				$parent.find('input[type=radio][name^=mega_filter_module]').attr('checked', false).trigger('change');
			}
		}).trigger('change.mf');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-filters-table').find('input[type=checkbox][id],input[type=radio][id]').bind('change.bold',function(){
		var s		= jQuery(this).is(':checked'),
			id		= jQuery(this).attr('id'),
			type	= jQuery(this).attr('type');
			
		if( type.toLowerCase() == 'radio' )
			jQuery(this).parent().find('label').removeAttr('style');
		
		jQuery('label[for="' + id + '"]').css('font-weight',s?'bold':'normal');
	});
	
	jQuery('#<?php echo $IDX; ?>_mf-filters-table').find('input[type=checkbox][id]:checked,input[type=radio][id]:checked').trigger('change.bold');
</script>