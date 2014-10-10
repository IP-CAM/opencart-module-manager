<?php 


function quote_escape($str) {
	global $registry;

	return '"' . $registry->get('db')->escape(chop($str)) . '"';
}


class FilterCase implements FilterCaseInterface 
{
	
	protected $conditions;


	/**
	 * Store attributes id's and set filters for SQL
	 *
	 * @return void
	 */
	public function setAttributes($attributes = array())
	{
		// Get list of attribute ids
		$ids = array_keys($attributes);

		// Set attribute ID's filter
		if ($ids)
		{
			$this->conditions[] = sprintf('`tmp`.`attribute_id` NOT IN(%s)', implode( ',', $ids));
		}


		// Convert attributes to sql
		if ($attributes)
		{
			$sql = array();

			foreach( $attributes as $attrib_id => $attr ) {
				$_settings['attribute_separator'] = "|";

				if( ! empty( $_settings['attribute_separator'] ) ) {
					$sql[]	= sprintf( "`product_id` IN( 
						SELECT 
							`product_id` 
						FROM 
							`" . DB_PREFIX . "product_attribute`
						WHERE 
							( %s ) AND
							`language_id` = " . (int) 1 . " AND
							`attribute_id` = " . (int) $attrib_id . " 
					)", implode( ' OR ', $this->_convertAttribs( $attr ) ) );
				} else {
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
			}
			
			$sql = $join . implode( ' AND ', $sql );
		} else {
			$sql = '';
		}


		$sql = $this->_createSQLByCategories(sprintf( "
			SELECT
				%s
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_attribute` AS `pa`
			ON
				`pa`.`product_id` = `p`.`product_id` AND `pa`.`language_id` = '" . (int) $this->_ctrl->config->get('config_language_id') . "'
			%s
			WHERE
				%s
			", 
			implode( ',', $columns ), 
			$this->_baseJoin(), 
			implode( ' AND ', $this->_baseConditions( $conditionsIn ) ) 
		));
		

		$sql = sprintf( "
			SELECT 
				`text`, `attribute_id`, COUNT( DISTINCT `tmp`.`product_id` ) AS `total`
			FROM( %s ) AS `tmp` 
				%s 
			GROUP BY 
				`text`, `attribute_id`
		", $sql, $this->_conditionsToSQL( $conditions ) );

		echo $sql; die('1231231');
	}


	private function _baseColumns() {
		$columns = func_get_args();
		
		
		
		return $columns;
	}

	private function _createSQLByCategories( $sql ) {
		if( ! $this->_categories )
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
		print_r($attribs); die();
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

	
}