<?php 


// $key string (is our case - 'menu') is required (defines if module is installed or not)
// MenuServiceProvider - class that will be resolved [required]
// `menu___v0.1` -> ___v[VERSION]
$GLOBALS['TeilServiceProviders']['checkout___v0.2'] = 'CheckoutServiceProvider';


use Teil\Lib\ModuleCore;


/**
* Simple checkout module
*/
class CheckoutModule extends ModuleCore 
{

	public function __construct($db)
	{
		parent::__construct(__FILE__, $db);
	}

	public function installDatabase()
	{
		// Get install sql
	}


	public function uninstallDatabase()
	{
		// Get install sql
	}

	
}