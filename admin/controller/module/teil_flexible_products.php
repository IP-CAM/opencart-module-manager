<?php

class ControllerModuleTeilFlexibleProducts extends Controller {

	public function index() {
		$this->load->model('tool/image');

		$this->language->load('module/teil_flexible_products');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
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
		$this->load->model('setting/setting');
		$this->language->load('module/teil_flexible_products');
		
		$result = $this->model_setting_setting->getSetting('teil_flexible_products');

		// Get image
		// if (!file_exists(DIR_IMAGE . $result['image']) AND !is_file(DIR_IMAGE . $result['image']))
		// {
		// 	$result['image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		// }
		// else
		// {
		// 	$result['image'] = $this->model_tool_image->resize($result['image'], 100, 100);
		// }

		echo json_encode($result); die();
	}


	// Store module config in the database
	public function store()
	{
		$result = array('status' => false);

		if ($this->request->server['REQUEST_METHOD'] == 'POST')
		{
			// Store module settings
			// $this->load->model('setting/setting');
			// $this->model_setting_setting->editSetting('watermark', $this->request->post);

			$result['status'] = true;
		}

		echo json_encode($result); die();
	}

}