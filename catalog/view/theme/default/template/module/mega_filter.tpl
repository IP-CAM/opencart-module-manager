<style type="text/css">
	.mfilter-counter {
		background: #<?php echo empty( $settings['background_color_counter'] ) ? '428BCA' : trim( $settings['background_color_counter'], '#' ); ?>;
		color: #<?php echo empty( $settings['text_color_counter'] ) ? 'FFFFFF' : trim( $settings['text_color_counter'], '#' ); ?>;
	}
	.mfilter-counter:after {
		border-right-color: #<?php echo empty( $settings['background_color_counter'] ) ? '428BCA' : trim( $settings['background_color_counter'], '#' ); ?>;
	}
</style>

<?php if( ! empty( $settings['css_style'] ) ) { ?>
	<style type="text/css">
		<?php echo htmlspecialchars_decode( $settings['css_style'] ); ?>
	</style>
<?php } ?>

<?php if( ! empty( $settings['javascript'] ) ) { ?>
	<script type="text/javascript">
		<?php echo htmlspecialchars_decode( $settings['javascript'] ); ?>
	</script>
<?php } ?>
	
<?php if( ! empty( $settings['background_color_search_button'] ) ) { ?>
	<style type="text/css">
		.mfilter-search #mfilter-opts-search_button {
			background-color: #<?php echo trim( $settings['background_color_search_button'], '#' ); ?>;
		}
	</style>
<?php } ?>
	
<?php if( ! empty( $settings['background_color_slider'] ) ) { ?>
	<style type="text/css">
		#mfilter-price-slider .ui-slider-range {
			background: #<?php echo trim( $settings['background_color_slider'], '#' ); ?>;
		}
	</style>
<?php } ?>

<?php

	$button_template = '<div class="mfilter-button mfilter-button-%s">%s</div>';
	$button_temp = '<a href="#" class="%s">%s</a>';
	$buttons = array( 'top' => array(), 'bottom' => array() );
	
	if( ! empty( $settings['show_reset_button'] ) ) {
		$buttons['bottom'][] = sprintf( $button_temp, 'mfilter-button-reset', '<i class="mfilter-reset-icon"></i>' . $text_reset_all );
	}

	if( ! empty( $settings['refresh_results'] ) && $settings['refresh_results'] == 'using_button' && ! empty( $settings['place_button'] ) ) {
		$place_button = explode( '_', $settings['place_button'] );
	
		if( in_array( 'top', $place_button ) ) {
			$buttons['top'][] = sprintf( $button_temp, 'button', $text_button_apply );
		}
		
		if( in_array( 'bottom', $place_button ) ) {
			$buttons['bottom'][] = sprintf( $button_temp, 'button', $text_button_apply );
		}
	}
	
	foreach( $buttons as $bKey => $bVal ) {	
		$buttons[$bKey] = $bVal ? sprintf( $button_template, $bKey, implode( '', $bVal ) ) : '';
	}
	
?>

<div class="box mfilter-box mfilter-<?php echo $_position; ?>">
	<?php if( $heading_title ) { ?>
		<div class="box-heading"><?php echo $heading_title; ?></div>
	<?php } ?>
	<div class="box-content mfilter-content<?php echo empty( $settings['show_number_of_products'] ) ? ' mfilter-hide-counter' : ''; ?>">
		<?php echo $buttons['top']; ?>
		<ul>
			<?php foreach( $filters as $kfilter => $filter ) { ?>
				<?php
				
					$base_type = empty( $filter['base_type'] ) ? $filter['type'] : $filter['base_type'];
					$base_id = empty( $filter['id'] ) ? '' : $filter['id'];
				
				?>
				<li
					data-type="<?php echo $filter['type']; ?>"
					data-base-type="<?php echo $base_type; ?>"
					data-id="<?php echo $base_id; ?>"
					data-seo-name="<?php echo $filter['seo_name']; ?>"
					<?php if( isset( $filter['auto_levels'] ) ) { ?>
						data-auto-levels="<?php echo $filter['auto_levels']; ?>"
					<?php } ?>
					data-display-live-filter="<?php 
						$display_live_filter = ! empty( $settings['display_live_filter']['enabled'] ) ? '1' : '-1';
					
						if( ! empty( $filter['display_live_filter'] ) ) {
							$display_live_filter = $filter['display_live_filter'];
						} 
						
						if( $display_live_filter == '1' && ! empty( $settings['display_live_filter']['items'] ) ) {
							echo $settings['display_live_filter']['items'];
						} else {
							echo 0;
						}
					?>"
					data-display-list-of-items="<?php echo empty( $filter['display_list_of_items'] ) ? '' : $filter['display_list_of_items']; ?>"
					class="mfilter-filter-item mfilter-<?php echo $filter['type']; ?> mfilter-<?php echo $base_type; ?><?php echo $base_type == 'attribute' ? ' mfilter-attributes' : ( $base_type == 'option' ? ' mfilter-options' : ( $base_type == 'filter' ? ' mfilter-filters' : '' ) ); ?>"
					>
					
					<?php if( $filter['collapsed'] != 'hide_header' && ! empty( $filter['name'] ) ) { ?>
						<div class="mfilter-heading<?php echo $filter['collapsed'] && $filter['collapsed'] == '1' ? ' mfilter-collapsed' : ''; ?>">
							<div class="mfilter-heading-content">
								<div class="mfilter-heading-text"><span><?php echo $filter['name']; ?></span></div>
								<i class="mfilter-head-icon"></i>
							</div>
						</div>
					<?php } ?>
					
					<div class="mfilter-content-opts"<?php echo $filter['collapsed'] && $filter['collapsed'] != 'hide_header' && $_position != 'content_top' ? ' style="display:none"' : ''; ?>>
						<div class="mfilter-opts-container">
							<div class="mfilter-content-wrapper">
								<div class="mfilter-options">
									<?php if( $base_type == 'categories' ) { ?>
										<div class="mfilter-option mfilter-category mfilter-category-<?php echo $filter['type']; ?>">
											<?php if( $filter['type'] == 'related' ) { ?>
												<ul data-labels="<?php echo str_replace( '"', '&quot;', implode( '#|#', $filter['labels'] ) ); ?>">
													<?php $values = empty( $params[$filter['seo_name']] ) ? array() : $params[$filter['seo_name']]; ?>
													<?php foreach( $filter['levels'] as $level_id => $level ) { ?>
														<?php $value = empty( $values[$level_id] ) ? '' : $values[$level_id]; ?>
														<li>
															<select data-type="category-<?php echo $filter['type']; ?>">
																<option value=""><?php echo $level['name']; ?></option>
																<?php foreach( $level['options'] as $optKey => $optVal ) { ?>
																	<option value="<?php echo $optKey; ?>"<?php echo $value == $optKey ? ' selected="selected"' : ''; ?>><?php echo $optVal; ?></option>
																<?php } ?>
															</select>
														</li>
													<?php } ?>
												</ul>
											<?php } ?>
											
											<?php if( ! empty( $filter['show_button'] ) ) { ?>
												<div class="mfilter-button">
													<a href="#" class="button"><?php echo $text_button_apply; ?></a>
												</div>
											<?php } ?>
										</div>
									<?php } else if( $filter['type'] == 'search' || $filter['type'] == 'search_oc' ) { ?>
										<div class="mfilter-option mfilter-search<?php echo ! empty( $filter['button'] ) ? ' mfilter-search-button' : ''; ?>">
											<input
												id="mfilter-opts-search"
												type="text"
												data-refresh-delay="<?php echo isset( $filter['refresh_delay'] ) ? $filter['refresh_delay'] : '-1'; ?>"
												value="<?php echo isset( $params['search'][0] ) ? $params['search'][0] : ( isset( $params['search_oc'][0] ) ? $params['search_oc'][0] : '' ); ?>"
												/>

											<?php if( ! empty( $filter['button'] ) ) { ?>
												<i
													id="mfilter-opts-search_button"
													type="submit"
													></i>
											<?php } ?>
										</div>
									<?php } else if( $filter['type'] == 'price' ) { ?>
										<div class="mfilter-option mfilter-price">
											<div class="mfilter-price-inputs">
												<?php echo $this->currency->getSymbolLeft(); ?>
												<input
													id="mfilter-opts-price-min"
													type="text"
													value="<?php echo isset( $params['price'][0] ) ? $params['price'][0] : ''; ?>"
													/>
													<?php echo $this->currency->getSymbolRight(); ?>
													-
												<?php echo $this->currency->getSymbolLeft(); ?>
												<input
													id="mfilter-opts-price-max"
													type="text"
													value="<?php echo isset( $params['price'][1] ) ? $params['price'][1] : ''; ?>"
													/>
													<?php echo $this->currency->getSymbolRight(); ?>
											</div>
											<div class="mfilter-price-slider">
												<div id="mfilter-price-slider"></div>
											</div>
										</div>
									<?php } else if( $filter['type'] == 'rating' ) { ?>
										<div class="mfilter-tb">
											<?php for( $i = 5; $i >= 1; $i-- ) { ?>
												<div class="mfilter-option mfilter-tb-as-tr">
													<div class="mfilter-tb-as-td mfilter-col-input">
														<input
															id="mfilter-opts-rating-<?php echo $i; ?>"
															type="checkbox"
															<?php echo ! empty( $params['rating'] ) && in_array( $i, $params['rating'] ) ? ' checked="checked"' : ''; ?>
															value="<?php echo $i; ?>" />
													</div>
													<label class="mfilter-tb-as-td" for="mfilter-opts-rating-<?php echo $i; ?>">
														<?php echo $i; ?> <img src="catalog/view/theme/default/image/stars-<?php echo $i; ?>.png" alt="" />
													</label>
													<div class="mfilter-tb-as-td mfilter-col-count"><span class="mfilter-counter">0</span></div>
												</div>
											<?php } ?>
										</div>
									<?php } else if( in_array( $filter['type'], array( 'stock_status', 'manufacturers', 'checkbox', 'radio', 'image_list_radio', 'image_list_checkbox' ) ) ) { ?>
										<?php
										
											$_tmp_type = $filter['type'];
											
											if( in_array( $filter['type'], array( 'stock_status', 'manufacturers' ) ) ) {
												$_tmp_type = 'checkbox';
											}
										
										?>
										<div class="mfilter-tb">
											<?php $options_tmp = array(); ?>
											<?php foreach( $filter['options'] as $option_id => $option ) { if( $option['name'] === '' || isset( $options_tmp[$option['key']] ) ) continue; $options_tmp[$option['key']] = true; ?>
												<div class="mfilter-option mfilter-tb-as-tr">
													<div class="mfilter-tb-as-td mfilter-col-input">
														<input 
															id="mfilter-opts-attribs-<?php echo $base_id; ?>-<?php echo $option['key']; ?>" 
															name="<?php echo $filter['seo_name']; ?>"
															type="<?php echo $_tmp_type == 'image_list_checkbox' ? 'checkbox' : ( $_tmp_type == 'image_list_radio' ? 'radio' : $_tmp_type ); ?>"
															<?php echo ! empty( $params[$filter['seo_name']] ) && in_array( $option['value'], $params[$filter['seo_name']] ) ? ' checked="checked"' : ''; ?>
															value="<?php echo str_replace( '"', '&quot;', $option['value'] ); ?>" />
													</div>
													<label class="mfilter-tb-as-td" for="mfilter-opts-attribs-<?php echo $base_id; ?>-<?php echo $option['key']; ?>">
														<?php if( in_array( $_tmp_type, array( 'image_list_radio', 'image_list_checkbox' ) ) ) { ?>
															<img src="<?php echo $option['image']; ?>" /> <?php echo $option['name']; ?>
														<?php } else { ?>
															<?php echo $option['name']; ?>
														<?php } ?>
													</label>
													<div class="mfilter-tb-as-td mfilter-col-count"><span class="mfilter-counter">0</span></div>
												</div>
											<?php } ?>
										</div>
									<?php } else if( $filter['type'] == 'select' ) { ?>
										<div class="mfilter-tb">
											<div class="mfilter-option mfilter-select">
												<select>
													<option value="">---</option>
													<?php foreach( $filter['options'] as $option_id => $option ) { ?>
														<option 
															id="mfilter-opts-select-<?php echo $base_id; ?>-<?php echo $option['key']; ?>"
															value="<?php echo str_replace( '"', '&quot;', $option['value'] ); ?>"
															<?php echo ! empty( $params[$filter['seo_name']] ) && in_array( $option['value'], $params[$filter['seo_name']] ) ? ' selected="selected"' : ''; ?>
															><?php echo $option['name']; ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
									<?php } else if( $filter['type'] == 'image' ) { ?>
										<div class="mfilter-tb">
											<div class="mfilter-option mfilter-image">
												<ul>
													<?php foreach( $filter['options'] as $option_id => $option ) { ?>
														<li>
															<input
																id="mfilter-opts-attribs-<?php echo $base_id; ?>-<?php echo $option['key']; ?>" 
																name="<?php echo $filter['seo_name']; ?>"
																type="checkbox" 
																style="display:none"
																<?php echo ! empty( $params[$filter['seo_name']] ) && in_array( $option['value'], $params[$filter['seo_name']] ) ? ' checked="checked"' : ''; ?>
																value="<?php echo str_replace( '"', '&quot;', $option['value'] ); ?>" />
															<label for="mfilter-opts-attribs-<?php echo $base_id; ?>-<?php echo $option['key']; ?>" title="<?php echo $option['name']; ?>"><img src="<?php echo $option['image']; ?>" /></label>
														</li>
													<?php } ?>
												</ul>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</li>
			<?php } ?>
		</ul>
		<?php echo $buttons['bottom']; ?>
	</div>
</div>

<script type="text/javascript">
	MegaFilterLang.text_display = '<?php echo $text_display; ?>';
	MegaFilterLang.text_list	= '<?php echo $text_list; ?>';
	MegaFilterLang.text_grid	= '<?php echo $text_grid; ?>';
	MegaFilterLang.text_select	= '<?php echo $text_select; ?>';
	
	jQuery().ready(function(){
		jQuery('.box.mfilter-box:not(.init)').each(function(){
			var _t = jQuery(this).addClass('init'),
				_p = { };
			
			<?php foreach( $this->request->get as $k => $v ) { if( is_array( $v ) || ! in_array( $k, array( 'path', 'category_id', 'manufacturer_id', 'filter', 'search', 'sub_category', 'description', 'filter_tag' ) ) ) continue; ?>
				_p['<?php echo $k; ?>'] = '<?php echo addslashes( $v ); ?>';
			<?php } ?>
			
			(new MegaFilter()).init( _t, {
				'idx'					: '<?php echo (int) $_idx; ?>',
				'route'					: '<?php echo $_route; ?>',
				'routeProduct'			: '<?php echo $_routeProduct; ?>',
				'routeHome'				: '<?php echo $_routeHome; ?>',
				'contentSelector'		: '<?php echo empty( $settings['content_selector'] ) ? '#content' : addslashes( htmlspecialchars_decode( $settings['content_selector'] ) ); ?>',
				'refreshResults'		: '<?php echo empty( $settings["refresh_results"] ) ? "immediately" : $settings["refresh_results"]; ?>',
				'refreshDelay'			: <?php echo empty( $settings["refresh_delay"] ) ? 1000 : (int) $settings["refresh_delay"]; ?>,
				'autoScroll'			: <?php echo empty( $settings["auto_scroll_to_results"] ) ? 'false' : 'true'; ?>,
				'ajaxInfoUrl'			: '<?php echo $this->url->link( "module/mega_filter/ajaxinfo" ); ?>',
				'ajaxResultsUrl'		: '<?php echo $this->url->link( "module/mega_filter/results" ); ?>',
				'ajaxCategoryUrl'		: '<?php echo $this->url->link( "module/mega_filter/categories" ); ?>',
				'priceMin'				: <?php echo (string) $price['min']; ?>,
				'priceMax'				: <?php echo (string) $price['max']; ?>,
				'mijoshop'				: <?php echo class_exists( 'MijoShop' ) ? 'true' : 'false'; ?>,
				'showNumberOfProducts'	: <?php echo empty( $settings['show_number_of_products'] ) ? 'false' : 'true'; ?>,
				'addPixelsFromTop'		: <?php echo empty( $settings['add_pixels_from_top'] ) ? 0 : (int) $settings['add_pixels_from_top']; ?>,
				'displayListOfItems'	: {
					'type'				: '<?php echo empty( $settings['display_list_of_items']['type'] ) ? 'scroll' : $settings['display_list_of_items']['type']; ?>',
					'limit_of_items'	: <?php echo empty( $settings['display_list_of_items']['limit_of_items'] ) ? 4 : (int) $settings['display_list_of_items']['limit_of_items']; ?>,
					'maxHeight'			: <?php echo empty( $settings['display_list_of_items']['max_height'] ) ? 155 : (int) $settings['display_list_of_items']['max_height']; ?>,
					'textMore'			: '<?php echo $text_show_more; ?>',
					'textLess'			: '<?php echo $text_show_less; ?>'
				},
				'smp'					: {
					'isInstalled'			: <?php echo empty( $smp['isInstalled'] ) ? 'false' : 'true'; ?>,
					'disableConvertUrls'	: <?php echo empty( $smp['disableConvertUrls'] ) ? 'false' : 'true'; ?>
				},
				'params'				: _p,
				'inStockDefaultSelected': <?php echo empty( $settings['in_stock_default_selected'] ) ? 'false' : 'true'; ?>,
				'inStockStatus'			: '<?php echo empty( $settings['in_stock_status'] ) ? 7 : $settings['in_stock_status']; ?>',
				'showLoaderOverResults'	: <?php echo empty( $settings['show_loader_over_results'] ) ? 'false' : 'true'; ?>,
				'showLoaderOverFilter'	: <?php echo empty( $settings['show_loader_over_filter'] ) ? 'false' : 'true'; ?>,
				'hideInactiveValues'	: <?php echo empty( $settings['hide_inactive_values'] ) ? 'false' : 'true'; ?>,
				'text'					: {
					'loading'	: '<?php echo $text_loading; ?>'
				}
			});
		});
	});
</script>
