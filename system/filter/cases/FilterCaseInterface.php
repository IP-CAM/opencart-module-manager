<?php 


/**
 * This is kind of filter plan
 *
 */
interface FilterCaseInterface
{
	
	public function setCategory($category_id);
	public function setAttributes($attributes);
	public function setOptions($options);

}