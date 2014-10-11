<?php 


class AttributesSQL 
{
	

	const ATTRIBUTE_IDS_FILTER = "
		`tmp`.`attribute_id` NOT IN({{ATTRIBUTES}})
	";
	
	/**
	 * Sql to filter by attributes
	 */
	const ATTRIBUTE_FILTER = "
		`product_id` IN( 
			SELECT 
				`product_id` 
			FROM 
				`{{PREFIX}}product_attribute` 
			WHERE 
				REPLACE(REPLACE(TRIM(`text`), '\r', ''), '\n', '') IN({{ATTRIBUTES}}) AND
				`language_id` = {{LANGUAGE_ID}} AND
				`attribute_id` = {{ATTRIBUTE_ID}}
		)
	";


}



class Attributes 
{
	
	
	protected $_original = array();
	protected $_filters = array();


	/**
	 * Convert inserted filters to sql
	 *
	 * @return string
	 */
	public function sql($attributes)
	{
		$this->_original = $attributes;

		$this->filterIDs();
		$this->filterAttributes();

		print_r($this->_filters); die();
	}


	/**
	 * Store filter for attributes
	 *
	 * @return void
	 */
	protected function addFilter($key, $filter)
	{
		$this->_filters[$key] = $filter;
	}


	/**
	 * Create sql by given template with attributes
	 *
	 * @return string
	 */
	protected function createFilter($template, $setting = array())
	{
		foreach ($setting as $key => $value)
		{
			$template = str_replace('{{' . $key . '}}', $value, $template);
		}

		return $template;
	}


	/**
	 * Create attribute text filter
	 *
	 * @return void
	 */
	public function filterAttributes()
	{
		if ( ! $this->_original) return false;

		// Create attribute filter
		$filter = array();

		foreach($this->_original as $attribute_id => $attribute)
		{
			$filter[] = $this->createFilter(
				AttributesSQL::ATTRIBUTE_FILTER,
				array(
					"PREFIX" => DB_PREFIX,
					"ATTRIBUTES" => teil_quote_implode(',', $attribute),
					"LANGUAGE_ID" => 1,
					"ATTRIBUTE_ID" => $attribute_id
				)
			);

		}
		
		// Store filter
		$this->addFilter(
			'attribute_text_filter',
			implode(' AND ', $filter )
		);
	}


	/**
	 * Filter by attribute IDs
	 *
	 * @return string
	 */
	protected function filterIDs()
	{
		$ids = array_keys($this->_original);

		if ( ! $ids) return false;

		$filter = $this->createFilter(
			AttributesSQL::ATTRIBUTE_IDS_FILTER,
			array(
				"ATTRIBUTES" => implode(',', $ids)
			)
		);

		$this->addFilter('attribute_ids_filter', $filter);
	}


}