<?php 


/**
 * Create attribute groups from simple attribute list
 *
 */
class FilterFormatterAllAttributes implements FilterFormatterInterface 
{
	

	public function make($attributes)
	{
		$result = array();

		foreach ($attributes as $attribute)
		{
			$result[$attribute['attribute_id']]['name'] = $attribute['name'];
			$result[$attribute['attribute_id']]['name_md5'] = md5($attribute['name']);
			$result[$attribute['attribute_id']]['id'] = $attribute['attribute_id'];
			
			$result[$attribute['attribute_id']]['items'][md5($attribute['text'])] = array(
				'total' => $attribute['total'],
				'text' => $attribute['text'],
				'code' => md5($attribute['text'])
			);
		}

		return $result;
	}


}