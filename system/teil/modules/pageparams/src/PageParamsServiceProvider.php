<?php 


use Teil\Core\ServiceProvider;


class PageParamsServiceProvider extends ServiceProvider {


	public function __construct($app)
	{
		parent::__construct($app);

		$this->MODULE_CODE = 'pageparams';
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
			$this->registerPageParamsBuilder();
		}
	}


	/**
	 * Register the HTML builder instance.
	 *
	 * @return void
	 */
	protected function registerPageParamsBuilder()
	{
		$this->app->instance('PageParams', new PageParamsBuilder($this->app, $this->KEY_INFO));
	}


	/**
	 * Get the services provided by this provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('pageparams');
	}


}