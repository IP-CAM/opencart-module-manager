<?php 


use Teil\Core\CommandInterface;


/**
 * Filter module command
 *
 * @return void
 */
class FilterCommand implements CommandInterface {
	
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