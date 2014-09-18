<?php 


/**
 * Convert query result to readable list
 *
 * Example:
 *
 * Array
 * (
 * )
 *
 */
class FilterFormatterOptions implements FilterFormatterInterface 
{
	

	public function make($items)
	{
		$result = array();

		foreach ($items as $option)
		{
			$key = 'option_id_' . $option['option_id'];

			$result[$key]['id'] = $option['option_id'];
			$result[$key]['name'] = $option['option_name'];
			$result[$key]['type'] = $option['option_type'];

			$result[$key]['items'][] = array(
				'product_id' => $option['product_id'],
				'option_value_id' => $option['option_value_id'],
				'name' => $option['name']
			);
		}

		return $result;
	}


}