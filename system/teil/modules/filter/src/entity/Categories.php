<?php


class CategoriesSQL extends StringTemplate
{


	const CATEGORIES_FILTER = "
		SELECT {{SELECT}}
		FROM `{{PREFIX}}product` AS `p`

		INNER JOIN `{{PREFIX}}product_attribute` AS `pa`
			ON
				`pa`.`product_id` = `p`.`product_id`
				AND `pa`.`language_id` = '{{LANGUAGE_ID}}'

		{{JOINS}}

		WHERE {{FILTERS}}
	";


	const CATEGORIES_TO_PRODUCT_FILTER = "
		SELECT
			`tmp`.*
		FROM
			( {{INNER_TABLE}} ) AS `tmp`
		{{PRODUCTS_TABLE}} 
		{{CATEGORIES_PATHS_TABLE}} 
		{{CATEGORIES}} 
	";


	const PRODUCT_TO_CATEGORY_RELATION = "
		INNER JOIN `{{PREFIX}}product_to_category` AS `mf_p2c`
		ON `mf_p2c`.`product_id` = `p`.`product_id`
	";

	const CATEGORY_TO_CATEGORY_RELATION = "
		INNER JOIN `{{PREFIX}}category_path` AS `mf_cp`
		ON `mf_cp`.`category_id` = `mf_p2c`.`category_id`
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
		$inner_table_result = CategoriesSQL::make(
			CategoriesSQL::CATEGORIES_FILTER,
			array(
				'SELECT' => implode(',', $sql['select']),
				'PREFIX' => DB_PREFIX,
				'LANGUAGE_ID' => 1,
				'JOINS' => $sql['joins'],
				'FILTERS' => implode(' AND ', $sql['filters'])
			)
		);

		// Here we are checking if we should filter results by categories
		if ( ! $this->hasCategories($params)) return $inner_table_result;

		return CategoriesSQL::make(
			CategoriesSQL::CATEGORIES_TO_PRODUCT_FILTER,
			array(
				'INNER_TABLE' => $inner_table_result,
				'PRODUCTS_TABLE' => $this->createProductToCategoryRelation(),
				'CATEGORIES_PATHS_TABLE' => $this->createCategoryToCategoryRelation(),
				'CATEGORIES' => $this->filterCategories($params['categories'])
			)
		);
	}


	/**
	 * Check if we need to filter by categories
	 *
	 * @return bool
	 */
	private function hasCategories($params)
	{
		if (empty($params['categories']))
		{
			return false;
		}

		return true;
	}


	/**
	 * Filter categories (convert them to sql)
	 *
	 * @return string
	 */
	private function filterCategories($join = ' WHERE ', $categories)
	{
		$ids = array();
		$sql = array();
		
		foreach($categories as $cat1)
		{
			foreach( $cat1 as $cat2 )
			{
				$ids[] = end( $cat2 );
			}
		}
		
		$ids = implode( ',', $ids );
		
		$sql[] = '`mf_cp`.`path_id` IN(' . $ids . ')';
		
		$sql = $join . implode( ' AND ', $sql );
		
		return $sql;
	}


	/**
	 * Join product to category table
	 *
	 * @return string
	 */
	private function createProductToCategoryRelation() {
		return CategoriesSQL::make(
			CategoriesSQL::PRODUCT_TO_CATEGORY_RELATION,
			array('PREFIX' => DB_PREFIX)
		);
	}


	/**
	 * Join category paths table
	 *
	 * @return string
	 */
	private function createCategoryToCategoryRelation( $alias = 'mf_cp', $on = 'mf_p2c' ) {
		return CategoriesSQL::make(
			CategoriesSQL::CATEGORY_TO_CATEGORY_RELATION,
			array('PREFIX' => DB_PREFIX)
		);
	}


}