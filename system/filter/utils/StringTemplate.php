<?php 


class StringTemplate 
{
	
	
	/**
	 * Replate variables into given values
	 *
	 * @return string
	 */
	static public function make($template, $params)
	{
		foreach ($params as $key => $value)
		{
			$template = str_replace('{{' . $key . '}}', $value, $template);
		}

		return $template;
	}


}