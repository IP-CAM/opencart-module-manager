<?php

/**
 * Mega Filter
 * 
 * @license Commercial
 * @author marsilea15@gmail.com 
 */

class MegaFilterCore {	
	
	public static $_specialRoute	= array( 'product/special' );
	
	public static $_searchRoute		= array( 'product/search' );
	
	private static $_cache			= array();
	
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Aktualnie przetwarzane zapytanie SQL
	 * 
	 * @var string 
	 */
	private $_sql	= '';
	
	/**
	 * Aktualne dane
	 * 
	 * @var array 
	 */
	private $_data	= array();
	
	/**
	 * Aktualny kontroler nadrzędny
	 * 
	 * @var stdClass 
	 */
	private $_ctrl	= NULL;
	
	/**
	 * Aktualne parametry URL
	 * 
	 * @var string 
	 */
	private $_mfilterUrl	= '';
	
	/**
	 * Przetworzone parametry URL
	 * 
	 * @var array 
	 */
	private $_parseParams	= array();
	
	/**
	 * Lista atrybutów
	 * 
	 * @var array 
	 */
	private $_attribs		= array();
	
	/**
	 * Ustawienia
	 * 
	 * @var array 
	 */
	public $_settings		= array();
	
	/**
	 * Lista opcji
	 * 
	 * @var array 
	 */
	private $_options		= array();
	
	/**
	 * Lista filtrów
	 * 
	 * @var array 
	 */
	private $_filters		= array();
	
	/**
	 * Lista kategorii
	 * 
	 * @var array 
	 */
	private $_categories	= array();
	
	/**
	 * Lista warunków SQL
	 * 
	 * @var array 
	 */
	private $_conditions	= array();
	
	/**
	 * @var bool
	 */
	private static $_hasFilters	= NULL;
	
	/**
	 * Utwórz nową instancję klasy
	 * 
	 * @return MegaFilterCore 
	 */
	static public function newInstance( & $ctrl, $sql ) {
		return new MegaFilterCore( $ctrl, $sql );
	}
	
	static public function hasFilters() {
		if( self::$_hasFilters === NULL ) {
			self::$_hasFilters = version_compare( VERSION, '1.5.5', '>=' );
		}
		
		return self::$_hasFilters;
	}
	
	/**
	 * Pobierz informacje JSON
	 * 
	 * @param array $types
	 * @return type 
	 */
	public function getJsonData( array $types, $idx = NULL ) {
		$json		= array();
		$modules	= $this->_ctrl->config->get('mega_filter_module');
		$attribs	= $idx !== NULL && isset( $modules[$idx] ) ? $modules[$idx]['base_attribs'] : $this->_settings['attribs'];
		
		foreach( $types as $type ) {
			if( in_array( $type, array( 'manufacturers', 'stock_status', 'rating', 'price' ) ) ) {
				if( ! empty( $attribs[$type]['enabled'] ) ) {
					switch( $type ) {
						case 'stock_status'		: $json[$type] = $this->getCountsByStockStatus(); break;
						case 'manufacturers'	: $json[$type] = $this->getCountsByManufacturers(); break;
						case 'rating'			: $json[$type] = $this->getCountsByRating(); break;
						case 'price'			: $json[$type] = $this->getMinMaxPrice(); break;
					}
				}
			} else {
				switch( $type ) {
					case 'attribute'	:
					case 'attributes'	: $json['attributes'] = $this->getCountsByAttributes(); break;
					case 'option'		:
					case 'options'		: $json['options'] = $this->getCountsByOptions(); break;
					case 'filter'		:
					case 'filters'		: if( self::hasFilters() ) { $json['filters'] = $this->getCountsByFilters(); } break;
				}
			}
		}
		
		return $json;
	}
	
	/**
	 * __construct()
	 * 
	 * @param string $sql 
	 */
	private function __construct( & $ctrl, $sql ) {
		$this->_ctrl	= & $ctrl;
		$this->_sql		= $sql;
		$this->_data	= self::_getData( $ctrl );
		
		$this->_settings	= $this->_ctrl->config->get('mega_filter_settings');
		
		$this->_mfilterUrl	= isset( $this->_ctrl->request->get['mfp'] ) ? $this->_ctrl->request->get['mfp'] : '';
		
		if( ! empty( $this->_settings['in_stock_default_selected'] ) ) {
			if( false === mb_strpos( $this->_mfilterUrl, 'stock_status', 0, 'utf-8' ) ) {
				$this->_mfilterUrl .= $this->_mfilterUrl ? ',' : '';
				$this->_mfilterUrl .= 'stock_status[' . $this->inStockStatus() . ']';
			}
		}
		
		$this->_parseParams();
	}
	
	public static function _getData( & $ctrl ) {
		$data = array();
		
		/**
		 * Kategoria 
		 */
		if( ! empty( $ctrl->request->get['category_id'] ) ) {
			$data['filter_category_id'] = (int) $ctrl->request->get['category_id'];
		} else if( ! empty( $ctrl->request->get['path'] ) ) {
			$parts = explode( '_', (string) $ctrl->request->get['path'] );
			$data['filter_category_id'] = (int) array_pop( $parts );
		}
		
		/**
		 * Wyszukiwanie w podkategoriach 
		 */
		if( ! empty( $ctrl->request->get['sub_category'] ) ) {
			$data['filter_sub_category'] = $ctrl->request->get['sub_category'];
		} else if( in_array( self::_route( $ctrl ), array( 'product/category' ) ) ) {
			if( self::_showProductsFromSubcategories( $ctrl ) ) {
				$data['filter_sub_category'] = '1';
			}
		}
		
		/**
		 * Filtry 
		 */
		if( ! empty( $ctrl->request->get['filter'] ) ) {
			$data['filter_filter'] = $ctrl->request->get['filter'];
		}
		
		/**
		 * Wyszukiwanie w opisach produktów 
		 */
		if( ! empty( $ctrl->request->get['description'] ) ) {
			$data['filter_description'] = $ctrl->request->get['description'];
		}
		
		/**
		 * Tagi 
		 */
		if( ! empty( $ctrl->request->get['filter_tag'] ) ) {
			$data['filter_tag'] = $ctrl->request->get['filter_tag'];
		} else if( ! empty( $ctrl->request->get['tag'] ) ) {
			$data['filter_tag'] = $ctrl->request->get['tag'];
		} else if( ! empty( $ctrl->request->get['search'] ) ) {
			$data['filter_tag'] = $ctrl->request->get['search'];
		}
		
		/**
		 * Producent
		 */
		if( ! empty( $ctrl->request->get['manufacturer_id'] ) ) {
			$data['filter_manufacturer_id'] = (int) $ctrl->request->get['manufacturer_id'];
		}
		
		/**
		 * Fraza 
		 */
		if( ! empty( $ctrl->request->get['search'] ) ) {
			$data['filter_name'] = (string) $ctrl->request->get['search'];
		}
		
		return $data;
	}
	
	private static function _showProductsFromSubcategories( & $ctrl ) {
		$settings = $ctrl->config->get('mega_filter_settings');
		
		if( empty( $settings['show_products_from_subcategories'] ) ) {
			return false;
		}
		
		if( ! empty( $settings['level_products_from_subcategories'] ) ) {
			$level = (int) $settings['level_products_from_subcategories'];
			$path = explode( '_', empty( $ctrl->request->get['path'] ) ? '' : $ctrl->request->get['path'] );
			
			if( $path && count( $path ) < $level ) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Pobierz listę parametrów
	 * 
	 * @return array 
	 */
	public function getParseParams() {
		return $this->_parseParams;
	}
	
	public function inStockStatus() {
		return $inStockStatus = empty( $this->_settings['in_stock_status'] ) ? 7 : $this->_settings['in_stock_status'];
	}
	
	/**
	 * Przetwarzanie listy parametrów
	 * 
	 * @return array 
	 */
	private function _parseParams() {
		$this->_parseParams = array();
		$this->_attribs		= array();
		$this->_options		= array();
		$this->_filters		= array();
		$this->_categories	= array();
		$this->_conditions	= array(
			'out' => array(),
			'in' => array()
		);
		
		if( $this->_mfilterUrl ) {
			preg_match_all( '/([a-z0-9\-_]+)\[([^]]*)\]/', $this->_mfilterUrl, $matches );
			
			if( ! empty( $matches[0] ) ) {
				foreach( $matches[0] as $k => $match ) {
					if( empty( $matches[1][$k] ) )						
						continue;
					
					$key	= $matches[1][$k];
					
					if( empty( $matches[2][$k] ) ) {
						if( $key == 'stock_status' && ! empty( $this->_settings['in_stock_default_selected'] ) ) {
							$this->_parseParams[$key] = array();
						}
						
						continue;
					}
					
					$value	= explode( ',', $matches[2][$k] );
					
					foreach( $value as $kk => $vv ) {						
						$value[$kk]	= str_replace(array(
							'LA==', 'Ww==', 'XQ=='
						), array(
							',', '[', ']'
						), $vv);
					}
					
					switch( $key ) {
						case 'search_oc' :
						case 'search' : {
							if( isset( $value[0] ) ) {
								$this->_data['filter_name'] = $value[0];
								$this->_data['filter_mf_name'] = $value[0];
							} else {
								$value = NULL;
							}
							
							break;
						}
						case 'price' : {
							if( isset( $value[0] ) && isset( $value[1] ) ) {
								$this->_conditions['out']['mf_price'] = '( `mf_price` > ' . ( (int) $value[0] - 1 ) . ' AND `mf_price` < ' . ( (int) $value[1] + 1 ) . ')';
							} else {
								$value = NULL;
							}
							
							break;
						}
						case 'manufacturers' : {
							$this->_conditions['in']['manufacturers'] = '`p`.`manufacturer_id` IN(' . implode( ',', $this->_parseArrayToInt( $value ) ) . ')';
							
							break;
						}
						case 'rating' : {
							$sql = array();
							
							foreach( $this->_parseArrayToInt( $value ) as $rating ) {
								switch( $rating ) {
									case '1' :
									case '2' :
									case '3' :
									case '4' : {
										$sql[] = '( `mf_rating` >= ' . $rating . ' AND `mf_rating` < ' . ( $rating + 1 ) . ')'; 
										
										break;
									}
									case '5' : {
										$sql[] = '`mf_rating` = 5';
									}
								}
							}
							
							if( $sql )
								$this->_conditions['out']['mf_rating'] = '(' . implode( ' OR ', $sql ) . ')';
							
							break;
						}
						case 'stock_status' : {
							$value = $this->_parseArrayToInt( $value );
							
							$this->_conditions['in']['stock_status'] = sprintf( 'IF( `p`.`quantity` > 0, %s, `p`.`stock_status_id` ) IN(%s)', 
								$this->inStockStatus(),
								implode( ',', $value )
							);
							
							break;
						}
						default : {
							if( preg_match( '/^c-.+-[0-9]+$/', $key ) ) {
								$this->_categories[$key][] = $this->_parseArrayToInt( $value );
							} else {
								$k = explode( '-', $key );

								if( isset( $k[0] ) && isset( $k[1] ) && 'o' == mb_substr( $k[0], -1, 1, 'utf-8' ) ) {
									$this->_options[trim( $k[0], 'o').'-'.$k[1]][] = implode( ',', $this->_parseArrayToInt( $value ) );
								} else if( isset( $k[0] ) && isset( $k[1] ) && 'f' == mb_substr( $k[0], -1, 1, 'utf-8' ) ) {
									if( self::hasFilters() ) {
										$this->_filters[trim( $k[0], 'f').'-'.$k[1]][] = implode( ',', $this->_parseArrayToInt( $value ) );
									}
								} else {
									if( empty( $this->_settings['attribute_separator'] ) ) {
										$this->_attribs[$key][] = implode( ',', $this->_parseArrayToStr( $value ) );
									} else {
										$this->_attribs[$key][] = $this->_parseArrayToStr( $value, $this->_settings['attribute_separator'] );
									}
								}
							}
						}
					}
					
					if( $value !== NULL )
						$this->_parseParams[$key] = $value;
				}
			}
		}
		
		return $this->_parseParams;
	}
	
	private function _baseColumns() {
		$columns = func_get_args();
		
		if( ! empty( $this->_conditions['out']['mf_price'] ) ) {
			if( $this->_ctrl->config->get( 'config_tax' ) ) {
				$columns['mf_price'] = '( ( ' . $this->_priceCol( NULL ) . ' * ( 1 + IFNULL(' . $this->_percentTaxCol( NULL ) . ', 0) / 100 ) + IFNULL(' . $this->_fixedTaxCol( NULL ) . ', 0) ) * ' . (float) $this->_ctrl->currency->getValue() . ') AS `mf_price`';
			} else {
				$columns['mf_price'] = '(' . $this->_priceCol( NULL ) . '* ' . (float) $this->_ctrl->currency->getValue() . ') AS `mf_price`';
			}
		}
		
		if( ! empty( $this->_conditions['out']['mf_rating'] ) ) {
			$columns['mf_rating'] = $this->_ratingCol();
		}
		
		return $columns;
	}
	
	/**
	 * Nowe zapytanie SQL
	 * 
	 * @return string 
	 */
	public function getSQL( $fn, $sql = NULL, $template = NULL, array $conditions = array() ) {
		if( $sql === NULL )
			$sql = $this->_sql;
		
		$sql 		= trim( $sql );
		$limit		= '';
		$limitReg	= '/LIMIT\s+[0-9]+(\s*,\s*[0-9]+)?$/i';
		
		if( preg_match( $limitReg, $sql, $matches ) ) {
			if( ! empty( $matches[0] ) ) {
				$limit 	= $matches[0];
				$sql	= preg_replace( $limitReg, '', $sql );
			}
		}
		
		if( ! $conditions )
			$conditions = $this->_conditions;
		
		if( ! isset( $conditions['in'] ) )
			$conditions['in'] = array();
		
		if( ! isset( $conditions['out'] ) )
			$conditions['out'] = array();
		
		if( isset( $this->_data['filter_mf_name'] ) && NULL != ( $baseConditions = $this->_baseConditions() ) && isset( $baseConditions['search'] ) )
			$conditions['in']['search'] = $baseConditions['search'];
		
		if( ! $conditions['out'] && ! $conditions['in'] && ! $this->_attribs && ! $this->_options && ! $this->_filters && ! $this->_categories && ! $template )
			return $sql . ( $limit ? ' ' . $limit : '' );
		
		$columns = implode( ',', $this->_baseColumns() );		
		$conditionsOut = array();
		
		if( $columns )
			$columns = ',' . $columns;
		
		if( NULL != ( $conditionsToSQL = $this->_conditionsToSQL( $conditions['out'], '' ) ) ) {
			$conditionsOut[] = $conditionsToSQL;
		}
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditions['in'], $conditionsOut );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditions['in'], $conditionsOut );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditions['in'], $conditionsOut );
		
		if( ! self::_showProductsFromSubcategories( $this->_ctrl ) && $this->_categories /*NULL != ( $categoriesToSQL = $this->_categoriesToSQL( '' ) )*/ ) {
			//$conditions['in'][] = $categoriesToSQL;
			
			//$sql = preg_replace( '/(AND\s+)?`?(p2c|cp)`?\.`?(category_id|path_id)`?\s*=\s*(\'|")[0-9]+(\'|")(\s+AND)?/i', '$6', $sql );
			
			//if( ! preg_match( '/`?' . DB_PREFIX . 'category_path`?/', $sql ) ) {
				//$sql = preg_replace( '/(LEFT|INNER)\s+JOIN/i', sprintf( '%s %s $1 JOIN', $this->_joinCategoryPath(), $this->_joinProductToCategory() ), $sql, 1 );
				//$sql = preg_replace('~(.*)WHERE~ms', '$1' . sprintf( '%s %s WHERE', $this->_joinProductToCategory(), $this->_joinCategoryPath() ), $sql, 1);
			//}
			
			if( preg_match( '/FROM\s+`?' . DB_PREFIX . 'product_to_category`?\s+(AS)?`?p2c`?/ims', $sql ) ) {
				$sql = preg_replace( '/(LEFT|INNER)\s+JOIN\s+`?' . DB_PREFIX . '(product_to_category|category_path)`?\s+(AS)?`?(p2c|cp)`?\s+ON\s*\(?\s*`?(cp|p2c|p)`?\.`?(category_id|product_id)`?\s*=\s*`?(p2c|cp|p)`?\.`?(category_id|product_id)`?\s*\)?/ims', '', $sql );
				$sql = preg_replace( 
					'/FROM\s+`?' . DB_PREFIX . 'product_to_category`?\s+(AS)?`?p2c`?/ims', 
					'
						FROM 
							`' . DB_PREFIX . 'category_path` AS `cp`
						INNER JOIN
							`' . DB_PREFIX . 'product_to_category` AS `p2c`
						ON
							`p2c`.`category_id` = `cp`.`category_id`
					', 
					$sql 
				);
				$sql = preg_replace( '/AND\s+`?p2c`?\.`?category_id`?\s*=/ims', 'AND `cp`.`path_id` =', $sql );
			}
			
		}
		
		if( $conditions['in'] ) {
			$sql = preg_replace('~(.*)WHERE~ms', '$1' . $this->_conditionsToSQL( $conditions['in'] ) . ' AND ', $sql, 1);
		}
		
		switch( $fn ) {
			case 'getTotalProductSpecials' :
			case 'getTotalProducts' : {
				$sql = preg_replace( '/COUNT\(\s*DISTINCT\s*(`?[^.]+`?)\.`?product_id`?\s*\)\s*(AS\s*)?total/', 'DISTINCT `$1`.`product_id`' . $columns, $sql );
				$sql = sprintf( $template ? $template : 'SELECT COUNT(DISTINCT `product_id`) AS `total` FROM(%s) AS `tmp`', $this->_createSQLByCategories( $sql ) );
				
				break;
			}
			case 'getProductSpecials' :
			case 'getProducts' : {
				$cols = '*';
			
				if( false !== mb_strpos( $sql, 'SQL_CALC_FOUND_ROWS', 0, 'utf-8' ) ) {
					$sql = str_replace( 'SQL_CALC_FOUND_ROWS', '', $sql );
					$cols = 'SQL_CALC_FOUND_ROWS *';
				}
				
				$sql = preg_replace( '/^(\s?SELECT\s)(DISTINCT\s)?([^.]+\.product_id)/', '$1$2$3' . $columns, $sql );
				$sql = sprintf( $template ? $template : 'SELECT ' . $cols . ' FROM(%s) AS `tmp`', $this->_createSQLByCategories( $sql ) );
				
				break;
			}
		}
		
		if( $conditionsOut ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditionsOut );
		}
		
		if( $limit ) {
			$sql .= ' ' . $limit;
		}
		
		//if( $fn == 'getProducts' )
		//	die($sql);
		
		//if( $fn == 'getTotalProducts' ) {
		//	die($sql);
		//}
		
		return $sql;
	}
	
	private function _joinOptions( & $conditions, array $options = NULL ) {
		if( $options === NULL )
			$options = $this->_options;
		
		if( ! $options )
			return '';
		
		$sql = '';
		$idx = 1;
		
		foreach( $options as $opt ) {
			$conditions[] = sprintf( '`tmp_opt' . $idx . '`.`option_value_id` IN(%s)', implode( ',', $opt ) );
			$sql .= "
				JOIN
					`" . DB_PREFIX . "product_option_value` AS `tmp_opt" . $idx . "`
				USING( `product_id` )
			";
			
			$idx++;
		}
		
		return $sql;
	}
	
	private function _joinAttribs( & $conditions, array $attribs = NULL ) {
		if( $attribs === NULL )
			$attribs = $this->_attribs;
		
		if( ! $attribs )
			return '';
		
		$sql		= '';
		$idx		= 1;
			
		foreach( $attribs as $attr ) {
			if( ! empty( $this->_settings['attribute_separator'] ) ) {
				$conditions[] = '(' . implode( ' OR ', $this->_convertAttribs( $attr, '`tmp_attr' . $idx . '`.`text`' ) ) . ')';
			} else {
				$conditions[] = sprintf( '`tmp_attr' . $idx . '`.`text` IN(%s)', implode( ',', $attr ) );
			}
			
			$sql .= "
				JOIN
					`" . DB_PREFIX . "product_attribute` AS `tmp_attr" . $idx . "`
				USING( `product_id` )
			";
			
			$idx++;
		}
		
		return $sql;
	}
	
	private function _optionsToSQL( $join = ' WHERE ', array $options = NULL, & $conditionsIn = NULL, & $conditionsOut = NULL ) {
		if( $options === NULL )
			$options = $this->_options;
		
		if( false != ( $mFilterPlus = $this->mfilterPlus() ) ) {
			$sql = $mFilterPlus->optionsToSQL( $join, $options );
			
			if( $conditionsIn !== NULL && $sql ) {
				$conditionsIn[] = $sql;
			}
			
			return $sql;
		}
		
		if( $options ) {
			$sql		= array();
			$quantity	= '';			
		
			if( ! empty( $this->_settings['in_stock_default_selected'] ) || ( ! empty( $this->_parseParams['stock_status'] ) && in_array( $this->inStockStatus(), $this->_parseParams['stock_status'] ) ) ) {
				$quantity .= ' AND `quantity` > 0';
			}
			
			foreach( $options as $opt ) {
				$sql[] = sprintf( "
					`product_id` IN( 
						SELECT 
							`product_id` 
						FROM 
							`" . DB_PREFIX . "product_option_value` 
						WHERE 
							`option_value_id` IN(%s) %s
					)", implode( ',', $opt ), $quantity );
			}
			
			$sql = $join . implode( ' AND ', $sql );
		} else {
			$sql = '';
		}
		
		if( $conditionsOut !== NULL && $sql )
			$conditionsOut[] = $sql;
		
		return $sql;
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
	
	private function _filtersToSQL( $join = ' WHERE ', array $filters = NULL, & $conditionsIn = NULL, & $conditionsOut = NULL ) {
		if( ! self::hasFilters() )
			return '';
		
		if( $filters === NULL )
			$filters = $this->_filters;
		
		if( false != ( $mFilterPlus = $this->mfilterPlus() ) ) {
			$sql = $mFilterPlus->filtersToSQL( $join, $filters );
			
			if( $conditionsIn !== NULL && $sql )
				$conditionsIn[] = $sql;
			
			return $sql;
		}
		
		if( $filters ) {
			$sql		= array();
			
			foreach( $filters as $opt ) {
				$sql[] = sprintf( "
					`product_id` IN( 
						SELECT 
							`product_id` 
						FROM 
							`" . DB_PREFIX . "product_filter` 
						WHERE 
							`filter_id` IN(%s)
					)", implode( ',', $opt ) );
			}
			
			$sql = $join . implode( ' AND ', $sql );
		} else {
			$sql = '';
		}
		
		if( $conditionsOut !== NULL && $sql )
			$conditionsOut[] = $sql;
		
		return $sql;
	}
	
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
	
	private function mfilterPlus() {
		if( ! file_exists( DIR_SYSTEM . 'library/mfilter_plus.php' ) )
			return false;
			
		$this->_ctrl->load->library('mfilter_plus');
		
		$mfilterPlus = Mfilter_Plus::getInstance( $this->_ctrl );
		
		return $mfilterPlus->setValues( $this->_attribs, $this->_options, $this->_filters );
	}
	
	private function _attribsToSQL( $join = ' WHERE ', array $attribs = NULL, & $conditionsIn = NULL, & $conditionsOut = NULL ) {
		if( $attribs === NULL )
			$attribs = $this->_attribs;
		
		if( false != ( $mFilterPlus = $this->mfilterPlus() ) ) {
			$sql = $mFilterPlus->attribsToSQL( $join, $attribs );
			
			if( $conditionsIn !== NULL && $sql )
				$conditionsIn[] = $sql;
			
			return $sql;
		}
		
		if( $attribs ) {
			$sql		= array();
			
			foreach( $attribs as $key => $attr ) {
				list( $attrib_id ) 	= explode( '-', $key );
				
				if( ! empty( $this->_settings['attribute_separator'] ) ) {
					$sql[]	= sprintf( "`product_id` IN( 
						SELECT 
							`product_id` 
						FROM 
							`" . DB_PREFIX . "product_attribute`
						WHERE 
							( %s ) AND
							`language_id` = " . (int) $this->_ctrl->config->get( 'config_language_id' ) . " AND
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
							`language_id` = " . (int) $this->_ctrl->config->get( 'config_language_id' ) . " AND
							`attribute_id` = " . (int) $attrib_id . "
					)", implode( ',', $attr ) );
				}
			}
			
			$sql = $join . implode( ' AND ', $sql );
		} else {
			$sql = '';
		}
		
		if( $conditionsOut !== NULL && $sql )
			$conditionsOut[] = $sql;
		
		return $sql;
	}
	
	/**
	 * Kolumna rating
	 * 
	 * @return string 
	 */
	private function _ratingCol( $alias = 'mf_rating' ) {
		return "(SELECT AVG(`rating`) AS `total` FROM `" . DB_PREFIX . "review` AS `r1` WHERE `r1`.`product_id` = `p`.`product_id` AND `r1`.`status` = '1' GROUP BY `r1`.`product_id`)" . ( $alias ? " AS `" . $alias . '`' : '' );
	}
	
	private function _customerGroupId() {
		return $this->_ctrl->customer->isLogged() ? $this->_ctrl->customer->getCustomerGroupId() : $this->_ctrl->config->get( 'config_customer_group_id' );
	}
	
	/**
	 * Kolumna discount
	 * 
	 * @return string 
	 */
	private function _discountCol( $alias = 'discount' ) {
		$sql = "SELECT `price` FROM `" . DB_PREFIX . "product_discount` AS `pd2` WHERE `pd2`.`product_id` = `p`.`product_id` AND `pd2`.`customer_group_id` = '" . (int) $this->_customerGroupId() . "' AND `pd2`.`quantity` = '1' AND ((`pd2`.`date_start` = '0000-00-00' OR `pd2`.`date_start` < NOW()) AND (`pd2`.`date_end` = '0000-00-00' OR `pd2`.`date_end` > NOW())) ORDER BY `pd2`.`priority` ASC, `pd2`.`price` ASC LIMIT 1";
		
		return $alias ? sprintf( "(%s) AS %s", $sql, $alias ) : $sql;
	}
	
	/**
	 * Kolumna special
	 * 
	 * @return string
	 */
	public function _specialCol( $alias = 'special' ) {
		$sql = "SELECT `price` FROM `" . DB_PREFIX . "product_special` AS `ps` WHERE `ps`.`product_id` = `p`.`product_id` AND `ps`.`customer_group_id` = '" . (int) $this->_customerGroupId() . "' AND ((`ps`.`date_start` = '0000-00-00' OR `ps`.`date_start` < NOW()) AND (`ps`.`date_end` = '0000-00-00' OR `ps`.`date_end` > NOW())) ORDER BY `ps`.`priority` ASC, `ps`.`price` ASC LIMIT 1";
		
		return $alias ? sprintf( "(%s) AS %s", $sql, $alias ) : $sql;
	}
	
	private function _taxConditions() {
		$conditions	= array();
		
		$country_id	= $p_country_id = $s_country_id = (int) $this->_ctrl->config->get('config_country_id');
		$zone_id = $p_zone_id = $s_zone_id = (int) $this->_ctrl->config->get('config_zone_id');
		
		if( ! empty( $this->_ctrl->session->data['payment_country_id'] ) && ! empty( $this->_ctrl->session->data['payment_zone_id'] ) ) {
			$p_country_id = (int) $this->_ctrl->session->data['payment_country_id'];
			$p_zone_id = (int) $this->_ctrl->session->data['payment_zone_id'];
		}
		
		if( ! empty( $this->_ctrl->session->data['shipping_country_id'] ) && ! empty( $this->_ctrl->session->data['shipping_zone_id'] ) ) {
			$s_country_id = (int) $this->_ctrl->session->data['shipping_country_id'];
			$s_zone_id = (int) $this->_ctrl->session->data['shipping_zone_id'];
		}
		
		$conditions[] = "(
			`tr1`.`based` = 'store' AND 
			`z2gz`.`country_id` = " . $country_id . " AND (
				`z2gz`.`zone_id` = '0' OR `z2gz`.`zone_id` = '" . $zone_id . "'
			)
		)";
		
		$conditions[] = "(
			`tr1`.`based` = 'payment' AND 
			`z2gz`.`country_id` = " . $p_country_id . " AND (
				`z2gz`.`zone_id` = '0' OR `z2gz`.`zone_id` = '" . $p_zone_id . "'
			)
		)";
		
		$conditions[] = "(
			`tr1`.`based` = 'shipping' AND 
			`z2gz`.`country_id` = " . $s_country_id . " AND (
				`z2gz`.`zone_id` = '0' OR `z2gz`.`zone_id` = '" . $s_zone_id . "'
			)
		)";	
		
		return implode( ' OR ', $conditions );
	}
	
	private function _taxCol( $type, $alias ) {
		return "
			(
				SELECT
					SUM(`tr2`.`rate`)
				FROM
					`" . DB_PREFIX . "tax_rule` AS `tr1`
				LEFT JOIN
					`" . DB_PREFIX . "tax_rate` AS `tr2`
				ON
					`tr1`.`tax_rate_id` = `tr2`.`tax_rate_id`
				INNER JOIN
					`" . DB_PREFIX . "tax_rate_to_customer_group` AS `tr2cg`
				ON
					`tr2`.`tax_rate_id` = `tr2cg`.`tax_rate_id`
				LEFT JOIN
					`" . DB_PREFIX . "zone_to_geo_zone` AS `z2gz`
				ON
					`tr2`.`geo_zone_id` = `z2gz`.`geo_zone_id`
				WHERE
					`tr1`.`tax_class_id` = `p`.`tax_class_id` AND
					`tr2`.`type` = '" . $type . "' AND
					( " . $this->_taxConditions() . " ) AND
					`tr2cg`.`customer_group_id` = " . $this->_customerGroupId() . "
			)" . ( $alias ? ' AS ' . $alias : '' ) . "
		";
	}
	
	private function _priceCol( $alias = 'price' ) {
		return "
			IFNULL( (" . $this->_specialCol( NULL ) . "), IFNULL( (" . $this->_discountCol( NULL ) . "), `p`.`price` ) )" . ( $alias ? " AS `" . $alias . '`' : '' ) . "
		";
	}
	
	/**
	 * Kolumna tax fixed
	 * 
	 * @return string 
	 */
	private function _fixedTaxCol( $alias = 'f_tax' ) {
		return $this->_taxCol( 'F', $alias );
	}
	
	/**
	 * Kolumna tax percent
	 * 
	 * @return string 
	 */
	private function _percentTaxCol( $alias = 'p_tax' ) {
		return $this->_taxCol( 'P', $alias );
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
		
		if( false != ( $mFilterPlus = $this->mfilterPlus() ) ) {
			$mFilterPlus->baseConditions( $conditions );
		}
		
		return $conditions;
	}
	
	public function _baseJoin( array $skip = array() ) {
		$sql = '';
		
		if( ! in_array( 'p2s', $skip ) ) {
			$sql .= "
				INNER JOIN
					`" . DB_PREFIX . "product_to_store` AS `p2s`
				ON
					`p2s`.`product_id` = `p`.`product_id` AND `p2s`.`store_id` = " . (int) $this->_ctrl->config->get( 'config_store_id' ) . "
			";
		}
		
		if( ( ! empty( $this->_data['filter_name'] ) || ! empty( $this->_data['filter_tag'] ) ) && ! in_array( 'pd', $skip ) ) {
			$sql .= "
				INNER JOIN
					`" . DB_PREFIX . "product_description` AS `pd`
				ON
					`pd`.`product_id` = `p`.`product_id` AND `pd`.`language_id` = " . (int) $this->_ctrl->config->get( 'config_language_id' ) . "
			";
		}
		
		if( ! empty( $this->_data['filter_category_id'] ) || $this->_categories ) {
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
		
		if( false != ( $mFilterPlus = $this->mfilterPlus() ) ) {
			$sql .= $mFilterPlus->baseJoin( $skip );
		}
		
		return $sql;
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
	
	/**
	 * Utwórz zapytanie SQL
	 * 
	 * @param array $columns
	 * @param array $conditions
	 * @param array $group_by
	 * @return type 
	 */
	private function _createSQL( array $columns, array $conditions = array(), array $group_by = array( '`p`.`product_id`' ) ) {
		$conditions	= $this->_baseConditions( $conditions );
		$group_by	= $group_by ? ' GROUP BY ' . implode( ',', $group_by ) : '';
		
		return $this->_createSQLByCategories( str_replace( array( '{COLUMNS}', '{CONDITIONS}', '{GROUP_BY}' ), array( implode( ',', $columns ), implode( ' AND ', $conditions ), $group_by ), sprintf("
			SELECT
				{COLUMNS}
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_description` AS `pd`
			ON
				`pd`.`product_id` = `p`.`product_id` AND `pd`.`language_id` = " . (int) $this->_ctrl->config->get( 'config_language_id' ) . "
			%s
			WHERE
				{CONDITIONS}
			{GROUP_BY}
		", $this->_baseJoin( array( 'pd' ) ) ) ) );
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
		",$sql, $this->_joinProductToCategory( 'mf_p2c', 'tmp' ), $this->_joinCategoryPath(), $this->_categoriesToSQL() );
	}
	
	private static function _route( & $ctrl ) {
		if( isset( $ctrl->request->get['mfilterRoute'] ) )
			return base64_decode( $ctrl->request->get['mfilterRoute'] );
		
		if( isset( $ctrl->request->get['route'] ) )
			return $ctrl->request->get['route'];
		
		return 'common/home';
	}
	
	public function route() {
		return self::_route( $this->_ctrl );
	}
	
	/**
	 * Pobierz min/max cenę
	 * 
	 * @return array 
	 */
	public function getMinMaxPrice() {
		$sel			= '`price_tmp`';
		$columns		= array( $this->_priceCol( 'price_tmp' ) );
		$baseColumns	= $this->_baseColumns();
		
		if( isset( $baseColumns['mf_rating'] ) )
			$columns[] = $baseColumns['mf_rating'];
		
		if( $this->_ctrl->config->get( 'config_tax' ) ) {
			$sel = '( ' . $sel . ' * ( 1 + IFNULL(`p_tax`, 0) / 100 ) + IFNULL(`f_tax`, 0) )';
			$columns[] = $this->_fixedTaxCol();
			$columns[] = $this->_percentTaxCol();
		}
		
		$conditionsOut	= $this->_conditions['out'];
		$conditionsIn	= $this->_conditions['in'];
		
		if( isset( $conditionsOut['mf_price'] ) )
			unset( $conditionsOut['mf_price'] );
		
		if( $this->_attribs || $this->_options || $this->_filters || $this->_categories )
			$columns[] = '`p`.`product_id`';
		
		$conditions		= array();
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $conditions );
		
		if( isset( $conditionsOut['mf_rating'] ) ) {
			$conditions[] = $conditionsOut['mf_rating'];
			unset( $conditionsOut['mf_rating'] );
		}
		
		if( in_array( $this->route(), self::$_specialRoute ) ) {
			$columns[] = $this->_specialCol();
			$conditions[] = '`special` IS NOT NULL';
		}
		
		$conditions = $conditions ? ' WHERE ' . implode( ' AND ', $conditions ) : '';
		
		$sql = sprintf( 
			'SELECT MIN(`price`) AS `p_min`, MAX(`price`) AS `p_max` FROM( SELECT ' . $sel . ' AS `price` FROM( %s ) AS `tmp` %s ) AS `tmp` ' . $this->_conditionsToSQL( $conditionsOut ),
			$this->_createSQL( $columns, $conditionsIn, array() ), $conditions
		);
		
		$query = $this->_ctrl->db->query( $sql );
		
		if( ! $query->num_rows )
			return array( 'min' => 0, 'max' => 0 );
		
		return array(
			'min'	=> floor( $query->row['p_min'] * $this->_ctrl->currency->getValue() ),
			'max'	=> ceil( $query->row['p_max'] * $this->_ctrl->currency->getValue() )
		);
	}
	
	private function _conditionsToSQL( $conditions, $join = ' WHERE ' ) {
		return $conditions ? $join . implode( ' AND ', $conditions ) : '';
	}
	
	/**
	 * Pobierz ilość wg stock status
	 * 
	 * @return array 
	 */
	public function getCountsByStockStatus() {
		$conditionsIn	= $this->_conditions['in'];
		$conditionsOut	= $this->_conditions['out'];
		$columns		= $this->_baseColumns(sprintf(
			'IF( `p`.`quantity` > 0, %s, `p`.`stock_status_id` ) AS `stock_status_id`', $this->inStockStatus()
		));
		
		if( isset( $conditionsIn['stock_status'] ) )
			unset( $conditionsIn['stock_status'] );		
		
		//if( $this->_attribs || $this->_options || $this->_filters ) {
			$columns[] = '`p`.`product_id`';
		//}
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		if( in_array( $this->route(), self::$_specialRoute ) ) {
			$columns[] = $this->_specialCol();
			$conditionsOut[] = '`special` IS NOT NULL';
		}
		
		$sql = sprintf(
			'SELECT COUNT(DISTINCT `product_id`) AS `total`, `stock_status_id` FROM( %s ) AS `tmp` %s GROUP BY `stock_status_id`',
			$this->_createSQL( $columns, $conditionsIn, array() ), $this->_conditionsToSQL( $conditionsOut )
		);
		
		$query = $this->_ctrl->db->query( $sql );		
		$counts = array();
		
		foreach( $query->rows as $row )
			$counts[$row['stock_status_id']] = $row['total'];
		
		return $counts;
	}
	
	/**
	 * Pobierz ilość wg rating'u
	 * 
	 * @return array 
	 */
	public function getCountsByRating() {
		$conditionsIn	= $this->_conditions['in'];
		$conditionsOut	= $this->_conditions['out'];
		$columns		= $this->_baseColumns();
		
		$columns['mf_rating'] = $this->_ratingCol();
		
		if( isset( $conditionsOut['mf_rating'] ) )
			unset( $conditionsOut['mf_rating'] );
		
		//if( $this->_attribs || $this->_options || $this->_filters ) {
			$columns[] = '`p`.`product_id`';
		//}
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		if( in_array( $this->route(), self::$_specialRoute ) ) {
			$columns[] = $this->_specialCol();
			$conditionsOut[] = '`special` IS NOT NULL';
		}
		
		$conditionsOut[] = '`mf_rating` IS NOT NULL';
		
		$sql = sprintf(
			'SELECT COUNT(DISTINCT `product_id`) AS `total`, `mf_rating` FROM( %s ) AS `tmp` %s GROUP BY `mf_rating`',
			$this->_createSQL( $columns, $conditionsIn, array() ), $this->_conditionsToSQL( $conditionsOut )
		);
		
		$query = $this->_ctrl->db->query( $sql );
		$counts = array();
		
		foreach( $query->rows as $row )
			$counts[(int)$row['mf_rating']] = $row['total'];
		
		return $counts;
	}
	
	/**
	 * Pobierz ilość wg manufacturers
	 * 
	 * @return array  
	 */
	public function getCountsByManufacturers() {
		$conditionsIn	= $this->_conditions['in'];
		$conditionsOut	= $this->_conditions['out'];
		$columns		= $this->_baseColumns( '`p`.`manufacturer_id`' );
		
		if( isset( $conditionsIn['manufacturers'] ) )
			unset( $conditionsIn['manufacturers'] );
		
		//if( $this->_attribs || $this->_options || $this->_filters ) {
			$columns[] = '`p`.`product_id`';
		//}
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $conditionsOut );
		
		if( in_array( $this->route(), self::$_specialRoute ) ) {
			$columns[] = $this->_specialCol();
			$conditionsOut[] = '`special` IS NOT NULL';
		}
		
		$sql = sprintf(
			'SELECT COUNT(DISTINCT `product_id`) AS `total`, `manufacturer_id` FROM( %s ) AS `tmp` %s GROUP BY `manufacturer_id`',
			$this->_createSQL( $columns, $conditionsIn, array( '`p`.`product_id`' ) ), $this->_conditionsToSQL( $conditionsOut )
		);
		
		$query = $this->_ctrl->db->query( $sql );
		$counts = array();
		
		foreach( $query->rows as $row ) {
			$counts[$row['manufacturer_id']] = $row['total'];
		}
		
		return $counts;
	}
	
	private function _replaceCounts( array $counts1, array $counts2 ) {
		foreach( $counts2 as $k1 => $v1 ) {
			foreach( $v1 as $k2 => $v2 ) {				
				$counts1[$k1][$k2] = $v2;
			}
		}
		
		return $counts1;
	}
	
	// ATRYBUTY ////////////////////////////////////////////////////////////////
	
	private function _getCountsByAttributes( array $conditions, array $conditionsIn ) {
		$counts	= array();
		
		$conditionsOut		= $this->_conditions['out'];
		$columns			= $this->_baseColumns( '`pa`.`attribute_id`', '`p`.`product_id`', '`pa`.`text`' );
		
		if( in_array( $this->route(), self::$_specialRoute ) ) {
			$columns[] = $this->_specialCol();
			$conditions[] = '`special` IS NOT NULL';
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
		", implode( ',', $columns ), $this->_baseJoin(), implode( ' AND ', $this->_baseConditions( $conditionsIn ) ) ));

		if( $conditionsOut )
			$sql = sprintf( "SELECT * FROM( %s ) AS `tmp` WHERE %s", $sql, implode( ' AND ', $conditionsOut ) );

		$sql = sprintf( "
			SELECT 
				`text`, `attribute_id`, COUNT( DISTINCT `tmp`.`product_id` ) AS `total`
			FROM( %s ) AS `tmp` 
				%s 
			GROUP BY 
				`text`, `attribute_id`
		", $sql, $this->_conditionsToSQL( $conditions ) );
		$cName = __FUNCTION__ . md5( $sql );
		
		if( isset( self::$_cache[$cName] ) )
			return self::$_cache[$cName];
		
		$query = $this->_ctrl->db->query( $sql );
		
		foreach( $query->rows as $row ) {
			if( ! empty( $this->_settings['attribute_separator'] ) ) {
				$texts = explode( $this->_settings['attribute_separator'], $row['text'] );
				
				foreach( $texts as $txt ) {
					if( ! isset( $counts[$row['attribute_id']][md5($txt)] ) )
						$counts[$row['attribute_id']][md5($txt)] = 0;
					
					$counts[$row['attribute_id']][md5($txt)] += $row['total'];
				}
			} else {
				$counts[$row['attribute_id']][md5($row['text'])] = $row['total'];
			}
		}
		
		self::$_cache[$cName] = $counts;
		
		return $counts;
	}
	
	/**
	 * Pobierz ilość wg atrybutów
	 * 
	 * @return array 
	 */
	public function getCountsByAttributes() {
		$attribs	= array_keys( $this->_attribs );
		$ids		= array();
		$counts		= array();
		
		foreach( $attribs as $attrib ) {
			list( $id ) = explode( '-', $attrib );
			
			$id = (int) $id;
			
			if( $id )
				$ids[] = $id;
		}
		
		$conditions = array();
		$conditionsIn = $this->_conditions['in'];
		
		if( $ids )
			$conditions[] = sprintf( '`tmp`.`attribute_id` NOT IN(%s)', implode( ',', $ids ) );
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $conditions );
		
		$counts = $this->_getCountsByAttributes( $conditions, $conditionsIn );
		
		$clearConditions	= array();
		$conditionsIn		= $this->_conditions['in'];
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $clearConditions );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $clearConditions );
		
		$clearCounts = $conditions ? $this->_getCountsByAttributes( $clearConditions, $conditionsIn ) : array();
		
		foreach( $attribs as $key ) {
			$copy			= $this->_attribs;
			$conditions		= array();
			$conditionsIn	= $this->_conditions['in'];
			
			list( $k ) = explode( '-', $key );
			
			unset( $copy[$key] );
			
			if( $copy ) {
				// atrybuty
				$this->_attribsToSQL( '', $copy, $conditionsIn, $conditions );
				
				// opcje
				$this->_optionsToSQL( '', NULL, $conditionsIn, $conditions );
				
				// filtry
				$this->_filtersToSQL( '', NULL, $conditionsIn, $conditions );
				
				$tmp = $this->_getCountsByAttributes( $conditions, $conditionsIn );
				
				if( isset( $tmp[$k] ) ) {
					$counts = $counts + array( $k => $tmp[$k] );
				}
			} else {				
				if( isset( $clearCounts[$k] ) ) {
					$counts = $this->_replaceCounts( $counts, array( $k => $clearCounts[$k] ) );
					//$counts = $counts + array( $k => $clearCounts[$k] );
				}
			}
		}
		
		return $counts;
	}
	
	// OPCJE ///////////////////////////////////////////////////////////////////
	
	private function _getCountsByOptions( array $conditions, array $conditionsIn ) {
		$counts	= array();
		
		$conditionsOut		= $this->_conditions['out'];
		$columns			= $this->_baseColumns( '`pov`.`option_value_id`', '`pov`.`option_id`', '`p`.`product_id`' );
		
		if( in_array( $this->route(), self::$_specialRoute ) ) {
			$columns[] = $this->_specialCol();
			$conditions[] = '`special` IS NOT NULL';
		}
		
		if( ! empty( $this->_settings['in_stock_default_selected'] ) || ( ! empty( $this->_parseParams['stock_status'] ) && in_array( $this->inStockStatus(), $this->_parseParams['stock_status'] ) ) ) {
			$conditionsIn[] = '`pov`.`quantity` > 0';
		}

		$sql = $this->_createSQLByCategories(sprintf( "
			SELECT
				%s
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_option_value` AS `pov`
			ON
				`pov`.`product_id` = `p`.`product_id`
			%s
			WHERE
				%s
		", implode( ',', $columns ), $this->_baseJoin(), implode( ' AND ', $this->_baseConditions( $conditionsIn ) ) ));

		if( $conditionsOut )
			$sql = sprintf( "SELECT * FROM( %s ) AS `tmp` WHERE %s", $sql, implode( ' AND ', $conditionsOut ) );

		$sql = sprintf( "
			SELECT 
				`option_value_id`, `option_id`, COUNT( DISTINCT `tmp`.`product_id` ) AS `total`
			FROM( %s ) AS `tmp` 
				%s 
			GROUP BY 
				`option_id`, `option_value_id`
		", $sql, $this->_conditionsToSQL( $conditions ) );
		
		$cName = __FUNCTION__ . md5( $sql );
		
		if( isset( self::$_cache[$cName] ) )
			return self::$_cache[$cName];
		
		$query = $this->_ctrl->db->query( $sql );

		foreach( $query->rows as $row ) {
			$counts[$row['option_id']][$row['option_value_id']] = $row['total'];
		}
		
		self::$_cache[$cName] = $counts;
		
		return $counts;
	}
	
	/**
	 * Pobierz ilość wg atrybutów
	 * 
	 * @return array 
	 */
	public function getCountsByOptions() {
		$options	= array_keys( $this->_options );
		$ids		= array();
		$counts		= array();
		
		foreach( $options as $attrib ) {
			list( $id ) = explode( '-', $attrib );
			
			$id = (int) $id;
			
			if( $id )
				$ids[] = $id;
		}
		
		$conditions = array();
		$conditionsIn = $this->_conditions['in'];
		
		if( $ids )
			$conditions[] = sprintf( '`tmp`.`option_value_id` NOT IN(%s)', implode( ',', $ids ) );
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $conditions );
		
		$counts = $this->_getCountsByOptions( $conditions, $conditionsIn );
		
		$clearConditions	= array();
		$conditionsIn		= $this->_conditions['in'];
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $clearConditions );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $clearConditions );
		
		$clearCounts = $conditions ? $this->_getCountsByOptions( $clearConditions, $conditionsIn ) : array();
		
		foreach( $options as $key ) {
			$copy			= $this->_options;
			$conditions		= array();
			$conditionsIn	= $this->_conditions['in'];
			
			list( $k ) = explode( '-', $key );
			
			unset( $copy[$key] );
			
			if( $copy ) {
				// opcje
				$this->_optionsToSQL( '', $copy, $conditionsIn, $conditions );
				
				// atrybuty
				$this->_attribsToSQL( '', NULL, $conditionsIn, $conditions );
				
				// filtry
				$this->_filtersToSQL( '', NULL, $conditionsIn, $conditions );
				
				$tmp = $this->_getCountsByOptions( $conditions, $conditionsIn );
				
				if( isset( $tmp[$k] ) ) {
					$counts = $counts + array( $k => $tmp[$k] );
				}
			} else {				
				if( isset( $clearCounts[$k] ) ) {
					$counts = $this->_replaceCounts( $counts, array( $k => $clearCounts[$k] ) );
					//$counts = $counts + array( $k => $clearCounts[$k] );
				}
			}
		}
		
		return $counts;
	}
	
	// FILTRY //////////////////////////////////////////////////////////////////
	
	private function _getCountsByFilters( array $conditions, array $conditionsIn ) {
		$counts	= array();
		
		$conditionsOut		= $this->_conditions['out'];
		$columns			= $this->_baseColumns( '`f`.`filter_group_id`', '`pf`.`filter_id`', '`p`.`product_id`' );
		
		if( in_array( $this->route(), self::$_specialRoute ) ) {
			$columns[] = $this->_specialCol();
			$conditions[] = '`special` IS NOT NULL';
		}

		$sql = $this->_createSQLByCategories(sprintf( "
			SELECT
				%s
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_filter` AS `pf`
			ON
				`pf`.`product_id` = `p`.`product_id`
			INNER JOIN
				`" . DB_PREFIX . "filter` AS `f`
			ON
				`f`.`filter_id` = `pf`.`filter_id`
			%s
			WHERE
				%s
		", implode( ',', $columns ), $this->_baseJoin(), implode( ' AND ', $this->_baseConditions( $conditionsIn ) ) ));

		if( $conditionsOut )
			$sql = sprintf( "SELECT * FROM( %s ) AS `tmp` WHERE %s", $sql, implode( ' AND ', $conditionsOut ) );

		$sql = sprintf( "
			SELECT 
				`filter_id`, `filter_group_id`, COUNT( DISTINCT `tmp`.`product_id` ) AS `total`
			FROM( %s ) AS `tmp` 
				%s 
			GROUP BY 
				`filter_group_id`, `filter_id`
		", $sql, $this->_conditionsToSQL( $conditions ) );
		$cName = __FUNCTION__ . md5( $sql );
		
		if( isset( self::$_cache[$cName] ) )
			return self::$_cache[$cName];
		
		$query = $this->_ctrl->db->query( $sql );

		foreach( $query->rows as $row ) {
			$counts[$row['filter_group_id']][$row['filter_id']] = $row['total'];
		}
		
		self::$_cache[$cName] = $counts;
		
		return $counts;
	}
	
	/**
	 * Pobierz ilość wg filtrów
	 * 
	 * @return array 
	 */
	public function getCountsByFilters() {
		$filters	= array_keys( $this->_filters );
		$ids		= array();
		$counts		= array();
		
		foreach( $filters as $attrib ) {
			list( $id ) = explode( '-', $attrib );
			
			$id = (int) $id;
			
			if( $id )
				$ids[] = $id;
		}
		
		$conditions = array();
		$conditionsIn = $this->_conditions['in'];
		
		if( $ids )
			$conditions[] = sprintf( '`tmp`.`filter_group_id` NOT IN(%s)', implode( ',', $ids ) );
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $conditions );
		
		// filtry
		$this->_filtersToSQL( '', NULL, $conditionsIn, $conditions );
		
		$counts = $this->_getCountsByFilters( $conditions, $conditionsIn );
		
		$clearConditions	= array();
		$conditionsIn		= $this->_conditions['in'];
		
		// atrybuty
		$this->_attribsToSQL( '', NULL, $conditionsIn, $clearConditions );
		
		// opcje
		$this->_optionsToSQL( '', NULL, $conditionsIn, $clearConditions );
		
		$clearCounts = $conditions ? $this->_getCountsByFilters( $clearConditions, $conditionsIn ) : array();
		
		foreach( $filters as $key ) {
			$copy			= $this->_filters;
			$conditions		= array();
			$conditionsIn	= $this->_conditions['in'];
			
			list( $k ) = explode( '-', $key );
			
			unset( $copy[$key] );
			
			if( $copy ) {
				// filtry
				$this->_filtersToSQL( '', $copy, $conditionsIn, $conditions );
				
				// atrybuty
				$this->_attribsToSQL( '', NULL, $conditionsIn, $conditions );
				
				// opcje
				$this->_optionsToSQL( '', NULL, $conditionsIn, $conditions );
				
				$tmp = $this->_getCountsByFilters( $conditions, $conditionsIn );
				
				if( isset( $tmp[$k] ) ) {
					$counts = $counts + array( $k => $tmp[$k] );
				}
			} else {				
				if( isset( $clearCounts[$k] ) ) {
					$counts = $this->_replaceCounts( $counts, array( $k => $clearCounts[$k] ) );
					//$counts = $counts + array( $k => $clearCounts[$k] );
				}
			}
		}
		
		return $counts;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Zamień wszystkie elementy tablicy na int
	 * 
	 * @param array $params
	 * @return array 
	 */
	private function _parseArrayToInt( $params ) {
		foreach( $params as $k => $v ) {
			$v = (int) $v;
			
			if( ! $v ) {
				unset( $params[$k] );
			} else {
				$params[$k] = $v;
			}
		}
		
		return $params;
	}
	
	/**
	 * Zamień wszystkie elementy tablicy na stringi przygotowane do zapytania SQL
	 * 
	 * @param array $params
	 * @return array 
	 */
	private function _parseArrayToStr( $params, $like = false ) {
		foreach( $params as $k => $v ) {
			$v = (string) $v;
			
			if( $v === '' ) {
				unset( $params[$k] );
			} else {
				if( $like && $like != ',' ) {
					$params[$k] = array();
					$params[$k][] = "'" . $this->_ctrl->db->escape( $v ) . "'";
					$params[$k][] = "'%" . $like . $this->_ctrl->db->escape( $v ) . $like . "%'";
					$params[$k][] = "'" . $this->_ctrl->db->escape( $v ) . $like . "%'";
					$params[$k][] = "'%" . $like . $this->_ctrl->db->escape( $v ) . "'";
				} else {
					$params[$k] = "'" . $this->_ctrl->db->escape( $v ) . "'";
				}
			}
		}
		
		return $params;
	}
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ModelModuleMegaFilter extends Model {	
	
	private static $_tmp_sort_parameters = NULL;
	
	/**
	 * Pobierz listę statusów
	 * 
	 * @return array 
	 */
	public function getStockStatuses() {
		$list = array();
		
		foreach( $this->db->query("
			SELECT
				*
			FROM
				`" . DB_PREFIX . "stock_status`
			WHERE
				`language_id` = " . (int) $this->config->get('config_language_id') . "
		")->rows as $row ) {
			$list[] = array(
				'key' => $row['stock_status_id'],
				'value' => $row['stock_status_id'],
				'name' => $row['name']
			);
		}
		
		return $list;
	}
	
	public function getManufacturers() {
		$sql = "
			SELECT 
				`m`.* 
			FROM 
				`" . DB_PREFIX . "manufacturer` AS `m` 
			INNER JOIN 
				`" . DB_PREFIX . "manufacturer_to_store` AS `m2s` 
			ON 
				`m`.`manufacturer_id` = `m2s`.`manufacturer_id` AND `m2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'
			{join}
			{conditions}
			{group}
			ORDER BY 
				`m`.`name` ASC
		";
		
		$core		= MegaFilterCore::newInstance( $this, NULL );
		$data		= MegaFilterCore::_getData( $this );
		$join		= '';
		$group		= array();
		$conditions	= $core->_baseConditions();
		$join		= 'INNER JOIN `' . DB_PREFIX . 'product` AS `p` ON `p`.`manufacturer_id` = `m`.`manufacturer_id`';
		
		if( in_array( $core->route(), MegaFilterCore::$_specialRoute ) ) {
			$conditions[] = '(' . $core->_specialCol( '' ) . ') IS NOT NULL';
		}
		
		if( ! empty( $data['filter_name'] ) || ! empty( $data['filter_category_id'] ) || ! empty( $data['filter_manufacturer_id'] ) || ! empty( $conditions['search'] ) ) {
			$join .= ' ' . $core->_baseJoin();
		}
		
		if( $join ) {
			$group[] = '`m`.`manufacturer_id`';
		}
		
		$group		= $group ? 'GROUP BY ' . implode( ',', $group ) : '';
		$conditions	= $conditions ? 'WHERE ' . implode( ' AND ', $conditions ) : '';
		
		$sql			= str_replace( array( '{join}', '{conditions}', '{group}' ), array( $join, $conditions, $group ), $sql );
		$manufacturers	= array();
		
		foreach( $this->db->query( $sql )->rows as $row ) {
			$manufacturers[] = array(
				'key'	=> $row['manufacturer_id'],
				'value'	=> $row['manufacturer_id'],
				'name'	=> $row['name']
			);
		}
		
		return $manufacturers;
	}
	
	private function stockStatusIsEnabled( $idx ) {		
		$modules	= $this->config->get('mega_filter_module');
		$attribs	= $idx !== NULL && isset( $modules[$idx]['base_attribs'] ) ? $modules[$idx]['base_attribs'] : $this->_settings['attribs'];
		
		return empty( $attribs['stock_status']['enabled'] ) ? false : true;
	}
	
	/**
	 * Utwórz listę filtrów 
	 */
	public function createFilters( $idx, array $config ) {
		$sql = "
			SELECT
				`fgd`.`name` AS `gname`,
				`f`.`filter_group_id`,
				`f`.`filter_id`,
				`fd`.`name`
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_to_store` AS `p2s`
			ON
				`p`.`product_id` = `p2s`.`product_id` AND `p2s`.`store_id` = " . (int) $this->config->get( 'config_store_id' ) . "
			INNER JOIN
				`" . DB_PREFIX . "product_filter` AS `pf`
			ON
				`p`.`product_id` = `pf`.`product_id`
			INNER JOIN
				`" . DB_PREFIX . "filter` AS `f`
			ON
				`pf`.`filter_id` = `f`.`filter_id`
			INNER JOIN
				`" . DB_PREFIX . "filter_description` AS `fd`
			ON
				`pf`.`filter_id` = `fd`.`filter_id` AND `fd`.`language_id` = " . (int) $this->config->get('config_language_id') . "
			INNER JOIN
				`" . DB_PREFIX . "filter_group_description` AS `fgd`
			ON
				`f`.`filter_group_id` = `fgd`.`filter_group_id` AND `fgd`.`language_id` = " . (int) $this->config->get('config_language_id') . "
			{join}
			WHERE
				{conditions}
			GROUP BY
				`f`.`filter_group_id`, `f`.`filter_id`
			ORDER BY
				`f`.`sort_order`, `fd`.`name`
		";
		
		$filter_ids		= array();
		
		if( ! empty( $config['based_on_category'] ) ) {
			$category_id	= isset( $this->request->get['path'] ) ? explode( '_', (string) $this->request->get['path'] ) : array();
			$category_id	= end( $category_id );

			if( ! $category_id )
				return array();

			foreach( $this->db->query( 'SELECT `filter_id` FROM `' . DB_PREFIX . 'category_filter` WHERE `category_id` = ' . (int) $category_id )->rows as $row ) {
				$filter_ids[] = (int) $row['filter_id'];
			}

			if( ! $filter_ids )
				return array();
		}
		
		$core			= MegaFilterCore::newInstance( $this, NULL );
		$conditions		= $core->_baseConditions();
		$filters		= array();
		$join			= $core->_baseJoin(array('p2s','pf'));
		
		if( $filter_ids ) {
			$conditions[]	= sprintf( '`f`.`filter_id` IN(%s)', implode( ',', $filter_ids ) );
		}
		
		if( in_array( $core->route(), MegaFilterCore::$_specialRoute ) ) {
			$conditions[] = '(' . $core->_specialCol( '' ) . ') IS NOT NULL';
		}
		
		if( ! $this->stockStatusIsEnabled( $idx ) && ! empty( $core->_settings['in_stock_default_selected'] ) ) {
			$conditions[] = sprintf( '( `p`.`quantity` > 0 OR `p`.`stock_status_id` = %s )', $core->inStockStatus() );
		}
		
		$sql	= str_replace( array( '{conditions}', '{join}' ), array( implode( ' AND ', $conditions ), $join ), $sql );
		$sort	= array();
		
		foreach( $this->db->query( $sql )->rows as $filter ) {
			if( empty( $config[$filter['filter_group_id']]['enabled'] ) ) continue;
			
			$item = $config[$filter['filter_group_id']];
			
			if( ! isset( $filters['f_'.$filter['filter_group_id']] ) ) {
				$filters['f_'.$filter['filter_group_id']] = array(
					'id'					=> $filter['filter_group_id'],
					'type'					=> $item['type'],
					'base_type'				=> 'filter',
					'sort_order'			=> $item['sort_order'],
					'name'					=> $filter['gname'],
					'seo_name'				=> $filter['filter_group_id'] . 'f-' . $this->clear( $filter['gname'] ),
					'options'				=> array(),
					'collapsed'				=> empty( $item['collapsed'] ) ? false : $item['collapsed'],
					'display_list_of_items'	=> empty( $item['display_list_of_items'] ) ? '' : $item['display_list_of_items'],
					'display_live_filter'	=> empty( $item['display_live_filter'] ) ? '' : $item['display_live_filter']
				);
			}
			
			if( ! empty( $item['sort_order_values'] ) )
				$sort['f_'.$filter['filter_group_id']] = $item['sort_order_values'];
			
			$filters['f_'.$filter['filter_group_id']]['options'][$filter['filter_id']] = array(
				'name' => $filter['name'],
				'value' => $filter['filter_id'],
				'key' => $filter['filter_id']
			);
		}
		
		foreach( $sort as $filter_group_id => $type ) {
			$this->_sortOptions( $filters[$filter_group_id]['options'], $type, true );
		}
		
		return $filters;
	}
	
	/**
	 * Utwórz listę opcji 
	 */
	public function createOptions( $idx, array $opts ) {
		$sql = "
			SELECT
				`o`.`option_id`,
				`ov`.`option_value_id`,
				`od`.`name` AS `gname`,
				`ov`.`image`,
				`ovd`.`name`
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_to_store` AS `p2s`
			ON
				`p`.`product_id` = `p2s`.`product_id` AND `p2s`.`store_id` = " . (int) $this->config->get( 'config_store_id' ) . "
			INNER JOIN
				`" . DB_PREFIX . "product_option_value` AS `pov`
			ON
				`p`.`product_id` = `pov`.`product_id`
			INNER JOIN
				`" . DB_PREFIX . "option_value` AS `ov`
			ON
				`ov`.`option_value_id` = `pov`.`option_value_id`
			INNER JOIN
				`" . DB_PREFIX . "option_value_description` AS `ovd`
			ON
				`ov`.`option_value_id` = `ovd`.`option_value_id` AND `ovd`.`language_id` = " . (int) $this->config->get('config_language_id') . "
			INNER JOIN
				`" . DB_PREFIX . "option` AS `o`
			ON
				`o`.`option_id` = `pov`.`option_id`
			INNER JOIN
				`" . DB_PREFIX . "option_description` AS `od`
			ON
				`od`.`option_id` = `o`.`option_id` AND `od`.`language_id` = " . (int) $this->config->get('config_language_id') . "
			{join}
			WHERE
				{conditions}
			GROUP BY
				`o`.`option_id`, `ov`.`option_value_id`
			ORDER BY
				`ov`.`sort_order`, `ovd`.`name`
		";
		
		$this->load->model('tool/image');
		
		$core			= MegaFilterCore::newInstance( $this, NULL );
		$conditions		= $core->_baseConditions();
		$conditions[]	= "`o`.`type` IN('radio','checkbox','select','image')";
		$options		= array();
		$join			= $core->_baseJoin(array('p2s'));
		
		if( in_array( $core->route(), MegaFilterCore::$_specialRoute ) ) {
			$conditions[] = '(' . $core->_specialCol( '' ) . ') IS NOT NULL';
		}
		
		if( ! $this->stockStatusIsEnabled( $idx ) && ! empty( $core->_settings['in_stock_default_selected'] ) ) {
			$conditions[] = '`pov`.`quantity` > 0';
			$conditions[] = sprintf( '( `p`.`quantity` > 0 OR `p`.`stock_status_id` = %s )', $core->inStockStatus() );
		}
		
		$sql	= str_replace( array( '{conditions}', '{join}' ), array( implode( ' AND ', $conditions ), $join ), $sql );
		$sort	= array();
		
		foreach( $this->db->query( $sql )->rows as $option ) {
			if( empty( $opts[$option['option_id']]['enabled'] ) ) continue;
			
			$item = $opts[$option['option_id']];
			
			if( ! isset( $options['o_'.$option['option_id']] ) ) {
				$options['o_'.$option['option_id']] = array(
					'id'					=> $option['option_id'],
					'type'					=> $item['type'],
					'base_type'				=> 'option',
					'sort_order'			=> $item['sort_order'],
					'name'					=> $option['gname'],
					'seo_name'				=> $option['option_id'] . 'o-' . $this->clear( $option['gname'] ),
					'options'				=> array(),
					'collapsed'				=> empty( $item['collapsed'] ) ? false : $item['collapsed'],
					'display_list_of_items'	=> empty( $item['display_list_of_items'] ) ? '' : $item['display_list_of_items'],
					'display_live_filter'	=> empty( $item['display_live_filter'] ) ? '' : $item['display_live_filter']
				);
			}
			
			if( ! empty( $item['sort_order_values'] ) )
				$sort['o_'.$option['option_id']] = $item['sort_order_values'];
			
			$value = array(
				'name'	=> $option['name'],
				'value'	=> $option['option_value_id'],
				'key' => $option['option_value_id']
			);
			
			if( in_array( $item['type'], array( 'image', 'image_list_radio', 'image_list_checkbox' ) ) )
				$value['image'] = empty( $option['image'] ) ? $this->model_tool_image->resize('no_image.jpg', 20, 20) : $this->model_tool_image->resize($option['image'], 20, 20);
			
			$options['o_'.$option['option_id']]['options'][$option['option_value_id']] = $value;
		}
		
		foreach( $sort as $option_id => $type ) {
			$this->_sortOptions( $options[$option_id]['options'], $type, true );
		}
		
		return $options;
	}
	
	/**
	 * Utwórz listę atrybutów
	 * 
	 * @param array $attribs
	 * @return type 
	 */
	public function createAttribs( $idx, array $attribs ) {
		$sql = "
			SELECT
				`a`.`attribute_id`,
				REPLACE(REPLACE(TRIM(pa.text), '\r', ''), '\n', '') AS `txt`,
				`ad`.`name`,
				`agd`.`name` AS `gname`,
				`agd`.`attribute_group_id`
			FROM
				`" . DB_PREFIX . "product` AS `p`
			INNER JOIN
				`" . DB_PREFIX . "product_to_store` AS `pts`
			ON
				`p`.`product_id` = `pts`.`product_id` AND `pts`.`store_id` = " . (int) $this->config->get( 'config_store_id' ) . "
			INNER JOIN
				`" . DB_PREFIX . "product_attribute` AS `pa`
			ON
				`p`.`product_id` = `pa`.`product_id` AND `pa`.`language_id` = " . (int)$this->config->get('config_language_id') . "
			INNER JOIN
				`" . DB_PREFIX . "attribute` AS `a`
			ON
				`a`.`attribute_id` = `pa`.`attribute_id`
			INNER JOIN
				`" . DB_PREFIX . "attribute_description` AS `ad`
			ON
				`ad`.`attribute_id` = `a`.`attribute_id` AND `ad`.`language_id` = " . (int) $this->config->get('config_language_id') . "
			INNER JOIN
				`" . DB_PREFIX . "attribute_group` AS `ag`
			ON
				`ag`.`attribute_group_id` = `a`.`attribute_group_id`
			INNER JOIN
				`" . DB_PREFIX . "attribute_group_description` AS `agd`
			ON
				`agd`.`attribute_group_id` = `ag`.`attribute_group_id` AND `agd`.`language_id` = " . (int)$this->config->get('config_language_id') . "
			{join}
			WHERE
				{conditions}
			GROUP BY
				`txt`, `pa`.`attribute_id`
			HAVING 
				`txt` != ''
			ORDER BY
				`txt`
		";
		
		$this->load->model('tool/image');
		
		$core		= MegaFilterCore::newInstance( $this, NULL );
		$conditions	= $core->_baseConditions();
		$attributes = array();
		$join		= $core->_baseJoin(array('p2s'));
		$settings	= $this->config->get('mega_filter_settings');
		
		if( in_array( $core->route(), MegaFilterCore::$_specialRoute ) ) {
			$conditions[] = '(' . $core->_specialCol( '' ) . ') IS NOT NULL';
		}
		
		if( ! $this->stockStatusIsEnabled( $idx ) && ! empty( $core->_settings['in_stock_default_selected'] ) ) {
			$conditions[] = sprintf( '( `p`.`quantity` > 0 OR `p`.`stock_status_id` = %s )', $core->inStockStatus() );
		}
		
		$sql	= str_replace( array( '{conditions}', '{join}' ), array( implode( ' AND ', $conditions ), $join ), $sql );
		$sort	= array();
		
		foreach( $this->db->query( $sql )->rows as $attribute ) {
			if( empty( $attribs[$attribute['attribute_group_id']]['items'][$attribute['attribute_id']]['enabled'] ) ) continue;
			
			$item = $attribs[$attribute['attribute_group_id']]['items'][$attribute['attribute_id']];
			$images = (array) $this->config->get('mega_filter_at_img_' . $attribute['attribute_id'] . '_' . $this->config->get('config_language_id'));
			
			if( ! isset( $attributes['a_'.$attribute['attribute_id']] ) ) {
				$attributes['a_'.$attribute['attribute_id']] = array(
					'id'					=> $attribute['attribute_id'],
					'group_id'				=> $attribute['attribute_group_id'],
					'type'					=> $item['type'],
					'base_type'				=> 'attribute',
					'sort_order'			=> empty( $attribs[$attribute['attribute_group_id']]['sort_order'] ) ? 0 : (int) $attribs[$attribute['attribute_group_id']]['sort_order'],
					'sort_order-sec'		=> $item['sort_order'],
					'name'					=> $attribute['name'],
					'seo_name'				=> $attribute['attribute_id'] . '-' . $this->clear( $attribute['name'] ),
					'options'				=> array(),
					'collapsed'				=> empty( $item['collapsed'] ) ? false : $item['collapsed'],
					'display_list_of_items'	=> empty( $item['display_list_of_items'] ) ? '' : $item['display_list_of_items'],
					'display_live_filter'	=> empty( $item['display_live_filter'] ) ? '' : $item['display_live_filter']
				);
			}
			
			if( ! empty( $settings['attribute_separator'] ) ) {
				$attribute['txt'] = explode( $settings['attribute_separator'], $attribute['txt'] );
			} else {
				$attribute['txt'] = array( $attribute['txt'] );
			}
			
			$unique	= array();
			foreach( $attribute['txt'] as $text ) {
				if( isset( $unique[$text] ) ) continue;
				
				$k				= md5( $text );
				$unique[$text]	= true;
				$value			= array(
					'name' => $text,
					'value' => $text,
					'key' => $k
				);
				
				if( in_array( $item['type'], array( 'image', 'image_list_radio', 'image_list_checkbox' ) ) )
					$value['image'] = isset( $images[$k] ) ? $this->model_tool_image->resize($images[$k], 20, 20) : $this->model_tool_image->resize('no_image.jpg', 20, 20);
				
				$attributes['a_'.$attribute['attribute_id']]['options'][] = $value;
			}
			
			if( ! empty( $item['sort_order_values'] ) )
				$sort['a_'.$attribute['attribute_id']] = $item['sort_order_values'];
		}
		
		if( ! empty( $settings['attribute_separator'] ) ) {
			foreach( $attributes as $attribute_id => $attribute ) {
				if( ! empty( $attribute['options'] ) ) {					
					$this->_sortOptions( $attribute['options'], empty( $sort[$attribute_id] ) ? '' : $sort[$attribute_id], false, $attribute_id );
				}
				
				$attributes[$attribute_id] = $attribute;
			}
		} else {
			foreach( $attributes as $attribute_id => $attribute ) {
				$this->_sortOptions( $attributes[$attribute_id]['options'], empty( $sort[$attribute_id] ) ? '' : $sort[$attribute_id], false, $attribute_id );
			}
			//foreach( $sort as $attribute_id => $type ) {
			//	$this->_sortOptions( $attributes[$attribute_id]['options'], $type, false, $attribute_id );
			//}
		}
		
		return $attributes;
	}
	
	/**
	 * Utwórz listę kategorii 
	 */
	public function createCategories( & $core, $idx, $config ) {
		$categories = array();
		$params		= $core->getParseParams();
		
		foreach( $config as $key => $category ) {
			$row = array(
				'sort_order'	=> $category['sort_order'],
				'type'			=> $category['type'],
				'base_type'		=> 'categories',
				'collapsed'		=> empty( $category['collapsed'] ) ? false : $category['collapsed'],
				'show_button'	=> empty( $category['show_button'] ) ? false : true,
				'auto_levels'	=> empty( $category['auto_levels'] ) ? false : true,
				'name'			=> empty( $category['name'][$this->config->get('config_language_id')] ) ? current( $category['name'] ) : $category['name'][$this->config->get('config_language_id')],
			);
			
			$row['seo_name']	= 'c-' . $this->clear( $row['name'] ? $row['name'] : 'cat' ) . '-' . $key;
			$values				= empty( $params[$row['seo_name']] ) ? array() : $params[$row['seo_name']];
			
			switch( $category['type'] ) {
				case 'related' : {
					$row['levels']		= array();
					$row['labels']		= array();
					$root_category_id	= empty( $category['root_category_id'] ) ? NULL : $category['root_category_id'];
					$start_id			= 0;
					
					if( ! empty( $category['root_category_type'] ) ) {
						switch( $category['root_category_type'] ) {
							case 'by_url' : {
								if( ! empty( $this->request->get['path'] ) ) {
									$path = explode( '_', $this->request->get['path'] );
									$start_id = count( $path ) - 1;
									$root_category_id = end( $path );
								}
								
								break;
							}
							case 'top_category' : {
								$root_category_id = 0;
								
								break;
							}
						}
					}
					
					if( ! empty( $category['levels'] ) ) {						
						$levels = $category['levels'];
							
						foreach( $category['levels'] as $level ) {
							$row['labels'][] = empty( $level[$this->config->get('config_language_id')] ) ? $this->language->get('text_select') : $level[$this->config->get('config_language_id')];
						}
						
						if( $start_id ) {
							if( empty( $category['auto_levels'] ) ) {
								$levels = array_slice( $levels, $start_id );
							} else {
								$levels = array_slice( $levels, $start_id, count( $values ) ? count( $values ) : 1 );
							}
							
							//$values = array_slice( $values, $start_id );
							$row['labels'] = array_slice( $row['labels'], $start_id );
						}
						
						$level_id = 0;
						foreach( $levels as $level ) {
							$level = array(
								'name'			=> $row['labels'][$level_id],
								'options'		=> array()
							);
							$value = empty( $values[$level_id-1] ) ? $root_category_id : $values[$level_id-1];
							
							if( ! $row['levels'] || $value ) {
								if( $value ) {
									$this->load->model('catalog/category');
									
									foreach( $this->model_catalog_category->getCategories( $value ) as $cat ) {
										$level['options'][$cat['category_id']] = $cat['name'];
									}
									
									//if( isset( $values[$level_id-1] ) ) {
										if( $level_id && ! isset( $row['levels'][$level_id-1]['options'][$value] ) ) {
											if( ! empty( $category['auto_levels'] ) ) {
												break;
											} else {
												$level['options'] = array();
											}
										}
									//}
								}
								
								if( ! $level['options'] && ! $value ) {
									$row = NULL;
									break;
								}
							}
							
							$row['levels'][] = $level;
							$level_id++;
						}
					} else {
						$row = NULL;
					}
					
					break;
				}
			}
			
			if( $row != NULL && ! empty( $row['levels'] ) )
				$categories[] = $row;
		}
		
		return $categories;
	}
	
	private static function _sortItems( $a, $b ) {
		if( ! self::$_tmp_sort_parameters['type'] && self::$_tmp_sort_parameters['attribute_id'] !== NULL && self::$_tmp_sort_parameters['config'] ) {
			$as = isset( self::$_tmp_sort_parameters['config'][md5($a['name'])] ) ? self::$_tmp_sort_parameters['config'][md5($a['name'])] : count(self::$_tmp_sort_parameters['config']);
			$bs = isset( self::$_tmp_sort_parameters['config'][md5($b['name'])] ) ? self::$_tmp_sort_parameters['config'][md5($b['name'])] : count(self::$_tmp_sort_parameters['config']);
			
			if( $as > $bs )
				return 1;
			
			if( $as < $bs )
				return -1;
			
			return 0;
		} else {		
			$fn = 'sort';

			if( self::$_tmp_sort_parameters['order'] == 'desc' )
				$fn = 'r' . $fn;
			
			if( self::$_tmp_sort_parameters['a'] )
				$fn = 'a' . $fn;

			$tmp = self::$_tmp_sort_parameters['order'] == 'desc' ? array( $b['name'], $a['name'] ) : array( $a['name'], $b['name'] );
			
			switch( self::$_tmp_sort_parameters['type'] ) {
				case 'string' : {
					call_user_func_array( $fn, array( & $tmp, SORT_STRING ) );

					break;
				}
				case 'numeric' : {
					call_user_func_array( $fn, array( & $tmp, SORT_NUMERIC ) );

					break;
				}
			}
			
			return $tmp[0] == $a ? 0 : 1;
		}
	}
	
	private function _sortOptions( & $options, $sort, $a = false, $attribute_id = NULL ) {		
		if( $sort ) {
			list( $type, $order ) = explode( '_', $sort );
		} else {
			$type = $order = '';
		}
		
		self::$_tmp_sort_parameters = array(
			'attribute_id'	=> $attribute_id,
			'type'			=> $type,
			'order'			=> $order,
			'a'				=> $a,
			'config'		=> $this->config->get('mega_filter_at_sort_' . $attribute_id . '_' . $this->config->get('config_language_id') )
		);
		
		if( ! self::$_tmp_sort_parameters['type'] && ! self::$_tmp_sort_parameters['config'] ) {
			self::$_tmp_sort_parameters = NULL;
			
			return;
		}
		
		usort( $options, 'ModelModuleMegaFilter::_sortItems' );
		
		self::$_tmp_sort_parameters = NULL;
	}
		
	/**
	 * Pobierz listę atrybutów
	 * 
	 * @return array 
	 */
	public function getAttributes( & $core, $idx, array $base_attribs, array $attribs, array $opts, array $filters, array $categories ) {
		$attributes = 
			$this->createAttribs( $idx, $attribs ) + 
			$this->createOptions( $idx, $opts ) + 
			( MegaFilterCore::hasFilters() ? $this->createFilters( $idx, $filters ) : array() ) + 
			$this->createCategories( $core, $idx, $categories );
		
		/**
		 * Dodaj podstawowe atrybuty
		 */
		if( ! empty( $base_attribs ) ) {
			foreach( $base_attribs as $type => $attribute ) {
				if( empty( $attribute['enabled'] ) ) continue;
				
				if( ( $type == 'search' || $type == 'search_oc' ) && ( isset( $this->request->get['search'] ) || in_array( MegaFilterCore::newInstance( $this, NULL )->route(), MegaFilterCore::$_searchRoute ) ) ) {
					continue;
				}
				
				$attribute['type']					= isset( $attribute['display_as_type'] ) ? $attribute['display_as_type'] : $type;
				$attribute['base_type']				= $type;
				$attribute['id']					= $type;
				$attribute['sort_order']			= (int) $attribute['sort_order'];
				$attribute['name']					= $this->language->get( 'name_' . $type );
				$attribute['seo_name']				= $type;
				$attribute['collapsed']				= empty( $attribute['collapsed'] ) ? false : $attribute['collapsed'];
				$attribute['display_list_of_items']	= empty( $attribute['display_list_of_items'] ) ? '' : $attribute['display_list_of_items'];
				$attribute['display_live_filter']	= empty( $attribute['display_live_filter'] ) ? '' : $attribute['display_live_filter'];
				
				if( $type == 'manufacturers' ) {
					$attribute['options'] = $this->getManufacturers();
					
					if( ! $attribute['options'] )
						continue;
				} else if( $type == 'stock_status' ) {
					$attribute['options'] = $this->getStockStatuses();
					
					if( ! $attribute['options'] )
						continue;
				}
				
				$attributes[] = $attribute;
			}
		}
		
		/**
		 * Posortuj 
		 */
		usort( $attributes, 'ModelModuleMegaFilter::_sortAttributes' );
		
		return $attributes;
	}
	
	/**
	 * Sortowanie atrybutów
	 * 
	 * @param type $a
	 * @param type $b
	 * @return int 
	 */
	private static function _sortAttributes( $a, $b ) {
		/*$sa = isset( $a['sort_order-sec'] ) ? $a['sort_order-sec'] : $a['sort_order'];
		$sb = isset( $b['sort_order-sec'] ) ? $b['sort_order-sec'] : $b['sort_order'];
		
		if( $sa < $sb )
			return -1;
		
		if( $sa > $sb )
			return 1;
		
		return 0;*/
		
		
		if( ! isset( $a['sort_order-sec'] ) || ! isset( $b['sort_order-sec'] ) ) {
			if( (int) $a['sort_order'] < (int) $b['sort_order'] )
				return -1;
		
			if( (int) $a['sort_order'] > (int) $b['sort_order'] )
				return 1;
			
			return 0;
		}
		
		if( (int) $a['sort_order-sec'] < (int) $b['sort_order-sec'] )
			return -1;
		
		if( (int) $a['sort_order-sec'] > (int) $b['sort_order-sec'] )
			return 1;
		
		if( (int) $a['sort_order'] < (int) $b['sort_order'] )
			return -1;
		
		if( (int) $a['sort_order'] > (int) $b['sort_order'] )
			return 1;
		
		return 0;
	}
	
	/**
	 * Wyczyść adres URL
	 * 
	 * @param string $str
	 * @param bool $clearOn
	 * @return string 
	 */
	public function clear( $str, $clearOn = true ) {
		$str = str_replace(array(
			'`', '~', '!', '@', '#', '$', '%', '^', '*', '(', ')', '+', '=', '[', '{', ']', '}', '\\', '|', ';', ':', "'", '"', ',', '<', '.', '>', '/', '?'
		), ' ', str_replace(array(
			'&'
		), array(
			'and'
		), htmlspecialchars_decode( $str )) );		
		
		if( ! $clearOn )
			return mb_strtolower( trim( preg_replace( '/-+/', '-', preg_replace( '/ +/', '-', $str ) ), '-' ), 'utf-8' );
		
		$unPretty = array(
			'À', 'à', 'Á', 'á', 'Â', 'â', 'Ã', 'ã', 'Ä', 'ä', 'Å', 'å', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ǟ', 'ǟ', 'Ǻ', 'ǻ', 'Α', 'α', 'ъ',
			'Ḃ', 'ḃ', 'Б', 'б',
			'Ć', 'ć', 'Ç', 'ç', 'Č', 'č', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Ч', 'ч', 'Χ', 'χ',
			'Ḑ', 'ḑ', 'Ď', 'ď', 'Ḋ', 'ḋ', 'Đ', 'đ', 'Ð', 'ð', 'Д', 'д', 'Δ', 'δ',
			'Ǳ',  'ǲ', 'ǳ', 'Ǆ', 'ǅ', 'ǆ', 
			'È', 'è', 'É', 'é', 'Ě', 'ě', 'Ê', 'ê', 'Ë', 'ë', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ę', 'ę', 'Ė', 'ė', 'Ʒ', 'ʒ', 'Ǯ', 'ǯ', 'Е', 'е', 'Э', 'э', 'Ε', 'ε',
			'Ḟ', 'ḟ', 'ƒ', 'Ф', 'ф', 'Φ', 'φ',
			'ﬁ', 'ﬂ', 
			'Ǵ', 'ǵ', 'Ģ', 'ģ', 'Ǧ', 'ǧ', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ǥ', 'ǥ', 'Г', 'г', 'Γ', 'γ',
			'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ж', 'ж', 'Х', 'х',
			'Ì', 'ì', 'Í', 'í', 'Î', 'î', 'Ĩ', 'ĩ', 'Ï', 'ï', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'И', 'и', 'Η', 'η', 'Ι', 'ι',
			'Ĳ', 'ĳ', 
			'Ĵ', 'ĵ',
			'Ḱ', 'ḱ', 'Ķ', 'ķ', 'Ǩ', 'ǩ', 'К', 'к', 'Κ', 'κ',
			'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Л', 'л', 'Λ', 'λ',
			'Ǉ', 'ǈ', 'ǉ', 
			'Ṁ', 'ṁ', 'М', 'м', 'Μ', 'μ',
			'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'Ñ', 'ñ', 'ŉ', 'Ŋ', 'ŋ', 'Н', 'н', 'Ν', 'ν',
			'Ǌ', 'ǋ', 'ǌ', 
			'Ò', 'ò', 'Ó', 'ó', 'Ô', 'ô', 'Õ', 'õ', 'Ö', 'ö', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ø', 'ø', 'Ő', 'ő', 'Ǿ', 'ǿ', 'О', 'о', 'Ο', 'ο', 'Ω', 'ω',
			'Œ', 'œ', 
			'Ṗ', 'ṗ', 'П', 'п', 'Π', 'π',
			'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Р', 'р', 'Ρ', 'ρ', 'Ψ', 'ψ',
			'Ś', 'ś', 'Ş', 'ş', 'Š', 'š', 'Ŝ', 'ŝ', 'Ṡ', 'ṡ', 'ſ', 'ß', 'С', 'с', 'Ш', 'ш', 'Щ', 'щ', 'Σ', 'σ', 'ς',
			'Ţ', 'ţ', 'Ť', 'ť', 'Ṫ', 'ṫ', 'Ŧ', 'ŧ', 'Þ', 'þ', 'Т', 'т', 'Ц', 'ц', 'Θ', 'θ', 'Τ', 'τ',
			'Ù', 'ù', 'Ú', 'ú', 'Û', 'û', 'Ũ', 'ũ', 'Ü', 'ü', 'Ů', 'ů', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ų', 'ų', 'Ű', 'ű', 'У', 'у',
			'В', 'в', 'Β', 'β',
			'Ẁ', 'ẁ', 'Ẃ', 'ẃ', 'Ŵ', 'ŵ', 'Ẅ', 'ẅ',
			'Ξ', 'ξ',
			'Ỳ', 'ỳ', 'Ý', 'ý', 'Ŷ', 'ŷ', 'Ÿ', 'ÿ', 'Й', 'й', 'Ы', 'ы', 'Ю', 'ю', 'Я', 'я', 'Υ', 'υ',
			'Ź', 'ź', 'Ž', 'ž', 'Ż', 'ż', 'З', 'з', 'Ζ', 'ζ',
			'Æ', 'æ', 'Ǽ', 'ǽ', 'а', 'А'
		);
		$pretty   = array(
			'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A',
			'B', 'b', 'B', 'b',
			'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'CH', 'ch', 'CH', 'ch',
			'D', 'd', 'D', 'd', 'D', 'd', 'D', 'd', 'D', 'd', 'D', 'd', 'D', 'd',
			'DZ', 'Dz', 'dz', 'DZ', 'Dz', 'dz',
			'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e',
			'F', 'f', 'f', 'F', 'f', 'F', 'f',
			'fi', 'fl',
			'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
			'H', 'h', 'H', 'h', 'ZH', 'zh', 'H', 'h',
			'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
			'IJ', 'ij',
			'J', 'j',
			'K', 'k', 'K', 'k', 'K', 'k', 'K', 'k', 'K', 'k',
			'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l',
			'LJ', 'Lj', 'lj',
			'M', 'm', 'M', 'm', 'M', 'm',
			'N', 'n', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'N', 'n', 'N', 'n', 'N', 'n',
			'NJ', 'Nj', 'nj',
			'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o',
			'OE', 'oe',
			'P', 'p', 'P', 'p', 'P', 'p', 'PS', 'ps',
			'R', 'r', 'R', 'r', 'R', 'r', 'R', 'r', 'R', 'r',
			'S', 's', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 's', 'ss', 'S', 's', 'SH', 'sh', 'SHCH', 'shch', 'S', 's', 's',
			'T', 't', 'T', 't', 'T', 't', 'T', 't', 'T', 't', 'T', 't', 'TS', 'ts', 'TH', 'th', 'T', 't',
			'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
			'V', 'v', 'V', 'v',
			'W', 'w', 'W', 'w', 'W', 'w', 'W', 'w',
			'X', 'x',
			'Y', 'y', 'Y', 'y', 'Y', 'y', 'Y', 'y', 'Y', 'y', 'Y', 'y', 'YU', 'yu', 'YA', 'ya', 'Y', 'y',
			'Z', 'z', 'Z', 'z', 'Z', 'z', 'Z', 'z', 'Z', 'z',
			'AE', 'ae', 'AE', 'ae', 'a', 'A'
		);
		
		$str = mb_strtolower( str_replace( $unPretty, $pretty, $str ), 'utf-8' );
		$str = trim( preg_replace('/[^A-Z^a-z^0-9]+/','-', $str), '-');
		return preg_replace( '/-+/', '-', $str );
	}
}