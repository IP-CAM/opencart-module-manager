<?php namespace Teil\Core;


class App extends Container {


	function __construct() {

		// Get global providers
		$this->providers = $GLOBALS['TeilServiceProviders'];
	}


	/**
	 * Get the service provider repository instance.
	 *
	 */
	public function getProviderRepository()
	{
		return new ProviderRepository($this->providers);
	}

	
	/**
	 * Register a service provider with the application.
	 *
	 */
	public function register($provider, $options = array())
	{
		if (is_string($provider))
		{
			// Here are goint to create new instance of our module
			// Simple it works like - `new $provired`
			$provider = $this->resolveProviderClass($provider);
		}

		// After we successfully created new instance
		$provider->register();
	}


	/**
	 * Resolve a service provider instance from the class name.
	 *
	 */
	protected function resolveProviderClass($provider)
	{
		return new $provider($this);
	}


}