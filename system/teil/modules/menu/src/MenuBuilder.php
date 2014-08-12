<?php 


class MenuBuilder 
{
	private $app;
	private $key_info;


	public function __construct(App $app, $key_info)
	{
		$this->app = $app;
		$this->key_info = $key_info;
	}


	public function test()
	{
		// $this->app->make('registry')->get('db')->query("select * from product");
		print_r($this->key_info); die();
		
		echo "testing menu module";
	}

}