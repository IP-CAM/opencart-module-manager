<?php 


class FilterCaseAttributes extends FilterCase implements FilterCaseInterface 
{
	
	// SQL data
	public $select = array(
		"pa.product_id AS product_id",
		"pa.attribute_id AS attr_id",
		"pa.text AS attr_text",
		"a.attribute_group_id AS attr_group_id",
		"a.sort_order AS attr_group_order",
		"agd.name AS attr_group_name" 
	);

	public $from = "product_attribute AS pa";

	public $where = array(
		"language_filter" => "pa.language_id = 1",
		"product_filter"  => array(
			'select' => array('sub_ptc.product_id'),
			'from' => 'product_to_category AS sub_ptc',
			'join' => array(),
			'where' => array()
		)
	);

	public $join = array(
		" JOIN attribute AS a ON (a.attribute_id = pa.attribute_id) ",
		" JOIN attribute_group_description AS agd ON (a.attribute_group_id = agd.attribute_group_id) "
	);

	public $group_by = array(
		"attr_id"
	);

	public $order_by = array(
		"attr_id ASC"
	);


}