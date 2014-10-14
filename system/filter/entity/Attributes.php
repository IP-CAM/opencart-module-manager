<?php 


class AttributesSQL extends StringTemplate
{


	const ATTRIBUTES_RESULT_FILTER = "
		SELECT {{SELECT}} 
		FROM({{FROM}}) AS `tmp` 
		{{FILTERS}} 
		GROUP BY 
			`text`, `attribute_id` 
	";


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


	const ALL_ATTRIBUTES = "
		SELECT
			`a`.`attribute_id`,
			REPLACE(REPLACE(TRIM(pa.text), '\r', ''), '\n', '') AS `txt`,
			`ad`.`name`,
			`agd`.`name` AS `gname`,
			`agd`.`attribute_group_id`
		FROM
			`{{PREFIX}}product` AS `p`
		INNER JOIN
			`{{PREFIX}}product_to_store` AS `pts`
		ON
			`p`.`product_id` = `pts`.`product_id` AND `pts`.`store_id` = {{STORE_ID}}
		INNER JOIN
			`{{PREFIX}}product_attribute` AS `pa`
		ON
			`p`.`product_id` = `pa`.`product_id` AND `pa`.`language_id` = {{LANGUAGE_ID}}
		INNER JOIN
			`{{PREFIX}}attribute` AS `a`
		ON
			`a`.`attribute_id` = `pa`.`attribute_id`
		INNER JOIN
			`{{PREFIX}}attribute_description` AS `ad`
		ON
			`ad`.`attribute_id` = `a`.`attribute_id` AND `ad`.`language_id` = {{LANGUAGE_ID}}
		INNER JOIN
			`{{PREFIX}}attribute_group` AS `ag`
		ON
			`ag`.`attribute_group_id` = `a`.`attribute_group_id`
		INNER JOIN
			`{{PREFIX}}attribute_group_description` AS `agd`
		ON
			`agd`.`attribute_group_id` = `ag`.`attribute_group_id` AND `agd`.`language_id` = {{LANGUAGE_ID}} 
		 
		{{JOIN}} 
		{{WHERE}} 
		GROUP BY 
			`txt`, `pa`.`attribute_id` 
		HAVING 
			`txt` != '' 
		ORDER BY 
			`txt`
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

		return $this->_filters;
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
			$filter[] = AttributesSQL::make(
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
			implode(' AND ', $filter)
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

		$filter = AttributesSQL::make(
			AttributesSQL::ATTRIBUTE_IDS_FILTER,
			array(
				"ATTRIBUTES" => implode(',', $ids)
			)
		);

		$this->addFilter('attribute_ids_filter', $filter);
	}


}