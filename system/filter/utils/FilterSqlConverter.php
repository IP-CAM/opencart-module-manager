<?php 


class FilterSqlConverter
{


	/**
	 * Generate complite sql query for filtering
	 *
	 * @return string
	 */
	public function make($select, $from, $join = NULL, $where = NULL, $group_by = NULL, $order_by = NULL)
	{
		$result = $this->select($select);
		$result .= $this->getFrom($from);
		$result .= $this->getJoin($join);
		$result .= $this->getWhere($where);
		$result .= $this->getGroupBy($group_by);
		$result .= $this->getOrderBy($order_by);

		return $result;
	}


	/**
	 * Generate `select` sql statement
	 *
	 * @return string
	 */
	public function select($select)
	{
		$result = "SELECT ";
		$result .= implode(", ", $select);

		return $result;
	}


	/**
	 * Generate `from` sql statement
	 *
	 * @return string
	 */
	private function getFrom($from)
	{
		$result = " FROM ";
		$result .= " " . $from . " ";

		return $result;
	}


	private function getJoin($join)
	{
		if (empty($join)) return;

		return implode(" ", $join);
	}


	/**
	 * Generate `where` sql statement
	 *
	 * @return string
	 */
	private function getWhere($where)
	{
		if (empty($where)) return;

		$result = "";

		// Resolve sub where
		if (isset($where['product_filter']) AND is_array($where['product_filter']))
		{
			$product_filter = " product_id IN ( ";
			$product_filter .= $this->make(
				$where['product_filter']['select'],
				$where['product_filter']['from'],
				$where['product_filter']['join'],
				$where['product_filter']['where']
			);
			$product_filter .= " ) ";

			$where['product_filter'] = $product_filter;
		}
		
		$result .= " WHERE ";
		$result .= implode(" AND ", $where);

		return $result;
	}


	/**
	 * Generate `group by` sql statement
	 *
	 * @return string
	 */
	public function getGroupBy($group_by)
	{
		if (empty($group_by)) return;

		$result = " GROUP BY ";
		$result .= implode(", ", $group_by);

		return $result;
	}


	/**
	 * Generate `order by` sql statement
	 *
	 * @return string
	 */
	public function getOrderBy($order_by)
	{
		if (empty($order_by)) return;

		$result = " ORDER BY ";
		$result .= implode(", ", $order_by);

		return $result;
	}


}