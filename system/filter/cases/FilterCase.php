<?php 


class FilterCase implements FilterCaseInterface 
{
	

	protected $where;
	protected $_select = array(
		'`pa`.`attribute_id`',
		'`p`.`product_id`',
		'`pa`.`text`',
		'`ad`.`name`'
	);
	protected $_resultSelect = array(
		'`text`', 
		'`attribute_id`', 
		'`name`', 
		'COUNT( DISTINCT `tmp`.`product_id` ) AS `total`'
	);


	/**
	 * Get columns to select from
	 *
	 * @return array
	 */
	protected function getSelect()
	{
		return $this->_select;
	}


	/**
	 * Return SQL to fetch filtered attributes
	 *
	 * @return void
	 */
	public function setAttributes($attributes = array())
	{
		$_attributes = new Attributes;
		$_categories = new Categories;

		// Get attribute filters
		$attributeFilters = $_attributes->sql($attributes);

		// Get categories AND if needed filter by categories
		$categoriesResult = $_categories->sql(
			array(),
			array(
				'select' => $this->getSelect(),
				'joins' => $this->getBasicJoins(),
				'filters' => $this->getBasicFilters()
			)
		);

		// Compose attribute select query
		return AttributesSQL::make(
			AttributesSQL::ATTRIBUTES_RESULT_FILTER,
			array(
				'SELECT' => implode(',', $this->_resultSelect),
				'FROM' => $categoriesResult,
				'FILTERS' => array(
					'PREFIX' => ' WHERE ',
					'VALUE' => implode(' AND ', $attributeFilters)
				)
			)
		);
	}


	protected function getBasicJoins( array $skip = array() ) {
		$sql = '';
		
		if( ! in_array( 'p2s', $skip ) ) {
			$sql .= "
				INNER JOIN
					`" . DB_PREFIX . "product_to_store` AS `p2s`
				ON
					`p2s`.`product_id` = `p`.`product_id` AND `p2s`.`store_id` = " . (int) 0 . "
			";
		}


		if( ! in_array( 'ad', $skip ) ) {
			$sql .= "
				INNER JOIN
					`" . DB_PREFIX . "attribute_description` AS `ad`
				ON
					`ad`.`attribute_id` = `pa`.`attribute_id` AND `ad`.`language_id` = " . (int) 1 . "
			";
		}

		
		if( ( ! empty( $this->_data['filter_name'] ) || ! empty( $this->_data['filter_tag'] ) ) && ! in_array( 'pd', $skip ) ) {
			$sql .= "
				INNER JOIN
					`" . DB_PREFIX . "product_description` AS `pd`
				ON
					`pd`.`product_id` = `p`.`product_id` AND `pd`.`language_id` = " . (int) 1 . "
			";
		}
		
		if( ! empty( $this->_data['filter_category_id'] ) ) {
			if( ! in_array( 'p2c', $skip ) ) {
				$sql .= $this->_joinProductToCategory( 'p2c' );
			}
			
			if( ( ! empty( $this->_data['filter_sub_category'] ) || $this->_categories ) && ! in_array( 'cp', $skip ) ) {
				$sql .= $this->_joinCategoryPath( 'cp', 'p2c' );
			}
		
			if( ! empty( $this->_data['filter_filter'] ) && ! in_array( 'pf', $skip ) ) {
				$sql .= "
					INNER JOIN
						`" . DB_PREFIX . "product_filter` AS `pf`
					ON
						`p2c`.`product_id` = `pf`.`product_id`
				";
			}
		}
		
		return $sql;
	}


	protected function getBasicFilters( array $conditions = array() ) {
		array_unshift( $conditions, "`p`.`status` = '1'");
		array_unshift( $conditions, "`p`.`date_available` <= NOW()" );
		
		// sprawdź branżę
		if( ! empty( $this->_data['filter_manufacturer_id'] ) ) {
			$conditions[] = '`p`.`manufacturer_id` = ' . (int) $this->_data['filter_manufacturer_id'];
		}
		
		// sprawdź kategorię
		if( ! empty( $this->_data['filter_category_id'] ) ) {
			if( ! empty( $this->_data['filter_sub_category'] ) || $this->_categories ) {
				$conditions['cat_id'] = "`cp`.`path_id` = '" . (int) $this->_data['filter_category_id'] . "'";
			} else {
				$conditions['cat_id'] = "`p2c`.`category_id` = '" . (int) $this->_data['filter_category_id'] . "'";
			}
			
			if( self::hasFilters() && ! empty( $this->_data['filter_filter'] ) && ! empty( $this->_data['filter_category_id'] ) ) {
				$filters = explode( ',', $this->_data['filter_filter'] );
				
				$conditions[] = '`pf`.`filter_id` IN(' . implode( ',', $this->_parseArrayToInt( $filters ) ) . ')';
			}
		}
		
		// sprawdź frazę / tagi
		if( ! empty( $this->_data['filter_name'] ) || ! empty( $this->_data['filter_tag'] ) ) {
			$sql = array();
			
			if( ! empty( $this->_data['filter_name'] ) ) {
				$implode	= array();
				$words		= explode( ' ', trim( preg_replace( '/\s\s+/', ' ', $this->_data['filter_name'] ) ) );
				
				foreach( $words as $word ) {
					$implode[] = "`pd`.`name` LIKE '%" . $this->_ctrl->db->escape( $word ) . "%'";
				}
				
				if( $implode ) {
					$sql[] = '(' . implode( ' AND ', $implode ) . ')';
				}
				
				if( ! empty( $this->_data['filter_description'] ) ) {
					$sql[] = "`pd`.`description` LIKE '%" . $this->_ctrl->db->escape( $this->_data['filter_name'] ) . "%'";
				}
			}
			
			if( ! empty( $this->_data['filter_tag'] ) ) {
				$sql[] = "`pd`.`tag` LIKE '%" . $this->_ctrl->db->escape( $this->_data['filter_tag'] ) . "%'";
			}
			
			if( ! empty( $this->_data['filter_name'] ) ) {
				$tmp = array( '`p`.`model`', '`p`.`sku`', '`p`.`upc`', '`p`.`ean`', '`p`.`jan`', '`p`.`isbn`', '`p`.`mpn`' );
				
				foreach( $tmp as $tm ) {
					$sql[] = "LCASE(" . $tm . ") = '" . $this->_ctrl->db->escape( utf8_strtolower( $this->_data['filter_name'] ) ) . "'";
				}
			}
			
			if( $sql ) {
				$conditions['search'] = '(' . implode( ' OR ', $sql ) . ')';
			}
		}
		
		return $conditions;
	}

	
}