<?php 



class FilterBuilder implements FilterBuilderInterface 
{


	protected $filter_case;
	protected $formatter;

	protected $sql =  '';

	
	function __construct(FilterCaseInterface $filter_case, FilterFormatterInterface $formatter)
	{
		$this->filter_case = $filter_case;
		$this->formatter = $formatter;
	}


	/**
	 * Get all the output filters, convert them to sql and fetch results
	 *
	 * @return mixed
	 */
	public function fetch($db)
	{
		// Get result item list
		return $this->formatter->make(
			$db->query($this->sql)->rows
		);
	}


	/**
	 * Set filter category
	 *
	 * @return void
	 */
	public function buildCategory($category_id)
	{
		// $this->filter_case->setCategory($category_id);
	}


	/**
	 * Set filter attributes
	 *
	 * @return void
	 */
	public function buildAttributes($attributes)
	{
		$this->sql = $this->filter_case->setAttributes($attributes);
	}


	/**
	 * Set filter options
	 *
	 * @return void
	 */
	public function buildOptions($options)
	{
		// $this->filter_case->setOptions($options);
	}


}