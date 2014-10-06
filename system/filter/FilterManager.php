<?php 



class FilterManager 
{


	public function filter($db, $settings)
	{
		return array(
			'attributes' => $this->getFilteredAttributes($db, $settings),
			// 'options' => $this->getCategoryOptions($db, $settings)
		);
	}


	/**
	 * Get all the category attributes
	 *
	 * @return array
	 */
	public function getRealAttributes($db, $settings)
	{
		// Get all category attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributesGroup,
			new FilterFormatterAttributes
		);
		$factory = new FilterFactory($builder);
		$attributes = $factory->make(array(
				'category_id' => $settings['category_id'],
				'attributes' => array(),
				'options' => array()
			))
			->resolve($db);

		// Convert attributes to groups
		$converter = new AttributeGroupConverter;
		$result = $converter->toGroup($attributes);

		return $result;
	}


	/**
	 * Get all the filtered attributes
	 *
	 * @return array
	 */
	public function getFilteredAttributes($db, $settings)
	{
		$attribute_groups = $this->getRealAttributes($db, $settings);

		// Get filtered attributes
		foreach ($attribute_groups as & $group)
		{
			$group['filters'] = array();

			foreach ($settings['attributes'] as $setting_attribute_group)
			{
				if ($group['attr_id'] != $setting_attribute_group['attr_group_id'])
				{
					$group['filters'] = array_merge($group['filters'], $setting_attribute_group['items']);
				}
			}

			$filter_settings = array(
				'category_id' => $settings['category_id'],
				'attributes' => $group,
				'options' => $settings['options']
			);

			$builder = new FilterBuilder(
				new FilterCaseAttributes,
				new FilterFormatterAttributes
			);
			$factory = new FilterFactory($builder);

			$converter = new AttributeGroupConverter;

			$group['attributes'] = $converter->pluckAttributeTextArray(
				$factory->make($filter_settings)->resolve($db)
			);
		}

		$converter = new AttributeGroupConverter;
		$result = $converter->compose($attribute_groups, $settings['attributes']);

		return $result;
	}



























	// /**
	//  * Get all category attributes
	//  *
	//  * @return array
	//  */
	// protected function getCategoryAttributes($db, $settings)
	// {
	// 	// Get all category attributes
	// 	$builder = new FilterBuilder(
	// 		new FilterCaseAttributesGroup,
	// 		new FilterFormatterAttributes
	// 	);
	// 	$factory = new FilterFactory($builder);

	// 	$fake_settings = array(
	// 		'category_id' => $settings['category_id'],
	// 		'attributes' => array(),
	// 		'options' => array()
	// 	);

	// 	$attribute_groups = $factory->make($fake_settings)->resolve($db);

	// 	// echo "\n\nOriginal attributes:\n";
	// 	// print_r($attribute_groups);





	// 	// Get attribute groups attributes
	// 	$res = array();

	// 	foreach ($attribute_groups as & $group)
	// 	{
	// 		$group['filters'] = array();

	// 		foreach ($settings['attributes'] as $setting_attribute_group)
	// 		{
	// 			if ($group['attr_id'] != $setting_attribute_group['attr_group_id'])
	// 			{
	// 				$group['filters'] = array_merge($group['filters'], $setting_attribute_group['items']);
	// 			}
	// 		}

	// 		$filter_settings = array(
	// 			'category_id' => $settings['category_id'],
	// 			'attributes' => $group,
	// 			'options' => $settings['options']
	// 		);

	// 		$builder = new FilterBuilder(
	// 			new FilterCaseAttributes,
	// 			new FilterFormatterAttributes
	// 		);
	// 		$factory = new FilterFactory($builder);

	// 		$group['attributes'] = $factory
	// 			->make($filter_settings)
	// 			->resolve($db);
	// 	}

	// 	// print_r($attribute_groups); 

	// 	// die();

	// 	return $attribute_groups;
	// }
	


	// /**
	//  * Get all category attributes
	//  *
	//  * @return array
	//  */
	// protected function getCategoryOptions($db, $settings)
	// {
	// 	// Get all category attributes
	// 	$options_builder = new FilterBuilder(
	// 		new FilterCaseOptions,
	// 		new FilterFormatterOptions
	// 	);
	// 	$factory = new FilterFactory($options_builder);

	// 	$fake_settings = array(
	// 		'category_id' => $settings['category_id'],
	// 		'attributes' => array(),
	// 		'options' => array()
	// 	);

	// 	$options = $factory
	// 		->make($fake_settings)
	// 		->resolve($db, $fake_settings, 'options', 'option_value_id');

	// 	// echo "\n\nOriginal options:\n";
	// 	// print_r($options);





	// 	// Get filtered category options
	// 	$builder = new FilterBuilder(
	// 		new FilterCaseOptions,
	// 		new FilterFormatterOptions
	// 	);
	// 	$factory = new FilterFactory($builder);

	// 	$category_filtered_options = $factory
	// 		->make($settings)->resolve($db, $settings, 'options', 'option_value_id');

	// 	// echo "\n\nFiltered options:\n";
	// 	// print_r($category_filtered_options);

	// 	// Composer filter params
	// 	$filter_composer = new FilterComposer('option_value_id');

	// 	$options = $filter_composer->compose(
	// 		$options, 
	// 		$category_filtered_options, 
	// 		$settings['options']
	// 	);

	// 	// echo "\n\nComposed options:\n";
	// 	// print_r($options); die();



	// 	// Format
	// 	$formatter = new FilterFormatterOptions;
	// 	$options = $formatter->make($options);

	// 	return $options;
	// }


	// /**
	//  * Filter attributes
	//  *
	//  * @return mixed
	//  */
	// protected function filterAttributes($db, $settings)
	// {
	// 	$builder = new FilterBuilder(
	// 		new FilterCaseAttributes,
	// 		new FilterFormatterAttributes
	// 	);
	// 	$factory = new FilterFactory($builder);

	// 	$attributes = $factory
	// 		->make($settings)
	// 		->resolve($db, $settings, 'attributes', 'attr_id');

	// 	$formatter = new FilterFormatterAttributes;
	// 	$attributes = $formatter->make($attributes);

	// 	return $attributes;
	// }


	// protected function filterOptions($db, $settings)
	// {
	// 	$builder = new FilterBuilder(
	// 		new FilterCaseOptions,
	// 		new FilterFormatterOptions
	// 	);
	// 	$factory = new FilterFactory($builder);

	// 	$options = $factory
	// 		->make($settings)
	// 		->resolve($db, $settings, 'options', 'option_value_id');

	// 	$formatter = new FilterFormatterOptions;
	// 	$options = $formatter->make($options);

	// 	return $options;
	// }


}