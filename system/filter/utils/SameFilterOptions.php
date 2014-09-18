<?php 



class SameFilterOptions 
{
	

	/**
	 * Leaves only same options
	 *
	 * @return array
	 */
	public function filter($options, $filter_key)
	{
		$same_options = array();

		// Here we convert option array to option ID's array
		foreach ($options as $key => $option_result)
		{
			$same_options[$key] = array();

			foreach ($option_result as $option_key => $option)
			{
				$same_options[$key][$option_key] = $option[$filter_key];
			}
		}

		if ( ! count($same_options)) return array();
		
		// Leave only same ID's
		$result = $same_options[0];
		foreach ($same_options as $same_options_array)
		{
			$result = array_intersect($result, $same_options_array);
		}

		// Compose options array back from result same ID's
		return $this->composeSame($options, $result, $filter_key);
	}


	/**
	 * Compose options array back from result same ID's
	 *
	 * @return mixed
	 */
	private function composeSame($original, $same_options, $filter_key)
	{
		$result = array();

		foreach ($original as $option_result)
		{
			foreach ($option_result as $option)
			{
				if (in_array($option[$filter_key], $same_options))
				{
					$key = $filter_key . '_' . $option[$filter_key];
					$result[$key] = $option;
				}
			}
		}

		return $result;
	}


}