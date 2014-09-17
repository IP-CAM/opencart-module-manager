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
		return $items->rows;
	}


}