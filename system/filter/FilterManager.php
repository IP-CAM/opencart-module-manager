<?php 



class FilterManager 
{


	public function filter($db, $settings)
	{
		return array(
			'attributes' => $this->filterAttributes($db, $settings),
			'options' => $this->filterOptions($db, $settings)
		);
	}


	protected function filterAttributes($db, $settings)
	{
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);
		$factory = new FilterFactory($builder);

		$options = $factory
			->make($settings)
			->resolve($db, $settings, 'attributes', 'attr_id');

		$formatter = new FilterFormatterAttributes;
		$options = $formatter->make($options);

		return $options;
	}


	protected function filterOptions($db, $settings)
	{
		$builder = new FilterBuilder(
			new FilterCaseOptions,
			new FilterFormatterOptions
		);
		$factory = new FilterFactory($builder);

		$attributes = $factory
			->make($settings)
			->resolve($db, $settings, 'options', 'option_value_id');

		$formatter = new FilterFormatterOptions;
		$attributes = $formatter->make($attributes);

		return $attributes;
	}


}