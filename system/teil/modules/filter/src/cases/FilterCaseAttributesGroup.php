<?php 


class FilterCaseAttributesGroup implements FilterCaseInterface 
{
	
	// SQL data
	public $select = array(
		"pa.text",
		"a.attribute_id",
		"ad.name",
		"ag.attribute_group_id",
		"agd.name AS attribute_group_name",
	);

	public $from = "product_attribute AS pa";

	public $where = array(
		"filter_product_status" => "p.status = 1",
		"filter_product_date_avaliable" => "p.date_available <= NOW()",
		"filter_store_id" => "p2s.store_id =0",
		"filter_product_attribute_language_id" => "pa.language_id = 1",
		"filter_attribute_text_language_id" => "ad.language_id = 1",
		"filter_attribute_group_language_id" => "agd.language_id = 1"
	);

	public $join = array(
		" LEFT JOIN attribute a ON(pa.attribute_id=a.attribute_id) ",
		" LEFT JOIN attribute_description ad ON(a.attribute_id=ad.attribute_id) ",
		" LEFT JOIN attribute_group ag ON(ag.attribute_group_id=a.attribute_group_id) ",
		" LEFT JOIN attribute_group_description agd ON(agd.attribute_group_id=ag.attribute_group_id) ",
		" LEFT JOIN product p ON(p.product_id=pa.product_id) ",
		" LEFT JOIN product_to_category p2c ON(p.product_id=p2c.product_id) ",
		" LEFT JOIN product_to_store p2s ON(p.product_id=p2s.product_id) "
	);

	public $group_by = array();

	public $order_by = array(
		"ag.sort_order",
		"agd.name",
		"a.sort_order",
		"ad.name",
		"pa.text"
	);


	/**
	 * Filter by category id
	 *
	 * @return void
	 */
	public function setCategory($category_id)
	{
		if ( ! empty($category_id))
		{
			$this->where['filter_category_id'] = ' p2c.category_id = ' . $category_id . ' ';
		}
	}

	public function setAttributes($attributes) {}


}