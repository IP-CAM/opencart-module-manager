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
	 * @return void
	 */
	public function make($data)
	{
		$this->filter->buildCategory($data['category_id']);
		$this->filter->buildAttributes($data['attributes']);
		$this->filter->buildOptions($data['options']);

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