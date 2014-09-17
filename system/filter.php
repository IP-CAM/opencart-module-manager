<?php 



class Filter 
{
	
	protected $registry;
	protected $attribute_filter;
	protected $category_filter;


	public function __construct($registry) {
		$this->registry = $registry;
	}


	public function __get($key) {
		return $this->registry->get($key);
	}


	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}


	public function make($data)
	{
		// $result = $this->db->query("SELECT * FROM product LIMIT 10")->rows;
		// $result = $this->db->query("SELECT * FROM product LIMIT 10")->rows;
		// $result = $this->db->query("SELECT * FROM attribute_group")->rows;

		$this->load->model('catalog/product');
		$result = $this->filterAttributes($data);


		return $result;
		// print_r($result);
		// echo 'demo';
	}



	/**
	 * Get attribute list that depends on products id's from category
	 *
	 * @return mixed
	 */
	private function filterAttributes($data)
	{
		$query = "
			SELECT 
				pa.product_id AS product_id, 
				pa.attribute_id AS attr_id,
				pa.text AS attr_text,
				a.attribute_group_id AS attr_group_id,
				a.sort_order AS attr_group_order,
				agd.name AS attr_group_name 
			FROM product_attribute AS pa
				
			JOIN attribute AS a ON (a.attribute_id = pa.attribute_id) 
			JOIN attribute_group_description AS agd ON (a.attribute_group_id = agd.attribute_group_id)
		";

		$query .= $this->getAttributeProductsSearchQuery($data);

		$query .= "
			GROUP BY attr_id 

			ORDER BY 
				attr_group_order ASC, 
				attr_group_id ASC,
				attr_id ASC
		";

		$result = $this->db->query($query);


		// Create group array
		$attrs = array();
		foreach ($result->rows as $attribute)
		{
			$attr_key = 'attribute_group_id_' . $attribute['attr_group_id'];

			$attrs[$attr_key]['name'] = $attribute['attr_group_name'];
			$attrs[$attr_key]['items'][] = array(
				'product_id' => $attribute['product_id'],
				'attr_id' => $attribute['attr_id'],
				'attr_text' => $attribute['attr_text'],
				'attr_group_id' => $attribute['attr_group_id'],
				'attr_group_order' => $attribute['attr_group_order'],
				'attr_group_name' => $attribute['attr_group_name']
			);
		}

		return $attrs;
	}


	/**
	 * Get SQL query in order to filter attributes with result product id's
	 *
	 * @return string
	 */
	private function getAttributeProductsSearchQuery($data)
	{
		$query_has_where = false;
		$result = "";

		// Open SQL where
		$result .= "WHERE pa.product_id IN ( ";
		$result .= "SELECT sub_pa.product_id ";
		$result .= "FROM product_to_category AS sub_ptc ";
		$result .= "JOIN product_attribute AS sub_pa ON (sub_pa.product_id = sub_ptc.product_id) ";
		$result .= "WHERE ";

		// Filter by category id
		if ($data['category_id'])
		{
			$query_has_where = true;

			$result .= "sub_ptc.category_id IN (" . (int) $data['category_id'] . ") ";
		}

		// Filter by attributes
		if ($data['attributes'])
		{
			if ($query_has_where) $result .= "AND ";

			$result .= "sub_pa.attribute_id IN (" . implode(",", $data['attributes']) . ") ";
		}

		// Filter by language
		if ($query_has_where) $result .= "AND ";
		$result .= "pa.language_id = " . $this->config->get('config_language_id') . " ";
		
		
		// Close SQL where
		$result .= ")";

		return $result;
	}


}