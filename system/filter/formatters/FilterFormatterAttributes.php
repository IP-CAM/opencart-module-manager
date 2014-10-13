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

		foreach($attributes as $row)
		{
			if( ! empty($this->_settings['attribute_separator']))
			{
				$texts = explode($this->_settings['attribute_separator'], $row['text']);
				
				foreach($texts as $txt)
				{
					if( ! isset($result[$row['attribute_id']][$txt]))
					{
						$result[$row['attribute_id']][$txt] = 0;
					}
					
					$result[$row['attribute_id']][$txt] += $row['total'];
				}
			}
			else
			{
				$result[$row['attribute_id']][$row['text']] = $row['total'];
			}
		}

		return $result;
	}


}