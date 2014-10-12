<?php 


class CategoriesSQL extends StringTemplate 
{
	
	
	public const $CATEGORIES_FILTER = "
		SELECT {{SELECT}}
		FROM `{{PREFIX}}product` AS `p`

		INNER JOIN `{{PREFIX}}product_attribute` AS `pa`
			ON
				`pa`.`product_id` = `p`.`product_id` 
				AND `pa`.`language_id` = '{{LANGUAGE_ID}}'

		{{JOINS}}

		WHERE {{FILTERS}}
	";
}



class Categories 
{
	

	/**
	 * Filter by categories
	 *
	 * @return string
	 */
	public function sql($params, $sql)
	{
		$sql = CategoriesSQL::make(
			CategoriesSQL::CATEGORIES_FILTER,
			array(
				'SELECT' => $sql['select'],
				'PREFIX' => DB_PREFIX,
				'LANGUAGE_ID' => 1,
				'JOINS' => $sql['tables'],
				'FILTERS' => $sql['filters']
			)
		);

		if ($this->) {
			# code...
		}

	}


}