<?php 


// $key string (is our case - 'menu') is required (defines if module is installed or not)
// MenuServiceProvider - class that will be resolved [required]
// `menu___v0.1` -> ___v[VERSION]
$GLOBALS['TeilServiceProviders']['filter___v0.1'] = 'FilterServiceProvider';


use Teil\Lib\ModuleCore;



class FilterModule extends ModuleCore 
{

	public function __construct($db)
	{
		parent::__construct(__FILE__, $db);
	}

	public function installDatabase()
	{
		return false;
	}


	public function uninstallDatabase()
	{
		return false;
	}

	
}