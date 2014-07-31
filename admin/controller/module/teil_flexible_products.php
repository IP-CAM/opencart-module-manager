<?php

class ControllerModuleTeilFlexibleProducts extends Controller {

	public function index()
	{
		$this->load->model('tool/image');

		$this->language->load('module/teil_flexible_products');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			// Store module settings
		}

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

		// Local vars
		$this->data['action'] = $this->url->link('module/watermark', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		$this->load->model('catalog/product');

		$this->data['modules'] = array();

		if (isset($this->request->post['teil_flexible_products_module']))
		{
			$this->data['modules'] = $this->request->post['teil_flexible_products_module'];
		} elseif ($this->config->get('teil_flexible_products_module'))
		{ 
			$this->data['modules'] = $this->config->get('teil_flexible_products_module');
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

		$product_ids = $this->model_setting_setting->getSetting('teil_flexible_products_module');
		$products = explode(',', $product_ids['products']);
		
		$result = array(
			'products' => array(),
			'selected_ids' => $product_ids['products']
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

}