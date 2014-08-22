<?php namespace Teil\Core;


/**
 * Abstract class to every single service provider
 *
 */
abstract class ServiceProvider {

	protected $MODULE_CODE = NULL;
	protected $MODULE_STATUS = false;
	protected $KEY_INFO = NULL;


	/**
	 * The application instance.
	 *
	 */
	protected $app;

	
	/**
	 * Create a new service provider instance.
	 *
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}


	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {}


	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	protected function register() {
		// $this->checkLicense();
	}


	/**
	 * Check and store license info and module info
	 *
	 * @return mixed
	 */
	private function checkLicense()
	{
		// Validate module license key
		try {
			$validationResult = $this->app
				->make('security')
				->validate(
					$_SERVER['SERVER_NAME'],
					$this->MODULE_CODE
				);

			// Set module `is valid`
			$this->MODULE_STATUS = $validationResult['valid'];

			// Store module key info (credentials)
			if ($validationResult['valid'])
			{
				$this->KEY_INFO = $validationResult['info'];
			}
		} catch (\Exception $e) {
			print_r($e);
			echo "License file not found! Module name is - <b>" . $this->MODULE_CODE . "</b>";
		}
	}


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}


}