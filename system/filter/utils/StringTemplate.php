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
		// $template = get_class($this) . '::' . $template_key;

		// if (defined($template))
		// {
		// 	return $this->replace($$template, $params);
		// }

		foreach ($params as $key => $value)
		{
			$template = str_replace('{{' . $key . '}}', self::getValue($value), $template);
		}

		return $template;
	}


	/**
	 * Get value
	 *
	 * @return string
	 */
	static private function getValue($value)
	{
		$prefix = empty($value['PREFIX']) ? '' : $value['PREFIX'];
		$postfix = empty($value['POSTFIX']) ? '' : $value['POSTFIX'];
		$value = isset($value['VALUE']) ? $value['VALUE'] : $value;

		// If we doesn't have any value -> ignore it
		if (empty($value)) return '';

		return $prefix . $value . $postfix;
	}


}