<?php

class ControllerModuleWatermark extends Controller {

	public function index() {
		$this->load->model('tool/image');

		$this->language->load('module/watermark');
		$this->document->setTitle($this->language->get('text_title'));

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
			'text'      => $this->language->get('text_title'),
			'href'      => $this->url->link('module/account', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		// Language
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_home'] = $this->language->get('text_home');
		$this->data['text_module'] = $this->language->get('text_module');
		$this->data['text_title'] = $this->language->get('text_title');
		$this->data['text_save'] = $this->language->get('text_save');
		$this->data['text_cancel'] = $this->language->get('text_cancel');
		$this->data['text_insert_image'] = $this->language->get('text_insert_image');
		$this->data['text_new_image'] = $this->language->get('text_new_image');
		$this->data['text_clear_image'] = $this->language->get('text_clear_image');
		$this->data['text_image_width'] = $this->language->get('text_image_width');
		$this->data['text_image_height'] = $this->language->get('text_image_height');
		$this->data['text_offset_controll'] = $this->language->get('text_offset_controll');
		$this->data['text_top'] = $this->language->get('text_top');
		$this->data['text_bottom'] = $this->language->get('text_bottom');
		$this->data['text_right'] = $this->language->get('text_right');
		$this->data['text_left'] = $this->language->get('text_left');
		$this->data['text_live_preview'] = $this->language->get('text_live_preview');

		$this->data['positions'] = array(
			'top-left' => $this->language->get('text_top_left'),
			'top-center' => $this->language->get('text_top_center'),
			'top-right' => $this->language->get('text_top_right'),
			'middle-left' => $this->language->get('text_middle_left'),
			'middle-center' => $this->language->get('text_middle_center'),
			'middle-right' => $this->language->get('text_middle_right'),
			'bottom-left' => $this->language->get('text_bottom_left'),
			'bottom-center' => $this->language->get('text_bottom_center'),
			'bottom-right' => $this->language->get('text_bottom_right'),
		);

		// Local vars
		$this->data['action'] = $this->url->link('module/watermark', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		$this->template = 'teil/module/watermark.tpl';
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
		$result = $this->model_setting_setting->getSetting('watermark');

		// Get image
		if (!file_exists(DIR_IMAGE . $result['image']) AND !is_file(DIR_IMAGE . $result['image']))
		{
			$result['image'] = "/image/no_image.jpg";
			$result['editor_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}
		else
		{
			$result['editor_image'] = $this->model_tool_image->resize($result['image'], 100, 100);
		}

		// Get positions
		$this->language->load('module/watermark');

		$result['positions'] = array(
			array(
				'key' => 'top-left',
				'value' => $this->language->get('text_top_left')
			), 
			array(
				'key' => 'top-center',
				'value' => $this->language->get('text_top_center')
			), 
			array(
				'key' => 'top-right',
				'value' => $this->language->get('text_top_right')
			), 
			array(
				'key' => 'middle-left',
				'value' => $this->language->get('text_middle_left')
			), 
			array(
				'key' => 'middle-center',
				'value' => $this->language->get('text_middle_center')
			), 
			array(
				'key' => 'middle-right',
				'value' => $this->language->get('text_middle_right')
			), 
			array(
				'key' => 'bottom-left',
				'value' => $this->language->get('text_bottom_left')
			), 
			array(
				'key' => 'bottom-center',
				'value' => $this->language->get('text_bottom_center')
			), 
			array(
				'key' => 'bottom-right',
				'value' => $this->language->get('text_bottom_right')
			)
		);

		echo json_encode($result); die();
	}


	// Store module config in the database
	public function store()
	{
		$result = array('status' => false);

		if ($this->request->server['REQUEST_METHOD'] == 'POST')
		{
			// Store module settings
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('watermark', $this->request->post);

			$result['status'] = true;
		}

		echo json_encode($result); die();
	}

}