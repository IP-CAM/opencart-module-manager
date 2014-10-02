<?php 


/**
* Add `disabled` statuses and `selected/active` classes
*/
class FilterComposer 
{
	
	
	protected $filter_key;

	
	function __construct($key)
	{
		$this->filter_key = $key;
	}


	/**
	 * Add `disabled/selected`
	 *
	 * $original - array with all the attributes of selected category/section
	 * $filtered - same as $original but filtered throught all avalible filters
	 * $settings - list of selected attributes, options etc.
	 *
	 * @return array
	 */
	public function compose($original, $filtered, $setting)
	{
		// Loop through original array and check if original item has same
		// value as filtered item -> then remove `disabled` status and
		// add `active` status to it
		foreach ($original as & $original_item)
		{
			$original_item['disabled'] = true;

			// Here we are looping through filtered array
			foreach ($filtered as $filtered_item)
			{
				if ($this->selected($original_item, $setting))
				{
					$original_item['selected'] = true;
				}

				if ( ! $this->disabled($original_item, $filtered_item))
				{
					$original_item['disabled'] = false;

					// continue;
				}
			}
		}

		return $original;
	}


	/**
	 * Check if filter item is disabled
	 *
	 * @return bool
	 */
	private function disabled($original, $filtered)
	{
		if ($original[$this->filter_key] == $filtered[$this->filter_key])
		{
			return false;
		}

		return true;
	}


	/**
	 * Check if filter item is selected
	 *
	 * @return bool
	 */
	private function selected($original, $setting)
	{
		if (in_array($original[$this->filter_key], $setting))
		{
			return true;
		}

		return false;
	}


}