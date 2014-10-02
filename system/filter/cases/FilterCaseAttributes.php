<?php 


class FilterCaseAttributes extends FilterCase implements FilterCaseInterface 
{
	
	// SQL data
	public $select = array(
		"main_pa.product_id AS product_id",
		"main_pa.attribute_id AS attr_id",
		"main_pa.text AS attr_text",
		"a.attribute_group_id AS attr_group_id",
		"ad.name AS attr_name",
		"a.sort_order AS attr_group_order",
		"agd.name AS attr_group_name" 
	);

	public $from = "product_attribute AS main_pa";

	public $where = array(
		"language_filter" => "main_pa.language_id = 1",
		"product_filter"  => array(
			'select' => array('DISTINCT sub_ptc.product_id'),
			'from' => 'product_to_category AS sub_ptc',
			'join' => array(),
			'where' => array()
		)
	);

	public $join = array(
	);

	public $group_by = array(
		"attr_text"
	);

	public $order_by = array(
		"attr_id ASC"
	);


	public function setOptions($attributes = array())
	{
		$this->join['options_1'] = " LEFT JOIN product_option AS main_po ON (main_po.product_id = main_pa.product_id) ";
		
		parent::setOptions($attributes);
	}


}