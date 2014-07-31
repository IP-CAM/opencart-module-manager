<?php namespace Teil\Core;


/**
 * Every module installation program should implement this interface
 *
 * @return void
 */
interface CommandInterface {
	public function execute();

	public function undo();
}