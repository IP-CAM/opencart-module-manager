<?php 


class FilterManager 
{

	private $app;
	private $key_info;

	private $db;


	public function __construct(Teil\Core\App $app, $key_info)
	{
		$this->app = $app;
		$this->key_info = $key_info;
	}


	/**
	 * Get list of all the attributes, options etc.
	 *
	 * @return array
	 */
	public function info($settings)
	{
		$this->db = $this->app->make('registry')->get('db');

		// Get categories
		// $this->app->make('registry')->get('load')->model('catalog/category');

		// $categories = $this->app->make('registry')->get('model_catalog_category')->getCategories();

		// print_r($categories); die();

		return array(
			'attributes' => $this->getAllAttributes($settings)
		);
	}


	public function filter($settings)
	{
		$this->db = $this->app->make('registry')->get('db');

		return array(
			'attributes' => $this->getFilteredAttributes($settings)
		);
	}


	/**
	 * Get all the attributes
	 *
	 * @return array
	 */
	public function getAllAttributes($settings)
	{
		// Get all the attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAllAttributes
		);

		// Fake setting
		$settings['attributes'] = array();

		$factory = new FilterFactory($builder);
		return $factory->make($settings)->resolve($this->db);
	}


	/**
	 * Get all the category attributes
	 *
	 * @return array
	 */
	public function getRealAttributes($settings)
	{
		// Get all the attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);

		// Fake setting
		$settings['attributes'] = array();

		$factory = new FilterFactory($builder);
		return $factory->make($settings)->resolve($this->db);
	}


	/**
	 * Get all the filtered attributes
	 *
	 * @return array
	 */
	public function getFilteredAttributes($settings)
	{
		$attribute_groups = $this->getRealAttributes($settings);

		// Get filtered attributes
		$builder = new FilterBuilder(
			new FilterCaseAttributes,
			new FilterFormatterAttributes
		);

		$factory = new FilterFactory($builder);
		$filteredAttributes = $factory->make($settings)->resolve($this->db);

		// ///////////////////
		foreach($settings['attributes'] as $attribute_id => $attribute_values)
		{
			$copy = $settings;
			
			// list($k ) = explode( '-', $key);
			
			unset($copy['attributes'][$attribute_id]);
			
			if ($copy['attributes'])
			{
				$tmp = $factory->make($copy)->resolve($this->db);

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
				if ( isset($attribute_groups[$attribute_id]))
				{
					$filteredAttributes = $this->_replaceCounts($filteredAttributes, array($attribute_id => $attribute_groups[$attribute_id]));
				}
			}

		}

		return $filteredAttributes;
	}

	private function _replaceCounts( array $counts1, array $counts2 ) {
		foreach($counts2 as $k1 => $v1 ) {
			foreach($v1 as $k2 => $v2 ) {				
				$counts1[$k1][$k2] = $v2;
			}
		}
		
		return $counts1;
	}


}