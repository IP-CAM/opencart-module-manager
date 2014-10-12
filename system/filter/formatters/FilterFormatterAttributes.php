<?php 


/**
 * Create attribute groups from simple attribute list
 *
 */
class FilterFormatterAttributes implements FilterFormatterInterface 
{
	

	public function make($attributes)
	{
		$result = array();

		// Create readable attribute groups
		foreach($attributes as $attribute)
		{
			if( ! isset($result[$attribute['attribute_group_id']]))
			{
				$result[$attribute['attribute_group_id']] = array(
					'name' => $attribute['attribute_group_name'],
					'attribute_values' => array()
				);
			}

			if( ! isset($result[$attribute['attribute_group_id']]['attribute_values'][$attribute['attribute_id']]))
			{
				$result[$attribute['attribute_group_id']]['attribute_values'][$attribute['attribute_id']] = array(
					'id' => $attribute['attribute_id'], 
					'name' => $attribute['name'], 
					'values' => array()
				);
			}

			// | - is attribute delimeter
			foreach(explode("|", $attribute['text']) as $text)
			{
				if( ! $this->valueExists($text, $result[$attribute['attribute_group_id']]['attribute_values'][$attribute['attribute_id']]))
				{
					$result[$attribute['attribute_group_id']]['attribute_values'][$attribute['attribute_id']]['values'][] = array('text' => $text);
				}
			}
		}

		// Sort result
		foreach($result as $attribute_group_id => $attribute_group)
		{
			foreach($attribute_group['attribute_values'] as $attribute_id => $attribute)
			{
				sort($result[$attribute_group_id]['attribute_values'][$attribute_id]['values']);
			}
		}

		return $result;
	}


	/**
	 * Check if attribute value already exists in array
	 *
	 * @return bool
	 */
	protected function valueExists($text, $attributes)
	{
		if (in_array(array('text' => $text), $attributes['values']))
		{
			return true;
		}

		return false;
	}


}