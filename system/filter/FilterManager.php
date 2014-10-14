<?php 


class FilterManager 
{


	public function info($db, $settings)
	{
		return array(
			'attributes' => $this->getAllAttributes($db, $settings)
		);
	}


	public function filter($db, $settings)
	{
		return array(
			'attributes' => $this->getFilteredAttributes($db, $settings)
		);
	}


	/**
	 * Get all the attributes
	 *
	 * @return array
	 */
	public function getAllAttributes($db, $settings)
	{
		// Get all the attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAllAttributes
		);

		// Fake setting
		$settings['attributes'] = array();

		$factory = new FilterFactory($builder);
		return $factory->make($settings)->resolve($db);
	}


	/**
	 * Get all the category attributes
	 *
	 * @return array
	 */
	public function getRealAttributes($db, $settings)
	{
		// Get all the attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);

		// Fake setting
		$settings['attributes'] = array();

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

		// Get filtered attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);

		$factory = new FilterFactory($builder);
		$filteredAttributes = $factory->make($settings)->resolve($db);

		// ///////////////////
		foreach($settings['attributes'] as $attribute_id => $attribute_values)
		{
			$copy = $settings;
			
			// list( $k ) = explode( '-', $key );
			
			unset($copy['attributes'][$attribute_id]);
			
			if ($copy['attributes'])
			{
				$tmp = $factory->make($copy)->resolve($db);

				// print_r($tmp); 
				
				if (isset($tmp[$attribute_id]))
				{
					$filteredAttributes = $filteredAttributes + array(
						$attribute_id => $tmp[$attribute_id]
					);
				}
			}
			
			else
			{
				if( isset( $attribute_groups[$attribute_id] ) ) {
					$filteredAttributes = $this->_replaceCounts( $filteredAttributes, array( $attribute_id => $attribute_groups[$attribute_id] ) );
				}
			}

		}

		return $filteredAttributes;
	}

	private function _replaceCounts( array $counts1, array $counts2 ) {
		foreach( $counts2 as $k1 => $v1 ) {
			foreach( $v1 as $k2 => $v2 ) {				
				$counts1[$k1][$k2] = $v2;
			}
		}
		
		return $counts1;
	}


}