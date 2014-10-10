<?php 


class FilterManager 
{


	public function filter($db, $settings)
	{
		return array(
			'attributes' => $this->getFilteredAttributes($db, $settings),
			// 'attributes' => $this->getFilteredAttributes($db, $settings),
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
		
		return $factory->make($settings)->resolve($db);
	}


	/**
	 * Get all the filtered attributes
	 *
	 * @return array
	 */
	public function getFilteredAttributes($db, $settings)
	{
		$attribute_groups = $this->getRealAttributes($db, $settings);

		Get filtered attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterFilteredAttributes
		);

		$factory = new FilterFactory($builder);
		
		$filtered_attributes = $factory->make($settings)->resolve($db);
		

		return $attribute_groups;
	}


}