<?php

class ControllerModuleTeilFlexibleProducts extends Controller {

	private $error;

	public function index()
	{
		$this->load->model('tool/image');

		$this->language->load('module/teil_flexible_products');
		$this->document->setTitle($this->language->get('heading_title'));

		// Set up breadcrumbs
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
			'href'      => $this->url->link('module/teil_flexible_products', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		// Language
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_module'] = $this->language->get('text_module');
		$this->data['text_insert_text_placeholder'] = $this->language->get('text_insert_text_placeholder');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');

		$this->data['entry_limit'] = $this->language->get('entry_limit');
		$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');

		// Local vars
		$this->data['action'] = $this->url->link('module/teil_flexible_products', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		$this->data['modules'] = array();

		if (isset($this->request->post['positions'])) {
			$this->data['modules'] = $this->request->post['positions'];
		} elseif ($this->config->get('teil_flexible_positions')) { 
			$this->data['modules'] = $this->config->get('teil_flexible_positions');
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'teil/module/teil_flexible_products/index.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}


	// Get module setting
	public function settings()
	{
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model('setting/setting');

		$product_ids = $this->config->get('teil_flexible_products');
		$products = explode(',', $product_ids);
		
		$result = array(
			'products' => array(),
			'selected_ids' => $product_ids
		);

		foreach ($products as $product_id)
		{
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info)
			{
				// Get image
				if (!file_exists(DIR_IMAGE . $product_info['image']) AND !is_file(DIR_IMAGE . $product_info['image']))
				{
					$thumb = $this->model_tool_image->resize('no_image.jpg', 100, 100);
				}
				else
				{
					$thumb = $this->model_tool_image->resize($product_info['image'], 100, 100);
				}

				$result['products'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name'],
					'thumb'      => $thumb
				);
			}
		}

		echo json_encode($result); die();
	}


	public function autocomplete()
	{
		$json = array();

		$this->load->model('tool/image');

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model']) || isset($this->request->get['filter_category_id']))
		{
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

			if (isset($this->request->get['filter_name']))
			{
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model']))
			{
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit']))
			{
				$limit = $this->request->get['limit'];	
			} else {
				$limit = 20;	
			}

			$data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($data);

			foreach ($results as $result)
			{
				// Get image
				if (!file_exists(DIR_IMAGE . $result['image']) AND !is_file(DIR_IMAGE . $result['image']))
				{
					$thumb = $this->model_tool_image->resize('no_image.jpg', 100, 100);
				}
				else
				{
					$thumb = $this->model_tool_image->resize($result['image'], 100, 100);
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),	
					'model'      => $result['model'],
					'price'      => $result['price'],
					'thumb'      => $thumb
				);	
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		$this->language->load('module/teil_flexible_products');
		
		if (!$this->user->hasPermission('modify', 'module/teil_flexible_products')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['positions'])) {
			foreach ($this->request->post['positions'] as $key => $value) {
				if (!$value['image_width'] || !$value['image_height']) {
					$this->error['image'][$key] = $this->language->get('error_image');
				}
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	public function store()
	{
		$result = array('status' => false);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') AND $this->validate())
		{
			$data_to_store = array(
				'teil_flexible_products' => $this->request->post['products'],
				'teil_flexible_positions' => $this->request->post['positions']
			);

			// Store module settings
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('teil_flexible_products_module', $data_to_store);

			$result['status'] = true;
		}

		// Errors
		if (isset($this->error['warning'])) {
			$result['error_warning'] = $this->error['warning'];
		} else {
			$result['error_warning'] = '';
		}

		if (isset($this->error['image'])) {
			$result['error_image'] = $this->error['image'];
		} else {
			$result['error_image'] = array();
		}

		echo json_encode($result); die();
	}


}