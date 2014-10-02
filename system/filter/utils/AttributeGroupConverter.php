<?php 


/**
*  Convert db result attribute list to attribute group
*/
class AttributeGroupConverter 
{
	
	

	/**
	 * Here we will convert input db result attribute list to the attr groups
	 * with items = real product attributes inside
	 *
	 *
	 * @return array
	 */
	public function toGroup($attribute_list)
	{
		$result = array();

		foreach ($attribute_list as $attribute)
		{
			$result[$attribute['attr_id']]['attr_id'] = $attribute['attr_id'];
			$result[$attribute['attr_id']]['attr_name'] = $attribute['attr_name'];

			$result[$attribute['attr_id']]['items'][] = array(
				'text' => $attribute['attr_text']
			);
		}

		return array_values($result);
	}


	/**
	 * Get plucked array items
	 *
	 * @return array
	 */
	public function pluckAttributeTextArray($attributes)
	{
		$result = array();

		foreach ($attributes as $attribute)
		{
			$result[] = array(
				'text' => $attribute['attr_text']
			);
		}
	
		return $result;
	}


	/**
	 * Set `selected` and `disabled` statuses
	 *
	 * @return array
	 */
	public function compose($attributes)
	{
		foreach ($attributes as & $attribute)
		{
			foreach ($attribute['items'] as & $original_attr)
			{
				foreach ($attribute['attributes'] as $filtered_attr)
				{
					if ($original_attr['text'] != $filtered_attr['text'])
					{
						$original_attr['disabled'] = true;
						continue;
					}

					$original_attr['disabled'] = false;
					break;
				}
			}
		}

		return $attributes;
	}



}