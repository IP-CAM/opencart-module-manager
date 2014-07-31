<?php 


// $key string (is our case - 'menu') is required (defines if module is installed or not)
// MenuServiceProvider - class that will be resolved [required]
// `menu___v0.1` -> ___v[VERSION]
$GLOBALS['TeilServiceProviders']['menu___v0.2'] = 'MenuServiceProvider';


use Teil\Lib\ModuleCore;


/**
* Simple menu module
*/
class MenuModule extends ModuleCore 
{

	public function __construct($db)
	{
		parent::__construct(__FILE__, $db);
	}

	public function installDatabase()
	{
		// Get install sql
		$installSql = file_get_contents($this->modulePath . '/resources/install.sql');
		$unInstallSql = file_get_contents($this->modulePath . '/resources/uninstall.sql');

		$this->db->query($unInstallSql);
		$this->db->query($installSql);
	}


	public function uninstallDatabase()
	{
		// Get install sql
		$unInstallSql = file_get_contents($this->modulePath . '/resources/uninstall.sql');

		$this->db->query($unInstallSql);
	}

	
}