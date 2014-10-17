<?php 



class FilterFactory 
{

	
	protected $filter;
	

	function __construct(FilterBuilderInterface $filter)
	{
		$this->filter = $filter;
	}


	/**
	 * Set all needle data for filtering
	 *
	 * @return mixed
	 */
	public function make($settings)
	{
		// $this->filter->buildCategory($settings['category_id']);
		$this->filter->buildAttributes($settings);
		// $this->filter->buildOptions($settings['options']);

		return $this;
	}


	/**
	 * Get all the products, attributes, manufacturers.. Resolves catalog data
	 *
	 * @return mixed
	 */
	public function resolve($db)
	{
		return $this->filter->fetch($db);
	}


}