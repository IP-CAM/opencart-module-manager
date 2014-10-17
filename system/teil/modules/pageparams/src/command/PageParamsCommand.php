<?php 


use Teil\Core\CommandInterface;


/**
 * PageParams module command
 *
 */
class PageParamsCommand implements CommandInterface {
	
	private $module;


	public function __construct($module)
	{
		$this->module = $module;
	}


	public function execute()
	{
		$this->module->installDatabase();
	}


	public function undo()
	{
		$this->module->uninstallDatabase();
	}

	
}