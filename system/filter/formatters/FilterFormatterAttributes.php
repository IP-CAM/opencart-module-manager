<?php 


/**
 * Convert query result to readable list
 *
 * Example:
 *
 * Array
 * (
 *     [attribute_id_6] => Array
 *         (
 *             [id] => 6
 *             [name] => Processor
 *             [items] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [product_id] => 43
 *                             [attr_id] => 2
 *                             [attr_text] => 1
 *                             [attr_id] => 6
 *                             [attr_group_order] => 5
 *                             [attr_group_name] => Processor
 *                         )
 *                   )
 *          )
 *     [attribute_id_3] => Array
 *         (
 *             [id] => 3
 *             [name] => Memory
 *             [items] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [product_id] => 43
 *                             [attr_id] => 4
 *                             [attr_text] => 8gb
 *                             [attr_id] => 3
 *                             [attr_group_order] => 1
 *                             [attr_group_name] => Memory
 *                         )
 *                  )
 *         )
 *  )
 *
 */
class FilterFormatterAttributes implements FilterFormatterInterface 
{
	

	public function make($items)
	{
		$result = array();

		foreach ($items as $attribute)
		{
			$key = 'attribute_id_' . $attribute['attr_id'];
			$disabled = empty($attribute['disabled']) ? false : true;
			$selected = empty($attribute['selected']) ? false : true;

			$result[$key]['id'] = $attribute['attr_id'];
			$result[$key]['name'] = $attribute['attr_name'];
			$result[$key]['items'][] = array(
				'product_id' => $attribute['product_id'],
				'attr_id' => $attribute['attr_id'],
				'attr_text' => $attribute['attr_text'],
				'attr_group_id' => $attribute['attr_group_id'],
				'attr_group_order' => $attribute['attr_group_order'],
				'attr_group_name' => $attribute['attr_group_name'],
				'selected' => $selected,
				'disabled' => $disabled
			);
		}

		return $result;
	}


}