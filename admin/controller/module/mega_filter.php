<?php

/**
 * Mega Filter
 * 
 * @author marsilea15 <marsilea15@gmail.com> 
 */
class ControllerModuleMegaFilter extends Controller {
	
	/**
	 * Lista błędów
	 *
	 * @var array
	 */
	private $error			= array();
	
	private $_name			= 'mega_filter';
	
	private $_version		= '1.2.8.6';
	
	private $_hasFilters	= NULL;
	
	private static $_tmp_sort_parameters = NULL;
	
	public function __construct($registry) {
		parent::__construct($registry);
	}
	
	private function hasFilters() {
		if( $this->_hasFilters === NULL ) {
			$this->_hasFilters = version_compare( VERSION, '1.5.5', '>=' );
		}
		
		return $this->_hasFilters;
	}

	/**
	 * Inicjuj
	 */
	private function _init( $tab ) {
		// załaduj język
		$this->data = array_merge($this->data, $this->language->load('module/' . $this->_name));
		
		// aktywna zakładka
		$this->data['tab_active'] = $tab;
		
		// linki zakładek
		$this->data['tab_layout_link']		= $this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL');
		$this->data['tab_attributes_link']	= $this->url->link('module/' . $this->_name . '/attributes', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['tab_options_link']		= $this->url->link('module/' . $this->_name . '/options', 'token=' . $this->session->data['token'], 'SSL');
		if( $this->hasFilters() ) {
			$this->data['tab_filters_link']		= $this->url->link('module/' . $this->_name . '/filters', 'token=' . $this->session->data['token'], 'SSL');
		}
		$this->data['tab_settings_link']	= $this->url->link('module/' . $this->_name . '/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['tab_about_link']		= $this->url->link('module/' . $this->_name . '/about', 'token=' . $this->session->data['token'], 'SSL');
		
		// linki
		$this->data['action']	= $this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL');
		$this->data['back']		= $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['_name']	= $this->_name;
		
		// okruszki
		$this->data['breadcrumbs'] = array();
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		// tytuł
		$this->document->setTitle($this->language->get('heading_title'));
		
		// ustawienia szablonu
		$this->template = 'module/' . $this->_name . ( $tab != $this->_name ? '_' . $tab : '' ) . '.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		
		$this->_messages();
		
		// błędy
		$this->_setErrors(array(
			'warning' => ''
		));
		
		$curr_ver = $this->config->get('mfilter_version');
		
		if( version_compare( $curr_ver, $this->_version, '<' ) || $this->_isOldMFilterPlus() ) {
			// @since 1.2.0
			if( version_compare( $curr_ver, '1.2.0', '<' ) ) {
				$this->db->query( "
					UPDATE
						" . DB_PREFIX . "setting
					SET
						`group` = CONCAT( `group`, '_', '" . (int) $this->config->get( 'config_language_id' ) . "' ),
						`key` = CONCAT( `key`, '_', '" . (int) $this->config->get( 'config_language_id' ) . "' )
					WHERE
						`key` REGEXP '^mega_filter_at_img_[0-9]+$' OR `key` REGEXP '^mega_filter_at_sort_[0-9]+$'
				" );
			}
			
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			// @since 1.2.2
			if( version_compare( $curr_ver, '1.2.2', '<' ) ) {
				$settings = $this->config->get( $this->_name . '_settings' );
				$settings['show_loader_over_results'] = '1';
				
				// główny sklep
				$this->model_setting_setting->editSetting($this->_name . '_settings', array(
					$this->_name . '_settings' => $settings
				));
				
				// pozostałe sklepy
				foreach( $this->model_setting_store->getStores() as $result ) {
					$this->model_setting_setting->editSetting($this->_name . '_settings', array(
						$this->_name . '_settings' => $settings
					), $result['store_id']);
				}
			}
			
			// główny sklep
			$this->model_setting_setting->editSetting('mfilter_version', array(
				'mfilter_version' => $this->_version
			));
			
			// pozostałe sklepy
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting('mfilter_version', array(
					'mfilter_version' => $this->_version
				), $result['store_id']);
			}
			
			// @since 1.2.7
			if( version_compare( $curr_ver, '1.2.7', '<' ) ) {
				// pliki JavaScript zostały przeniesione do innego katalogu - poniższe nie są już potrzebne
				if( file_exists( DIR_APPLICATION . 'view/javascript/mf/colorpicker.js' ) && is_writable( DIR_APPLICATION . 'view/javascript/mf/colorpicker.js' ) ) {
					ob_start();
					@ unlink( DIR_APPLICATION . 'view/javascript/mf/colorpicker.js' );
					ob_end_clean();
				}
				
				if( file_exists( DIR_APPLICATION . 'view/javascript/mf/remaining.js' ) && is_writable( DIR_APPLICATION . 'view/javascript/mf/remaining.js' ) ) {
					ob_start();
					@ unlink( DIR_APPLICATION . 'view/javascript/mf/remaining.js' );
					ob_end_clean();
				}
				
				if( file_exists( DIR_APPLICATION . 'view/javascript/mf' ) && is_writable( DIR_APPLICATION . 'view/javascript/mf' ) ) {
					ob_start();
					@ rmdir( DIR_APPLICATION . 'view/javascript/mf' );
					ob_end_clean();
				}
			}
			
			// @since 1.2.8
			if( version_compare( $curr_ver, '1.2.8', '<' ) ) {
				// plik nie jest już potrzebny
				if( file_exists( DIR_APPLICATION . 'view/stylesheet/mf/js/remaining.js' ) && is_writable( DIR_APPLICATION . 'view/stylesheet/mf/js/remaining.js' ) ) {
					ob_start();
					@ unlink( DIR_APPLICATION . 'view/stylesheet/mf/js/remaining.js' );
					ob_end_clean();
				}
			}
			
			if( $tab != 'installprogress' ) {
				$this->_installMFilterPlus();
			}
			
			$this->data['_success'] = $this->language->get('success_updated');
		}
	}
	
	private function _hasMFilterPlus() {
		if( ! file_exists( DIR_SYSTEM . 'library/mfilter_plus.php' ) ) {
			return false;
		}
		
		return true;
	}
	
	private function _isOldMFilterPlus() {		
		if( ! $this->_hasMFilterPlus() ) {
			return false;
		}
		
		$curr_ver = $this->config->get('mfilter_plus_version');		
		$this->load->library('mfilter_plus');
		
		return version_compare( $curr_ver, Mfilter_Plus::getInstance( $this )->version(), '<' );
	}
	
	private function _messages() {		
		// powiadomienia
		if( isset( $this->session->data['success'] ) ) {
			$this->data['_success'] = $this->session->data['success'];
			
			unset( $this->session->data['success'] );
		}
	}
	
	public function get_data() {
		$json = array();
		
		if( isset( $this->request->get['mf_id'] ) ) {
			$json = $this->config->get( $this->_name . '_module' );
			$json = isset( $json[$this->request->get['mf_id']] ) ? $json[$this->request->get['mf_id']] : array();
		}
		
		echo json_encode( $json );
		
		die();
	}
	
	public function remove_data() {
		if( isset( $this->request->get['mf_id'] ) && $this->checkPermission() ) {
			$data = $this->config->get( $this->_name . '_module' );
			
			unset( $data[$this->request->get['mf_id']] );			
			
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting($this->_name . '_module', array( $this->_name . '_module' => $data ));
		}
		
		die();
	}
	
	private function _save( & $data, & $store ) {
		foreach( $data as $k => $v ) {
			if( is_array( $v ) ) {
				$tmp = isset( $store[$k] ) ? $store[$k] : array();
				
				$this->_save( $v, $tmp );
				
				$store[$k] = $tmp;
			} else if( isset( $store[$k] ) ) {
				array_push( $store, $v );
			} else {
				$store[$k] = $v;
			}
		}
	}
	
	public function save_data() {
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$idx	= (int) $this->request->get['mf_idx'];
			$count	= (int) $this->request->get['mf_count'];
			
			if( ! $idx || ! isset( $this->session->data['mf_data_to_save'] ) ) {
				$this->session->data['mf_data_to_save'] = array();
			}
			
			$this->_save( $this->request->post[$this->_name.'_module'], $this->session->data['mf_data_to_save'] );
			
			if( $idx == $count ) {				
				$settings = $this->config->get( $this->_name . '_settings' );
				$mf_id = $this->request->get['mf_id'];
				
				if( empty( $this->session->data['mf_data_to_save']['layout_id'] ) ) {
					$this->session->data['mf_data_to_save']['layout_id'] = array();
				}
				
				if( ! in_array( $settings['layout_c'], $this->session->data['mf_data_to_save']['layout_id'] ) ) {
					$this->session->data['mf_data_to_save']['category_id'] = array();
				}
			
				if( empty( $this->session->data['mf_data_to_save']['store_id'] ) ) {
					$this->session->data['mf_data_to_save']['store_id'] = array( 0 );
				}
				
				if( NULL == ( $data = $this->config->get($this->_name . '_module') ) ) {
					$data = array();
				}
				
				$data[$mf_id] = $this->session->data['mf_data_to_save'];
				
				$byStores = array();
				
				foreach( $data as $k => $v ) {
					if( empty( $v['store_id'] ) ) {
						$v['store_id'] = array( 0 );
					}
					
					foreach( $v['store_id'] as $kk => $vv ) {
						$byStores[$vv] = $vv;
					}
				}			
				
				$this->load->model('setting/setting');
				
				foreach( $byStores as $store_id ) {
					$this->model_setting_setting->editSetting(
						$this->_name . '_module', 
						array( $this->_name . '_module' => $data ),
						$store_id
					);
				}	
				
				unset( $this->session->data['mf_data_to_save'] );
			}
		}
		
		die();
	}
	
	/**
	 * Ustawienia szablonów
	 */
	public function index() {
		$this->_init( $this->_name );
		
		// załaduj modele
		$this->load->model('design/layout');
		$this->load->model('localisation/language');
		$this->load->model('design/layout');
		$this->load->model('catalog/category');
		$this->load->model('setting/store');
		
		$settings = $this->config->get( $this->_name . '_settings' );
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$modules = (array) $this->config->get($this->_name . '_module');
		
		ksort( $modules );
		
		$this->_setParams(array(
			'modules'		=> $modules
		), array());
		
		// parametry
		$this->data['token']		= $this->session->data['token'];
		$this->data['layouts']		= $this->model_design_layout->getLayouts();
		$this->data['languages']	= $this->model_localisation_language->getLanguages();		
		$this->data['layouts']		= $this->model_design_layout->getLayouts();
		$this->data['settings']		= $settings;
		$this->data['attribs']		= (array) $this->config->get( $this->_name . '_attribs' );
		$this->data['options']		= (array) $this->config->get($this->_name . '_options');
		$this->data['filters']		= (array) $this->config->get($this->_name . '_filters');
		$this->data['items']		= $this->_getItems();
		$this->data['optionItems']	= $this->_getOptionItems();
		
		if( $this->hasFilters() ) {
			$this->data['filterItems']	= $this->_getFilterItems();
		}
		
		// Show in Categories ////////////////////////////////////////////////////////////
		$categories_ids = array();
		
		foreach( $this->data['modules'] as $module ) {			
			if( ! empty( $module['category_id'] ) ) {
				foreach( $module['category_id'] as $category_id ) {
					$categories_ids[] = (int) $category_id;
				}
			}
			
			if( ! empty( $module['categories'] ) ) {
				foreach( $module['categories'] as $category_id ) {
					if( ! empty( $category_id['root_category_id'] ) ) {
						$categories_ids[] = (int) $category_id['root_category_id'];
					}
				}
			}
		}
		
		$this->data['categories'] = array();
		$categories = array();
		
		if( ! empty( $categories_ids ) ) {
			$categories	= array();
			
			foreach( $this->db->query( $this->_qCat( $categories_ids ) )->rows as $category ) {
				$categories[$category['category_id']] = ( $category['path'] ? $category['path'] . ' &gt; ' : '' ) . $category['name'];
			}
			
			foreach( $this->data['modules'] as $module_id => $module ) {
				if( ! empty( $module['category_id'] ) ) {				
					foreach( $module['category_id'] as $category_id ) {
						$this->data['categories'][$module_id][$category_id] = $categories[$category_id];
					}
				}
			}
		}
		
		$this->data['categoriesNames'] = $categories;
		
		// Hide in Categories ////////////////////////////////////////////////////////////
		$categories_ids = array();
		
		foreach( $this->data['modules'] as $module ) {
			if( empty( $module['hide_category_id'] ) ) continue;
			
			foreach( $module['hide_category_id'] as $category_id ) {
				$categories_ids[] = (int) $category_id;
			}
		}
		
		$this->data['hideCategories'] = array();
		
		if( ! empty( $categories_ids ) ) {
			$categories	= array();
			
			foreach( $this->db->query( $this->_qCat( $categories_ids ) )->rows as $category ) {
				$categories[$category['category_id']] = ( $category['path'] ? $category['path'] . ' &gt; ' : '' ) . $category['name'];
			}
			
			foreach( $this->data['modules'] as $module_id => $module ) {
				if( empty( $module['hide_category_id'] ) ) continue;
				
				foreach( $module['hide_category_id'] as $category_id ) {
					if( empty( $categories[$category_id] ) ) continue;
					
					$this->data['hideCategories'][$module_id][$category_id] = $categories[$category_id];
				}
			}
		}
		
		// Stores //////////////////////////////////////////////////////////////
		
		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->language->get('text_default')
		);
				
		foreach( $this->model_setting_store->getStores() as $result ) {
			$this->data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			);
		}
		
		////////////////////////////////////////////////////////////////////////
		
		$this->data['action_ldv']			= $this->url->link('module/' . $this->_name . '/ldv', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['action_save_data']		= $this->url->link('module/' . $this->_name . '/save_data', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['action_get_data']		= $this->url->link('module/' . $this->_name . '/get_data', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['action_remove_data']	= $this->url->link('module/' . $this->_name . '/remove_data', 'token=' . $this->session->data['token'], 'SSL');
				
		$this->response->setOutput( $this->render() );
	}
	
	private function _qCat( $categories_ids ) {
		return "
			SELECT 
				`c`.`category_id`, `cd`.`name`,
				(
					SELECT 
						GROUP_CONCAT(`cd1`.`name` ORDER BY `level` SEPARATOR ' &gt; ') 
					FROM 
						`" . DB_PREFIX . "category_path` AS `cp` 
					LEFT JOIN 
						`" . DB_PREFIX . "category_description` AS `cd1` 
					ON 
						`cp`.`path_id` = `cd1`.`category_id` AND `cp`.`category_id` != `cp`.`path_id` 
					WHERE 
						`cp`.`category_id` = `c`.`category_id` AND `cd1`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' 
					GROUP BY 
						`cp`.`category_id`
				) AS `path`
			FROM 
				`" . DB_PREFIX . "category` AS `c`
			LEFT JOIN
				`" . DB_PREFIX . "category_description` AS `cd`
			ON
				`c`.`category_id` = `cd`.`category_id` AND `cd`.`language_id` = " . $this->config->get( 'config_language_id' ) . "
			WHERE
				`c`.`category_id` IN(" . implode( ',', array_unique( $categories_ids ) ) . ")
		";
	}
	
	public function ldv() {
		// załaduj język
		$this->data = array_merge($this->data, $this->language->load('module/' . $this->_name));
		
		$this->data['_name']	= $this->_name;
		$this->data['name']		= $this->request->get['name'];
		$this->data['type']		= $this->request->get['type'];
		$this->data['IDX']		= $this->request->get['idx'];
		
		switch( $this->data['type'] ) {
			case 'base_attribs' : {
				$settings	= $this->config->get($this->_name . '_settings' );
				$this->data['base_attribs'] = $settings['attribs'];
				
				break;
			}
			case 'attribs'		: $this->data['attribs']	= $this->config->get($this->_name . '_attribs'); break;
			case 'options'		: $this->data['options']	= $this->config->get($this->_name . '_options'); break;
			case 'filters'		: $this->data['filters']	= $this->config->get($this->_name . '_filters'); break;
		}
		
		if( NULL != ( $module = $this->config->get($this->_name . '_module' ) ) ) {
			if( isset( $module[$this->request->get['idx']] ) ) {
				$module = $module[$this->request->get['idx']];
				
				if( ! empty( $module['base_attribs'] ) ) {
					$this->data['base_attribs'] = $module['base_attribs'];
				}

				if( ! empty( $module['attribs'] ) ) {
					$this->data['attribs'] = $module['attribs'];
				}

				if( ! empty( $module['options'] ) ) {
					$this->data['options'] = $module['options'];
				}

				if( ! empty( $module['filters'] ) ) {
					$this->data['filters'] = $module['filters'];
				}
			}
		}
		
		if( $this->data['type'] == 'options' ) {
			$this->data['optionItems']	= $this->_getOptionItems();
		} else if( $this->hasFilters() && $this->data['type'] == 'filters' ) {
			$this->data['filterItems'] = $this->_getFilterItems();
		} else {
			$this->data['items']		= $this->_getItems();
		}
		
		$this->template = 'module/' . $this->_name . '_ldv.tpl';
		$this->response->setOutput( $this->render() );
	}
	
	private function _getItems() {
		$items = array();
		
		$this->load->model('catalog/attribute');
		
		foreach( $this->model_catalog_attribute->getAttributes(array()) as $attribute ) {
			$items[$attribute['attribute_group_id']]['name']		= $attribute['attribute_group'];
			$items[$attribute['attribute_group_id']]['childs'][]	= $attribute;
		}
		
		return $items;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Ustawienia atrybutów 
	 */
	public function attributes() {
		$this->_init( 'attributes' );
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/attributes', 'token=' . $this->session->data['token'], 'SSL');
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_attribs', $this->request->post);
			
			// pozostałe sklepy			
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_attribs', $this->request->post, $result['store_id']);
			}
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name . '/attributes', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->load->model('tool/image');
		$this->load->model('catalog/attribute_group');
		$this->load->model('localisation/language');
		
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		
		$this->data['token'] = $this->session->data['token'];
		$this->data['items'] = $this->_getItems();
		$this->data['attribs'] = $this->config->get($this->_name . '_attribs');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['attribGroups'] = $this->model_catalog_attribute_group->getAttributeGroups(array());
		$this->data['action_attribs_by_group'] = $this->url->link('module/' . $this->_name . '/getAttribsByGroupAsJson', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['action_values_by_attrib'] = $this->url->link('module/' . $this->_name . '/getAttribsValuesByAttribAsJson', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['action_attribs_save'] = $this->url->link('module/' . $this->_name . '/attribsSave', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['action_attribs_reset'] = $this->url->link('module/' . $this->_name . '/attribsReset', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->response->setOutput( $this->render() );
	}
	
	public function attribsReset() {
		if( ! empty( $this->request->post['attr_id'] ) && ! empty( $this->request->post['type'] ) && ! empty( $this->request->post['lang_id'] ) ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			$attr_id = (int)$this->request->post['attr_id'];
			$lang_id = (int)$this->request->post['lang_id'];
			$type = (int)$this->request->post['type'];
			
			switch( $type ) {
				case 'images' : $type = 'img'; break;
				default : $type = 'sort';
			}
			
			// główny sklep
			$this->model_setting_setting->deleteSetting($this->_name . '_at_' . $type . '_' . $attr_id . '_' . $lang_id );
			
			// pozostałe sklepy			
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->deleteSetting($this->_name . '_at_' . $type . '_' . $attr_id . '_' . $lang_id, $result['store_id']);
			}	
		}
	}
	
	public function attribsSave() {
		if( ! empty( $this->request->post['attr_id'] ) && ! empty( $this->request->post['items'] ) || ! empty( $this->request->post['type'] ) && ! empty( $this->request->post['lang_id'] ) ) {
			$data		= array();
			$attr_id	= (int) $this->request->post['attr_id'];
			$lang_id = (int)$this->request->post['lang_id'];
			$type		= $this->request->post['type'];
			
			if( isset( $this->request->post['items'] ) ) {
				foreach( $this->request->post['items'] as $i => $v ) {
					switch( $type ) {
						case 'images' : {
							$data[$v['key']] = $v['img'];

							break;
						}
						case 'sort-values' : {
							$data[$v['key']] = $i;

							break;
						}
					}
				}
			}
			
			switch( $type ) {
				case 'images'		: 
					$type = 'img'; break;
				case 'sort-values'	: 
				default : 
					$type = 'sort'; break;
			}
			
			$key	= $this->_name . '_at_' . $type . '_' . $attr_id . '_' . $lang_id;
			$data	= array( $key => $data );
			
			$this->load->model('setting/store');
			$this->load->model('setting/setting');
			
			// główny sklep
			$this->model_setting_setting->editSetting($key, $data);
			
			// pozostałe sklepy
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($key, $data, $result['store_id']);
			}
		}
	}
	
	public function getAttribsByGroupAsJson() {
		$json = array();
		
		if( ! empty( $this->request->get['attribute_group_id'] ) ) {
			$this->load->model('catalog/attribute');
			$json = $this->model_catalog_attribute->getAttributes(array('filter_attribute_group_id'=>(int)$this->request->get['attribute_group_id']));
		}
		
		$this->response->setOutput( json_encode( $json ) );
	}
	
	public function getAttribsValuesByAttribAsJson() {
		$json = array();
		
		if( ! empty( $this->request->get['attribute_id'] ) && ! empty( $this->request->get['type'] ) && ! empty( $this->request->get['lang_id'] ) ) {
			$this->load->model('tool/image');
			
			/**
			 * Parametry 
			 */
			$attribute_id	= $this->request->get['attribute_id'];
			$lang_id		= $this->request->get['lang_id'];
			$type			= $this->request->get['type'];
			$settings		= $this->config->get( $this->_name . '_settings' );
			$attribs		= (array) $this->config->get( $this->_name . '_attribs' );
			$images			= $type == 'images' ? (array) $this->config->get( $this->_name . '_at_img_' . $attribute_id . '_' . $lang_id ) : array();
			
			/**
			 * SQL 
			 */
			$sql			= "
				SELECT
					`pa`.`text`,
					`pa`.`attribute_id`
				FROM
					`" . DB_PREFIX . "product_attribute` AS `pa`
				INNER JOIN
					`" . DB_PREFIX . "product` AS `p`
				ON
					`pa`.`product_id` = `p`.`product_id` AND `p`.`status` = '1'
				WHERE
					`pa`.`attribute_id` = " . (int) $this->request->get['attribute_id'] . " AND
					`pa`.`language_id` = '" . (int)$lang_id . "'
				GROUP BY
					`pa`.`text`, `pa`.`attribute_id`
			";
			$unique = array();
			foreach( $this->db->query( $sql )->rows as $row ) {
				if( ! empty( $settings['attribute_separator'] ) ) {
					$row['text'] = explode( $settings['attribute_separator'], $row['text'] );
					
					foreach( $row['text'] as $text ) {
						if( isset( $unique[$text] ) ) continue;
						
						$k = md5( $text );
						
						$json[] = array( 'key' => $k, 'val' => $text, 'img' => isset( $images[$k] ) ? $this->model_tool_image->resize( $images[$k], 100, 100 ) : '' );
						$unique[$text] = true;
					}
				} else if( ! isset( $unique[$row['text']] ) ) {
					$k = md5( $row['text'] );
					$json[] = array( 'key' => $k, 'val' => $row['text'], 'img' => isset( $images[$k] ) ? $this->model_tool_image->resize( $images[$k], 100, 100 ) : '' );
					$unique[$row['text']] = true;
				}
			}
			
			$this->_sortOptions( $json, ! empty( $attribs[$attribute_id]['sort_order_values'] ) ? $attribs[$attribute_id]['sort_order_values'] : '', $lang_id, $attribute_id );
		}
		
		$this->response->setOutput( json_encode( $json ) );
	}
	
	private static function _sortItems( $a, $b ) {
		if( self::$_tmp_sort_parameters['attribute_id'] !== NULL && self::$_tmp_sort_parameters['config'] ) {
			$as = isset( self::$_tmp_sort_parameters['config'][$a['key']] ) ? self::$_tmp_sort_parameters['config'][$a['key']] : count(self::$_tmp_sort_parameters['config']);
			$bs = isset( self::$_tmp_sort_parameters['config'][$b['key']] ) ? self::$_tmp_sort_parameters['config'][$b['key']] : count(self::$_tmp_sort_parameters['config']);
			
			if( $as > $bs )
				return 1;
			
			if( $as < $bs )
				return -1;
			
			return 0;
		} else {		
			$fn = 'sort';

			if( self::$_tmp_sort_parameters['order'] == 'desc' )
				$fn = 'r' . $fn;

			$tmp = self::$_tmp_sort_parameters['order'] == 'desc' ? array( $b['val'], $a['val'] ) : array( $a['val'], $b['val'] );
			
			switch( self::$_tmp_sort_parameters['type'] ) {
				case 'string' : {
					call_user_func_array( $fn, array( & $tmp, SORT_STRING ) );

					break;
				}
				case 'numeric' : {
					call_user_func_array( $fn, array( & $tmp, SORT_NUMERIC ) );

					break;
				}
			}
			
			return $tmp[0] == $a['val'] ? 0 : 1;
		}
	}
	
	private function _sortOptions( & $options, $sort, $lang_id, $attribute_id = NULL ) {
		if( $sort ) {
			list( $type, $order ) = explode( '_', $sort );
		} else {
			$type = $order = '';
		}
		
		self::$_tmp_sort_parameters = array(
			'attribute_id'	=> $attribute_id,
			'type'			=> $type,
			'order'			=> $order,
			'config'		=> $this->config->get('mega_filter_at_sort_' . $attribute_id . '_' . $lang_id )
		);
		
		if( ! self::$_tmp_sort_parameters['type'] && ! self::$_tmp_sort_parameters['config'] ) {
			self::$_tmp_sort_parameters = NULL;
			
			return;
		}
		
		usort( $options, 'ControllerModuleMegaFilter::_sortItems' );
		
		self::$_tmp_sort_parameters = NULL;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private function _getOptionItems() {
		$items = array();
		$sql = "
			SELECT 
				* 
			FROM 
				`" . DB_PREFIX . "option` AS `o` 
			LEFT JOIN 
				`" . DB_PREFIX . "option_description` AS `od` 
			ON 
				`o`.`option_id` = `od`.`option_id` 
			WHERE 
				`od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND
				`o`.`type` IN( 'select', 'radio', 'checkbox', 'image' )
		";
		
		foreach( $this->db->query( $sql )->rows as $option ) {
			$items[$option['option_id']] = $option;
		}
		
		return $items;
	}
	
	/**
	 * Ustawienia opcji 
	 */
	public function options() {
		$this->_init( 'options' );
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/options', 'token=' . $this->session->data['token'], 'SSL');
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_options', $this->request->post);
			
			// pozostałe sklepy			
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_options', $this->request->post, $result['store_id']);
			}	
			
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name . '/options', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['optionItems'] = $this->_getOptionItems();
		$this->data['options'] = $this->config->get($this->_name . '_options');
		
		$this->response->setOutput( $this->render() );
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private function _getFilterItems() {
		$items = array();
		$sql = "
			SELECT 
				* 
			FROM 
				`" . DB_PREFIX . "filter_group` AS `fg` 
			LEFT JOIN 
				`" . DB_PREFIX . "filter_group_description` AS `fgd` 
			ON 
				`fg`.`filter_group_id` = `fgd`.`filter_group_id` AND `fgd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'
		";
		
		foreach( $this->db->query( $sql )->rows as $filter ) {
			$items[$filter['filter_group_id']] = $filter;
		}
		
		return $items;
	}
	
	/**
	 * Ustawienia filtrów 
	 */
	public function filters() {
		$this->_init( 'filters' );
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/filters', 'token=' . $this->session->data['token'], 'SSL');
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_filters', $this->request->post);
			
			// pozostałe sklepy
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_filters', $this->request->post, $result['store_id']);
			}	
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name . '/filters', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['filterItems'] = $this->_getFilterItems();
		$this->data['filters'] = $this->config->get($this->_name . '_filters');
		
		$this->response->setOutput( $this->render() );
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Ustawienia modułu 
	 */
	public function settings() {
		$this->_init( 'settings' );
		
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/settings', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['settings'] = $this->config->get($this->_name . '_settings');
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->model('setting/setting');
			$this->load->model('setting/store');
			
			// główny sklep
			$this->model_setting_setting->editSetting($this->_name . '_settings', $this->request->post);
			
			// pozostałe sklepy
			foreach( $this->model_setting_store->getStores() as $result ) {
				$this->model_setting_setting->editSetting($this->_name . '_settings', $this->request->post, $result['store_id']);
			}	
			
			if( $this->_hasMFilterPlus() ) {
				$before = empty( $this->data['settings']['attribute_separator'] ) ? '' : $this->data['settings']['attribute_separator'];
				$after = empty( $this->request->post['mega_filter_settings']['attribute_separator'] ) ? '' : $this->request->post['mega_filter_settings']['attribute_separator'];

				if( $before != $after ) {
					$this->redirect($this->url->link('module/' . $this->_name . '/installprogress', 'token=' . $this->session->data['token'], 'SSL'));
				}
			}
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('module/' . $this->_name . '/settings', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		if( ! isset( $this->data['settings']['in_stock_status'] ) )
			$this->data['settings']['in_stock_status'] = 7;
		
		$this->load->model('design/layout');
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
		
		$this->load->model('localisation/stock_status');
		$this->data['stockStatuses'] = $this->model_localisation_stock_status->getStockStatuses(array());
				
		$this->response->setOutput( $this->render() );
	}
	
	/**
	 * Ustaw błędy
	 * 
	 * @param array $errors
	 */
	private function _setErrors( $errors ) {
		foreach( $errors as $name => $default ) {
			$this->data['_error_' . $name] = isset( $this->error[$name] ) ? $this->error[$name] : $default;
		}
	}
	
	/**
	 * Ustaw parametry
	 * 
	 * @param array $params
	 * @param array $record
	 */
	private function _setParams( $params, $record = NULL ) {
		if( ! $record )
			$record = array();
		
		foreach( $params as $param => $default ) {
			if( isset( $this->request->post[$param] ) )
				$this->data[$param] = $this->request->post[$param];
			else if( isset( $record[$param] ) )
				$this->data[$param] = $record[$param];
			else
				$this->data[$param] = $default;
		}
	}
	
	/**
	 * About
	 */
	public function about() {
		$this->_init( 'about' );
		
		$this->data['ext_version'] = $this->_version;
		$this->data['action']	= $this->url->link('module/' . $this->_name . '/about', 'token=' . $this->session->data['token'], 'SSL');
		
		if( $this->config->get('mfilter_plus_version') ) {
			$this->data['plus_version'] = $this->config->get('mfilter_plus_version');
			$this->data['action_rebuild_index'] = $this->data['action']	= $this->url->link('module/' . $this->_name . '/installprogress', 'token=' . $this->session->data['token'], 'SSL');
		}
		
		$this->response->setOutput( $this->render() );
	}
	
	private function _installMFilterPlus() {
		if( ! $this->_hasMFilterPlus() )
			return false;
		
		$this->load->library('mfilter_plus');
		
		if( Mfilter_Plus::getInstance( $this )->install() ) {
			$this->redirect($this->url->link('module/' . $this->_name . '/installprogress', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}
	
	public function installprogress() {
		if( ! $this->_hasMFilterPlus() ) {
			$this->redirect($this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		if( $this->request->server['REQUEST_METHOD'] == 'POST' && $this->checkPermission() ) {
			$this->load->library('mfilter_plus');
			
			echo json_encode( Mfilter_Plus::getInstance( $this )->installprogress() );
			
			return;
		}
		
		$this->_init( 'installprogress' );
		
		$this->data['progress_action'] = $this->url->link('module/' . $this->_name.'/installprogress', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['main_action'] = $this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL');
		
		$this->response->setOutput( $this->render() );
	}
	
	/**
	 * Instalacja
	 */
	public function install() {		
		$this->language->load('module/' . $this->_name);
		
		// załaduj modele
		$this->load->model('setting/setting');
		$this->load->model('setting/extension');
		$this->load->model('localisation/language');
		
		$titles = array();
		
		foreach( $this->model_localisation_language->getLanguages() as $language ) {
			$titles[$language['language_id']] = 'Mega Filter PRO';
		}
			
		/**
		 * Wprowadź domyślne ustawienia 
		 */
		$this->model_setting_setting->editSetting($this->_name . '_module', array(
			$this->_name . '_module' => array(
				1 => array(
					'title'			=> $titles,
					'layout_id'		=> array( '3' ),
					'store_id'		=> array( 0 ),
					'position'		=> 'column_left',
					'status'		=> '1',
					'sort_order'	=> ''
				)
			)
		));
		
		$this->model_setting_setting->editSetting($this->_name . '_settings', array(
			$this->_name . '_settings' => array(
				'show_number_of_products'	=> '1',
				'show_loader_over_results'	=> '1',
				'css_style'					=> '',
				'refresh_results'			=> 'immediately',
				'attribs'					=> array(
					'price'		=> array(
						'enabled'		=> '1',
						'sort_order'	=> '-1'
					)
				),
				'layout_c'					=> '3'
			)
		));
		
		/**
		 * Sprawdź czy wtyczka jest na liście 
		 */
		if( ! in_array( $this->_name, $this->model_setting_extension->getInstalled('module') ) )
			$this->model_setting_extension->install('module', $this->_name);
			
		$this->_installMFilterPlus();
		
		$this->session->data['success'] = $this->language->get('success_install');

		$this->redirect($this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'));
	}
	
	/**
	 * Deinstalacja
	 */
	public function uninstall() {
		$this->language->load('module/' . $this->_name);	
			
		/**
		 * Sprawdź czy wtyczka jest na liście 
		 */
		$this->load->model('setting/extension');
		$this->load->model('setting/store');
		
		$this->db->query("
			DELETE FROM
				`" . DB_PREFIX . "setting`
			WHERE
				`key` IN('mega_filter_module','mfilter_version','mega_filter_attribs','mega_filter_settings','mega_filter_filters','mega_filter_options') OR
				`key` REGEXP '^mega_filter_at_img_[0-9]+_[0-9]+$' OR 
				`key` REGEXP '^mega_filter_at_sort_[0-9]+_[0-9]+$'
		");
			
		if( in_array( $this->_name, $this->model_setting_extension->getInstalled('module') ) )
			$this->model_setting_extension->uninstall('module', $this->_name);
		
		if( file_exists( DIR_SYSTEM . 'library/mfilter_plus.php' ) ) {
			$this->load->library( 'mfilter_plus' );
			Mfilter_Plus::getInstance( $this )->uninstall();
		}
		
		$this->session->data['success'] = $this->language->get('success_uninstall');

		// przekieruj do listy modułów
		$this->redirect( $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL') );
	}
	
	/**
	 * Zweryfikuj prawnienia
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		if( ! $this->user->hasPermission('modify', 'module/' . $this->_name) )
			return false;
		
		return true;
	}
	
	protected function checkPermission() {
		if( ! $this->hasPermission() ) {
			$this->_setErrors(array(
				'warning'	=> $this->language->get( 'error_permission' )
			));
			
			return false;
		}
		
		return true;
	}
}
?>