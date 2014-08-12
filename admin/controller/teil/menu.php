<?php 
class ControllerTeilMenu extends Controller { 
 

    public function index() {
        $this->template = 'teil/modules/menu/index.tpl';
        
        // If form submited
        if ($this->request->server['REQUEST_METHOD'] == 'POST' 
            && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            // Perform some type of action
        }
        
        // DOM elements
        $this->document->setTitle('Menu module');
        
        // Breadcrumbs
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => 'Главная',
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => 'Teil',
            'href' => $this->url->link('teil/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => 'Menu module',
            'href' => $this->url->link('teil/menu', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        
        // Success and warning
        $this->getActionStatuses();

        $this->data['token'] = $this->session->data['token'];

        // Render
        $this->children = array(
            'common/header',
            'common/footer'
        );
        
        $this->response->setOutput($this->render());
	}


    /*
     * Get success and warning statuses
     */
    private function getActionStatuses()
    {
        // Success
        if (isset($this->session->data['success']))
        {
            $this->data['success'] = $this->session->data['success'];
            $this->session->data['success'] = '';
        }
        else
        {
            $this->data['success'] = '';
        }
        
        // Warning
        if (isset($this->session->data['warning']))
        {
            $this->data['warning'] = $this->session->data['warning'];
            $this->session->data['warning'] = '';
        }
        else
        {
            $this->data['warning'] = '';
        }
    }


}