<?php 



class FilterSettingResolver
{
	

	public function resolve($settings, $key, $filter_item_id)
	{
		if ($key == 'attributes')
			$settings['attributes'] = array($filter_item_id);

		elseif ($key == 'options')
			$settings['options'] = array($filter_item_id);

		else throw new Exception("Can not resolve settings with key - " . $key);

		return $settings;
	}


}