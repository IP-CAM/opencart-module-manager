<?php 


use Teil\Core\ServiceProvider;


class CheckoutServiceProvider extends ServiceProvider {


	public function __construct($app)
	{
		parent::__construct($app);

		$this->MODULE_CODE = 'checkout';
	}


	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		parent::register();

		// If module licence is ok, we will register it
		if ($this->MODULE_STATUS)
		{
		}
			$this->registerCheckoutBuilder();
	}


	/**
	 * Register the HTML builder instance.
	 *
	 * @return void
	 */
	protected function registerCheckoutBuilder()
	{
		$this->app->instance('checkout', new CheckoutFactory($this->app, $this->KEY_INFO));
	}


	/**
	 * Get the services provided by this provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('checkout');
	}


}