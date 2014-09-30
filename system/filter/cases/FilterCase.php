<?php 


function quote_escape($str) {
	global $registry;

	return '"' . $registry->get('db')->escape(chop($str)) . '"';
}


class FilterCase implements FilterCaseInterface 
{
	
	
	// Filter data
	protected $category_id;
	protected $attributes;
	protected $options;

	// SQL data
	public $select = array();
	public $from = "";
	public $where = array(
		"product_filter" => array()
	);
	public $join = array();
	public $group_by = array();
	public $order_by = array();


	/**
	 * Store category id and set filters for SQL
	 *
	 * @return void
	 */
	public function setCategory($category_id)
	{
		$this->category_id = $category_id;

		// Update where
		$this->where['product_filter']['where']['category'] = 'sub_ptc.category_id = ' . $category_id;
	}


	/**
	 * Store attributes id's and set filters for SQL
	 *
	 * @return void
	 */
	public function setAttributes($attributes = array())
	{
		$this->attributes = $attributes;

		// Update where
		if (count($attributes))
		{
			$this->where['product_filter']['where']['attributes'] = 'sub_pa.text IN (' . implode(',', array_map("quote_escape", $this->attributes)) . ')';
			$this->where['product_filter']['group_by'][] = ' sub_ptc.product_id ';
			$this->where['product_filter']['having'][] = ' COUNT(DISTINCT sub_pa.text) = ' . count($this->attributes) . ' ';
		}
		
		$this->where['product_filter']['join']['attributes'] = ' LEFT JOIN product_attribute AS sub_pa ON (sub_pa.product_id = sub_ptc.product_id) ';

		$this->join['attributes_3'] = " LEFT JOIN attribute AS a ON (a.attribute_id = main_pa.attribute_id) ";
		$this->join['attributes_4'] = " LEFT JOIN attribute_description AS ad ON (ad.attribute_id = main_pa.attribute_id) ";
		$this->join['attributes_5'] = " LEFT JOIN attribute_group_description AS agd ON (a.attribute_group_id = agd.attribute_group_id) ";
	}


	/**
	 * Store options id's and set filters for SQL
	 *
	 * @return void
	 */
	public function setOptions($options = array())
	{
		$this->options = $options;

		// Update where
		if (count($options))
		{
			$this->where['product_filter']['where']['options'] = 'sub_pov.option_value_id IN (' . implode(",", $options) . ')';
			$this->where['product_filter']['group_by'][] = ' sub_pov.product_id ';
			$this->where['product_filter']['having'][] = ' COUNT(DISTINCT sub_pov.option_value_id) = ' . count($options) . ' ';
		}

		$this->where['product_filter']['join']['options'] = ' LEFT join product_option_value AS sub_pov on (sub_ptc.product_id = sub_pov.product_id) ';

		$this->where[] = 'ov.option_value_id IN (select option_value_id from product_option_value where product_id = main_po.product_id)';

		$this->join['options_2'] = " LEFT JOIN `option` AS o ON (o.option_id = main_po.option_id) ";
		$this->join['options_3'] = " LEFT JOIN option_description AS od ON (od.option_id = main_po.option_id) ";
		$this->join['options_4'] = " LEFT JOIN option_value AS ov ON (ov.option_id = main_po.option_id) ";
		$this->join['options_5'] = " LEFT JOIN option_value_description AS ovd ON (ovd.option_value_id = ov.option_value_id) ";
	}

	
}