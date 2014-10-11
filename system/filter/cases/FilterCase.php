<?php 


class FilterCase implements FilterCaseInterface 
{
	

	protected $where;


	/**
	 * Store attributes id's and set filters for SQL
	 *
	 * @return void
	 */
	public function setAttributes($attributes = array())
	{
		$_attributes = new Attributes;
		$result = $_attributes->sql($attributes);
die();
		// // Get list of attribute ids
		// $ids = array_keys($attributes);

		// // Set attribute ID's filter
		// if ($ids)
		// {
		// 	$this->where[] = sprintf('`tmp`.`attribute_id` NOT IN(%s)', implode( ',', $ids));
		// }


		// Convert attributes to sql
		if ($attributes)
		{
			$sql = array();

			foreach( $attributes as $attrib_id => $attr ) {
				$sql[]	= sprintf( "`product_id` IN( 
					SELECT 
						`product_id` 
					FROM 
						`" . DB_PREFIX . "product_attribute` 
					WHERE 
						REPLACE(REPLACE(TRIM(`text`), '\r', ''), '\n', '') IN(%s) AND
						`language_id` = " . (int) 1 . " AND
						`attribute_id` = " . (int) $attrib_id . "
				)", implode( ',', $attr ) );
			}
			
			$sql = implode( ' AND ', $sql );
		} else {
			$sql = '';
		}

		$this->where[] = $sql;


		$columns = $this->_baseColumns( '`pa`.`attribute_id`', '`p`.`product_id`', '`pa`.`text`' );
		$sql = $this->_createSQLByCategories(sprintf( "
			SELECT
				%s
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_attribute` AS `pa`
			ON
				`pa`.`product_id` = `p`.`product_id` AND `pa`.`language_id` = '" . (int) 1 . "'
			%s
			WHERE
				%s
			", 
			implode( ',', $columns ), 
			$this->_baseJoin(), 
			implode( ' AND ', $this->_baseConditions() ) 
		));

		$sql = sprintf( "
			SELECT 
				`text`, `attribute_id`, COUNT( DISTINCT `tmp`.`product_id` ) AS `total`
			FROM( %s ) AS `tmp` 
				%s 
			GROUP BY 
				`text`, `attribute_id`
		", $sql, $this->_conditionsToSQL( $this->where ) );


		echo $sql; die();
	}


	private function _baseColumns() {
		$columns = func_get_args();
		
		
		
		return $columns;
	}

	private function _createSQLByCategories( $sql ) {
			return $sql;
		
		return sprintf("
			SELECT
				`tmp`.*
			FROM
				( %s ) AS `tmp`
			%s %s %s
			",
			$sql, 
			$this->_joinProductToCategory( 'mf_p2c', 'tmp' ), 
			$this->_joinCategoryPath(), 
			$this->_categoriesToSQL() 
		);
	}


	/**
	 * Conver attribute text to sql
	 *
	 * @return array
	 */
	private function _convertAttribs( $attribs, $field = 'text' ) {
		$tmp		= array();
		
		foreach( $attribs as $attr ) {
			foreach( $attr as $att ) {
				if( $this->_settings['attribute_separator'] == ',' ) {
					$tmp[] = sprintf( 'FIND_IN_SET( %s, `%s` )', $att, $field );
				} else {
					foreach( $att as $at ) {
						$tmp[] = sprintf( '`%s` LIKE %s', $field, $at );
					}
				}
			}
		}
		
		return $tmp;
	}


	private function _joinProductToCategory( $alias = 'mf_p2c', $on = 'p' ) {
		return "
			INNER JOIN
				`" . DB_PREFIX . "product_to_category` AS `" . $alias . "`
			ON
				`" . $alias . "`.`product_id` = `" . $on . "`.`product_id`
		";
	}


	private function _joinCategoryPath( $alias = 'mf_cp', $on = 'mf_p2c' ) {
		return "
			INNER JOIN
				`" . DB_PREFIX . "category_path` AS `" . $alias . "`
			ON
				`" . $alias . "`.`category_id` = `" . $on . "`.`category_id`
		";
	}


	private function _categoriesToSQL( $join = ' WHERE ', array $categories = NULL ) {
		if( $categories === NULL )
			$categories = $this->_categories;
		
		if( $categories ) {
			$ids = array();
			$sql = array();
			
			foreach( $categories as $cat1 ) {
				foreach( $cat1 as $cat2 ) {
					$ids[] = end( $cat2 );
				}
			}
			
			$ids = implode( ',', $ids );
			
			//if( ! empty( $this->_data['filter_sub_category'] ) ) {
				$sql[] = '`mf_cp`.`path_id` IN(' . $ids . ')';
			//} else {
			//	$sql[] = '`mf_p2c`.`category_id` IN(' . $ids . ')';
			//}
			
			$sql = $join . implode( ' AND ', $sql );
		} else {
			$sql = '';
		}
		
		return $sql;
	}


	public function _baseJoin( array $skip = array() ) {
		$sql = '';
		
		if( ! in_array( 'p2s', $skip ) ) {
			$sql .= "
				INNER JOIN
					`" . DB_PREFIX . "product_to_store` AS `p2s`
				ON
					`p2s`.`product_id` = `p`.`product_id` AND `p2s`.`store_id` = " . (int) 0 . "
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


	public function _baseConditions( array $conditions = array() ) {
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
		
		// sprawdź kategorię
		//if( ! empty( $this->_data['filter_category_id'] ) ) {
		//	$conditions[] = 'p2c.category_id = ' . (int) $this->_data['filter_category_id'];
		//}
		
		return $conditions;
	}


	private function _conditionsToSQL( $conditions, $join = ' WHERE ' ) {
		return $conditions ? $join . implode( ' AND ', $conditions ) : '';
	}

	
}