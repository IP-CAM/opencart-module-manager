<?php namespace Teil\Core;


/**
 * Every module should implement this intrface
 *
 * @return void
 */
interface ModuleInterface {
	public function installDatabase();

	public function uninstallDatabase();
}