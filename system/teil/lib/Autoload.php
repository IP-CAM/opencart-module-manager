<?php namespace Teil\Lib;


/**
* Load all the files
*/
class Autoload 
{
    // Result files
    private $core_files = array();
    private $modules_files = array();
    
    private $base_dir;
    private $modules_dir;


    /**
     * Initilize autoloading
     *
     * @return array
     */
    static public function start()
    {
        $self = new self;
        $self->setDirs(DIR_SYSTEM . 'teil/');
        
        foreach ($self->getPaths() as $path)
        {
            if (file_exists($path))
            {
                require_once $path;
            }
        }
    }


    /**
     * Get all the avalible paths
     *
     * @return array
     */
    public function getPaths()
    {
        if ( ! is_dir($this->base_dir))
        {
            throw new \Exception("No such directory"); die();
        }

        return array_merge_recursive(
            $this->loadCore(),
            $this->loadModules()
        );
    }


    /**
     * Store dirs paths
     *
     * @return void
     */
    public function setDirs($dir)
    {
        $this->base_dir = $dir;
        $this->modules_dir = $dir . 'modules/';
    }


    /**
     * Get modules paths
     *
     * @return array
     */
    private function loadModules()
    {
        $results = $module_dirs = array();
        $dirs = scandir($this->modules_dir);

        // Check directory
        foreach ($dirs as $dir)
        {
            if ( ! preg_match("/^\.+$/i", $dir))
            {
                $module_dirs[] = $dir;
            }
        }

        // Include module files
        foreach ($module_dirs as $module_code)
        {
            $module_config = $this->modules_dir . $module_code . '/config.php';

            if (file_exists($module_config))
            {
                $config = require_once($this->modules_dir . $module_code . '/config.php');
                $config['autoload'] = $this->addPrefixesForModulePaths($config['autoload'], $module_code);

                $results = array_merge_recursive($results, $config['autoload']);
            }
        }

        return $results;
    }


    /**
     * Load core files
     *
     * @return array
     */
    private function loadCore()
    {
        return array(
            $this->base_dir . 'core/Container.php',
            $this->base_dir . 'core/App.php',
            $this->base_dir . 'core/Facade.php',
            $this->base_dir . 'core/interface/CommandInterface.php',
            $this->base_dir . 'core/interface/ModuleInterface.php',
            $this->base_dir . 'core/ProviderRepository.php',
            $this->base_dir . 'core/ServiceProvider.php',
            $this->base_dir . 'lib/ModuleCore.php',
            $this->base_dir . 'lib/ModuleInstaller.php',
            $this->base_dir . 'lib/Security.php',
            $this->base_dir . 'lib/TeilAutoload.php',
            $this->base_dir . 'lib/TeilDownloader.php'
        );
    }


    /**
     * Add prefixed such as 'modules/{module_name}/'
     *
     * @return array
     */
    private function addPrefixesForModulePaths($paths, $module_code)
    {
        foreach ($paths as & $path)
        {
            $path = $this->modules_dir . $module_code . '/' . $path;
        }

        return $paths;
    }

}