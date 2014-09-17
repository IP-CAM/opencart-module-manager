<?php 



class SameFilterOptions 
{
	

	/**
	 * Leaves only same options
	 *
	 * @return array
	 */
	public function filter($options)
	{
		$same_options = array();

		// Here we convert option array to option ID's array
		foreach ($options as $key => $option_result)
		{
			$same_options[$key] = array();

			foreach ($option_result as $option_key => $option)
			{
				$same_options[$key][$option_key] = $option['option_value_id'];
			}
		}

		// Leave only same ID's
		$result = $same_options[0];
		foreach ($same_options as $same_options_array)
		{
			$result = array_intersect($result, $same_options_array);
		}

		// Compose options array back from result same ID's
		return $this->composeSame($options, $result);
	}


	/**
	 * Compose options array back from result same ID's
	 *
	 * @return mixed
	 */
	private function composeSame($original, $same_options)
	{
		$result = array();

		foreach ($original as $option_result)
		{
			foreach ($option_result as $option)
			{
				if (in_array($option['option_value_id'], $same_options))
				{
					$key = 'option_value_id_' . $option['option_value_id'];
					$result[$key] = $option;
				}
			}
		}

		return $result;
	}


}