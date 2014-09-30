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





/*


SELECT main_po.product_option_id,
       main_po.product_id,
       main_po.option_id,
       main_po.option_value,
       o.type AS option_type,
       o.sort_order AS option_sort_order,
       od.name AS option_name,
       ov.option_value_id,
       ov.image AS option_value_image,
       ov.sort_order AS option_value_sort_order,
       ovd.name 
FROM product_option AS main
LEFT JOIN product_option AS main_po ON (main_po.product_id = main.product_id)
LEFT JOIN `option` AS o ON (o.option_id = main_po.option_id)
LEFT JOIN option_description AS od ON (od.option_id = main_po.option_id)
LEFT JOIN option_value AS ov ON (ov.option_id = main_po.option_id)
LEFT JOIN option_value_description AS ovd ON (ovd.option_value_id = ov.option_value_id)

WHERE ovd.language_id = 1
  AND main.product_id IN
    (
    

SELECT sub_ptc.product_id 
FROM product_to_category AS sub_ptc
LEFT JOIN product_attribute AS sub_pa ON (sub_pa.product_id = sub_ptc.product_id)
#LEFT JOIN product_option_value AS sub_pov ON (sub_ptc.product_id = sub_pov.product_id)
WHERE sub_ptc.category_id = 24
  AND sub_pa.text IN ("Description palm", "test1 palm + iphone + htc")
  group by sub_ptc.product_id
  HAVING COUNT(DISTINCT sub_pa.text) = 2)

AND ov.option_value_id IN
         (SELECT option_value_id
          FROM product_option_value
          WHERE product_id = main_po.product_id)
  
#GROUP BY ovd.option_value_id
ORDER BY main_po.option_id ASC

*/