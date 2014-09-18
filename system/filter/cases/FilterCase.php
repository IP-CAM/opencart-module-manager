<?php 


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
			$this->where['product_filter']['where']['attributes'] = 'sub_pa.attribute_id IN (' . implode(",", $attributes) . ')';
		}
		$this->where['product_filter']['join']['attributes'] = ' JOIN product_attribute AS sub_pa ON (sub_pa.product_id = sub_ptc.product_id) ';
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
		}
		$this->where['product_filter']['where']['options_filter'] = 'ov.option_value_id IN (select option_value_id from product_option_value where product_id = po.product_id)';
		$this->where['product_filter']['join']['options'] = ' join product_option_value AS sub_pov on (sub_ptc.product_id = sub_pov.product_id) ';
	}

	
}