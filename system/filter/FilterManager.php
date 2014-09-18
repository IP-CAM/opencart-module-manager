<?php 



class FilterManager 
{
	

	function __construct($db)
	{
		$this->filterAttributes($db);
	}


	protected function filterAttributes($db)
	{
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
			// new FilterCaseOptions,
			// new FilterFormatterOptions
		);
		$factory = new FilterFactory($builder);

		$settings = array(
			'category_id' => 24,
			'attributes' => array(3),
			'options' => array(40, 43)
		);

		$same_options = $factory
			->make($settings)
			->resolve($db, $settings, 'options');

		print_r($same_options); die();
	}


}