<?php 



class FilterManager 
{


	public function filter($db, $settings)
	{
		return array(
			'attributes' => $this->getCategoryAttributes($db, $settings),
			'options' => $this->getCategoryOptions($db, $settings)
		);
	}


	/**
	 * Get all category attributes
	 *
	 * @return array
	 */
	protected function getCategoryAttributes($db, $settings)
	{
		// Get all category attributes
		$category_builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);
		$factory = new FilterFactory($category_builder);

		$fake_settings = array(
			'category_id' => $settings['category_id'],
			'attributes' => array(),
			'options' => array()
		);

		$attributes = $factory
			->make($fake_settings)
			->resolve($db, $fake_settings, 'attributes', 'attr_text');

		// echo "\n\nOriginal attributes:\n";
		// print_r($attributes);





		// Get filtered category attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);
		$factory = new FilterFactory($builder);

		$category_filtered_attributes = $factory
			->make($settings)->resolve($db, $settings, 'attributes', 'attr_text');

		// echo "\n\nFiltered attributes:\n";
		// print_r($category_filtered_attributes);

		// Composer filter params
		$filter_composer = new FilterComposer('attr_text');

		$attributes = $filter_composer->compose(
			$attributes, 
			$category_filtered_attributes, 
			$settings['attributes']
		);

		// echo "\n\nComposed attributes:\n";
		// print_r($attributes); die();



		// Format
		$formatter = new FilterFormatterAttributes;
		$attributes = $formatter->make($attributes);

		return $attributes;
	}



	/**
	 * Get all category attributes
	 *
	 * @return array
	 */
	protected function getCategoryOptions($db, $settings)
	{
		// Get all category attributes
		$options_builder = new FilterBuilder(
			new FilterCaseOptions,
			new FilterFormatterOptions
		);
		$factory = new FilterFactory($options_builder);

		$fake_settings = array(
			'category_id' => $settings['category_id'],
			'attributes' => array(),
			'options' => array()
		);

		$options = $factory
			->make($fake_settings)
			->resolve($db, $fake_settings, 'options', 'option_value_id');

		// echo "\n\nOriginal options:\n";
		// print_r($options);





		// Get filtered category options
		$builder = new FilterBuilder(
			new FilterCaseOptions,
			new FilterFormatterOptions
		);
		$factory = new FilterFactory($builder);

		$category_filtered_options = $factory
			->make($settings)->resolve($db, $settings, 'options', 'option_value_id');

		// echo "\n\nFiltered options:\n";
		// print_r($category_filtered_options);

		// Composer filter params
		$filter_composer = new FilterComposer('option_value_id');

		$options = $filter_composer->compose(
			$options, 
			$category_filtered_options, 
			$settings['options']
		);

		// echo "\n\nComposed options:\n";
		// print_r($options); die();



		// Format
		$formatter = new FilterFormatterOptions;
		$options = $formatter->make($options);

		return $options;
	}


	/**
	 * Filter attributes
	 *
	 * @return mixed
	 */
	protected function filterAttributes($db, $settings)
	{
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);
		$factory = new FilterFactory($builder);

		$attributes = $factory
			->make($settings)
			->resolve($db, $settings, 'attributes', 'attr_id');

		$formatter = new FilterFormatterAttributes;
		$attributes = $formatter->make($attributes);

		return $attributes;
	}


	protected function filterOptions($db, $settings)
	{
		$builder = new FilterBuilder(
			new FilterCaseOptions,
			new FilterFormatterOptions
		);
		$factory = new FilterFactory($builder);

		$options = $factory
			->make($settings)
			->resolve($db, $settings, 'options', 'option_value_id');

		$formatter = new FilterFormatterOptions;
		$options = $formatter->make($options);

		return $options;
	}


}