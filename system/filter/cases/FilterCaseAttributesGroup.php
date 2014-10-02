<?php 


class FilterCaseAttributesGroup extends FilterCase implements FilterCaseInterface 
{
	
	// SQL data
	public $select = array(
		"main_pa.attribute_id as attr_id",
		"main_pa.text as attr_text",
		"ad.name as attr_name"
	);

	public $from = "product_attribute AS main_pa";

	public $where = array(
		"language_filter" => "main_pa.language_id = 1",
		"product_filter"  => array(
			'select' => array('DISTINCT sub_ptc.product_id'),
			'from' => 'product_to_category AS sub_ptc',
			'join' => array(
				" LEFT JOIN product_attribute AS sub_pa ON (sub_pa.product_id = sub_ptc.product_id) ",
				" LEFT JOIN product_option_value AS sub_pov ON (sub_ptc.product_id = sub_pov.product_id) "
			),
			'where' => array()
		)
	);

	public $join = array(
		" JOIN attribute_description AS ad ON ad.attribute_id = main_pa.attribute_id "
	);

	public $group_by = array(
		"main_pa.text"
	);

	public $order_by = array(
		"main_pa.attribute_id ASC"
	);


	public function setOptions($options = array()) {}
	public function setAttributes($attributes = array()) {}


}