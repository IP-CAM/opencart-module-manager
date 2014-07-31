<?php 
class ControllerTeilHome extends Controller { 
 
    public function index() {
        $this->template = 'teil/base/default.tpl';
        
        // If form submited
        if ($this->request->server['REQUEST_METHOD'] == 'POST' 
            && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            // Perform some type of action
        }
        
        // DOM elements
        $this->document->setTitle('Hello, world');
        
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


    /**
     * Download and install new module
     *
     * @return void
     */
    public function install()
    {
        $result = array(
            'status' => true
        );

        $module_code = $this->request->post['module_code'];

        $loader = new Teil\Lib\TeilDownloader($module_code);
        
        // Close write session
        // We do this to bring user of getting download progress on air
        // If not ---> user cant watch download progress at all
        session_write_close();

        // try
        // {
            $loader->load(
                function($module_code, $filename, $dir) 
                {
                    $moduleInstaller = new Teil\Lib\ModuleInstaller(
                        $this->db,
                        $module_code,
                        $filename,
                        $dir
                    );

                    $moduleInstaller->unzip();
                    $moduleInstaller->writeDemoKey();
                    $moduleInstaller->boot();
                }
            );
        // }
        // catch (Exception $e)
        // {
        //     $result['status'] = false;
        // }

        echo json_encode($result); die();
    }


    /**
     * Store license key
     *
     * @return void
     */
    public function store()
    {
        $result = array();

        if (isset($this->request->post['module_code'])) {
            $module_code = $this->request->post['module_code'];
        } else {
            $module_code = NULL;
        }

        if (isset($this->request->post['key'])) {
            $key = $this->request->post['key'];
        } else {
            $key = NULL;
        }

        if (empty($module_code) OR empty($key))
        {
            echo json_encode(array('status' => false)); die();
        }


        // Store key
        if (file_put_contents(DIR_TEIL_MODULES . $module_code . '/resources/license.dat', $key))
        {
            echo json_encode(array('status' => true)); die();
        }

        echo json_encode(array('status' => false)); die();
    }


    /**
     * Get progress of module that is currently loading
     *
     * @return mixed
     */
    public function getProgress()
    {
        $progress = 0;

        $module_code = $this->request->post['module_code'];
        $module_code_hashed = md5($module_code);
        $download_directory_hash = md5('downloads') . '/';

        $progress_file = DIR_SYSTEM . "teil/" . $download_directory_hash . $module_code_hashed . '.txt';

        if (file_exists($progress_file))
        {
            $progress = file_get_contents($progress_file);
        }

        echo $progress; die();
    }


    /**
     * Get list of already installed modules
     *
     * @return mixed
     */
    public function my()
    {
        $result = array();

        foreach ($GLOBALS['TeilServiceProviders'] as $module_code_version => $module_provider)
        {
            $module_code = preg_replace("/___v.+/i", "", $module_code_version);
            $version = preg_replace("/.+___v/i", "", $module_code_version);
            $key = file_get_contents(DIR_TEIL_MODULES . $module_code . '/resources/license.dat');

            $result[$module_code] = array(
                'provider'  => $module_provider,
                'key'       => $key,
                'version'   => $version
            );
        }

        echo json_encode($result); die();
    }


    /**
     * Remove module
     *
     * @return void
     */
    public function remove()
    {
        $result = array(
            'status' => true
        );

        $module_code = $this->request->post['module_code'];

        $moduleInstaller = new Teil\Lib\ModuleInstaller(
            $this->db,
            $module_code,
            NULL, NULL
        );
        
        $moduleInstaller->remove();

        echo json_encode($result); die();
    }


    /**
     * Perform self update
     *
     * @return void
     */
    public function selfupdate()
    {
        $result = array(
            'status' => true
        );

        $loader = new Teil\Lib\TeilDownloader('self');
        
        // Close write session
        // We do this to bring user of getting download progress on air
        // If not ---> user cant watch download progress at all
        session_write_close();

        // try
        // {
            $loader->load(
                function($module_code, $filename, $dir) 
                {
                    $moduleInstaller = new Teil\Lib\ModuleInstaller(
                        $this->db,
                        $module_code,
                        $filename,
                        $dir
                    );

                    $moduleInstaller->unzip();
                }
            );
        // }
        // catch (Exception $e)
        // {
        //     $result['status'] = false;
        // }

        echo json_encode($result); die();
    }


}