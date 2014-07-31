<?php namespace Teil\Core;


// The IoC container
// See: http://en.wikipedia.org/wiki/Inversion_of_control
class Container {


	/**
	 * List of all instances
	 *
	 */
	public $instances = array();
	

	/**
	 * Add new instance to the list
	 * 
	 * $abstract - is the name on the instance.
	 * Our instance will be avalible throught $this->make($abstract)
	 *
	 * $instance -is simply the instance
	 *
	 * @return void
	 */
	public function instance($abstract, $instance)
	{
		if (isset($this->instances[$abstract]))
		{
			throw new Exception("Instance {$abstract} already exists!");
		}

		$this->instances[$abstract] = $instance;
	}


	/**
	 * Get instance alias (simply return injected instance by its name)
	 *
	 * @return mixed
	 */
	public function make($abstract)
	{
		if (isset($this->instances[$abstract]))
		{
			return $this->instances[$abstract];
		}
		else
		{
			throw new \Exception("Instance {$abstract} does not exists!");
		}
	}
	
}