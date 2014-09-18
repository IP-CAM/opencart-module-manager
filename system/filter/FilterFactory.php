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
	public function resolve($db, $settings, $key)
	{
		if (is_array($settings[$key]) AND count($settings[$key]) > 1)
		{
			return $this->resolveArray($db, $settings, $key);
		}
		else
		{
			return $this->filter->fetch($db);
		}
	}


	/**
	 * Get all the products, attributes, manufacturers.. 
	 * Resolves catalog data in array for every single option, attribute ect.
	 *
	 * @return mixed
	 */
	protected function resolveArray($db, $settings, $key)
	{
		$same_options = array();

		foreach ($settings[$key] as $id)
		{
			$settings = array(
				'category_id' => $settings['category_id'],
				'attributes' => $settings['attributes'],
				'options' => array($id)
			);

			$same_options[] = $this->make($settings)->resolve($db, $settings, $key);
		}

		// Leave items only with same ID
		$same = new SameFilterOptions;

		return $same->filter($same_options);
	}


}