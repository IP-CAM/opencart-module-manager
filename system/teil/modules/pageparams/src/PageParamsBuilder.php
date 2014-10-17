<?php 


class PageParamsBuilder 
{
	private $app;
	private $key_info;


	public function __construct(Teil\Core\App $app, $key_info)
	{
		$this->app = $app;
		$this->key_info = $key_info;
	}


	/**
	 * Parse page params
	 *
	 * @return array
	 */
	public function parseURL()
	{
		$result = array();
		$request = $this->app->make('registry')->get('request')->get;

		// Categories
		if ( ! empty($request['category_id']))
		{
			$result['filter_category_id'] = (int) $request['category_id'];
		}
		else if ( ! empty($request['path']))
		{
			$parts = explode('_', (string) $request['path']);
			$result['filter_category_id'] = (int) array_pop($parts);
		}
		
		// Sub categories
		if ( ! empty($request['sub_category']))
		{
			$result['filter_sub_category'] = $request['sub_category'];
		}

		// Filters
		if ( ! empty($request['filter']))
		{
			$result['filter_filter'] = $request['filter'];
		}
		
		// Tags
		if ( ! empty($request['filter_tag']))
		{
			$result['filter_tag'] = $request['filter_tag'];
		}
		elseif ( ! empty($request['tag']))
		{
			$result['filter_tag'] = $request['tag'];
		}
		elseif ( ! empty($request['search']))
		{
			$result['filter_tag'] = $request['search'];
		}
		
		// Manufacturer
		if ( ! empty($request['manufacturer_id']))
		{
			$result['filter_manufacturer_id'] = (int) $request['manufacturer_id'];
		}
		
		// Search
		if ( ! empty($request['search']))
		{
			$result['filter_name'] = (string) $request['search'];
		}
		
		return $result;
	}


}