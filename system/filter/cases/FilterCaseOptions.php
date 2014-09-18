<?php 


class FilterCaseOptions extends FilterCase implements FilterCaseInterface 
{
	
	// SQL data
	public $select = array(
		"main_po.product_option_id",
		"main_po.product_id",
		"main_po.option_id",
		"main_po.option_value",
		"o.type AS option_type",
		"o.sort_order as option_sort_order",
		"od.name as option_name",
		"ov.option_value_id",
		"ov.image as option_value_image",
		"ov.sort_order as option_value_sort_order",
		"ovd.name" 
	);

	public $from = "product_option as main";

	public $join = array();

	public $where = array(
		"language_filter" => "ovd.language_id = 1",
		"product_filter"  => array(
			'select' => array('DISTINCT sub_ptc.product_id'),
			'from' => 'product_to_category AS sub_ptc',
			'join' => array(),
			'where' => array()
		)
	);

	public $group_by = array(
		"ovd.option_value_id"
	);

	public $order_by = array(
		"main_po.option_id ASC"
	);

}