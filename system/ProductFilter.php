<?php 



class FilterBuilder implements FilterBuilderInterface 
{


	protected $filter_case;
	protected $formatter;

	
	function __construct(FilterCaseInterface $filter_case, FilterFormatter $formatter)
	{
		$this->filter_case = $filter_case;
		$this->formatter = $formatter;
	}


	/**
	 * Get all the output filters, convert them to sql and fetch results
	 *
	 * @return mixed
	 */
	public function fetch($db)
	{
		$sql_factory = new FilterSqlConverter();

		$sql = $sql_factory->make(
			$this->filter_case->select,
			$this->filter_case->from,
			$this->filter_case->join,
			$this->filter_case->where,
			$this->filter_case->group_by,
			$this->filter_case->order_by
		);

		$items = $db->query($sql);
		
		return $this->formatter->make($items);
	}


	/**
	 * Format db result list to readable list
	 *
	 * @return mixed
	 */
	protected function format($items)
	{
		$attrs = array();

		foreach ($items->rows as $attribute)
		{
			$attr_key = 'attribute_group_id_' . $attribute['attr_group_id'];

			$attrs[$attr_key]['id'] = $attribute['attr_group_id'];
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
	 * Set filter category
	 *
	 * @return void
	 */
	public function buildCategory($category_id)
	{
		$this->filter_case->setCategory($category_id);
	}


	/**
	 * Set filter attributes
	 *
	 * @return void
	 */
	public function buildAttributes($attributes)
	{
		$this->filter_case->setAttributes($attributes);
	}


}