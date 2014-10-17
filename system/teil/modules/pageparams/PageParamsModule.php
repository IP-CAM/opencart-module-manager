<?php 


// $key string (is our case - 'menu') is required (defines if module is installed or not)
// PageParamsServiceProvider - class that will be resolved [required]
// `menu___v0.1` -> ___v[VERSION]
$GLOBALS['TeilServiceProviders']['pageparams___v0.1'] = 'PageParamsServiceProvider';


use Teil\Lib\ModuleCore;


class PageParamsModule extends ModuleCore 
{

	public function __construct($db)
	{
		parent::__construct(__FILE__, $db);
	}

	public function installDatabase()
	{
		// Get install sql
		return false;
	}


	public function uninstallDatabase()
	{
		// Get install sql
		return false;
	}

	
}