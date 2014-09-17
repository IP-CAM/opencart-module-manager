<?php 


class FilterCaseOptions extends FilterCase implements FilterCaseInterface 
{
	
	// SQL data
	public $select = array(
		"po.product_option_id",
		"po.product_id",
		"po.option_id",
		"po.option_value",
		"o.type AS option_type",
		"o.sort_order as option_sort_order",
		"od.name as option_name",
		"ov.option_value_id",
		"ov.image as option_value_image",
		"ov.sort_order as option_value_sort_order",
		"ovd.name" 
	);

	public $from = "product_option as po";

	public $join = array(
		" JOIN `option` AS o ON (o.option_id = po.option_id) ",
		" JOIN option_description AS od ON (od.option_id = po.option_id) ",
		" JOIN option_value AS ov ON (ov.option_id = po.option_id) ",
		" JOIN option_value_description AS ovd ON (ovd.option_value_id = ov.option_value_id) "
	);

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
		"po.option_id ASC"
	);

}