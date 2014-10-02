<?php 


/**
 * FilterManagerTest
 *
 * @group group
 */
class FilterManagerTest extends \PHPUnit_Framework_TestCase
{
    

	/**
	 * @covers FilterManager::demo()
	 */
	public function testItWorks()
	{
		$this->assertTrue(true);
	}


	/**
	 * @covers FilterManager::getCategoryAttributes()
	 */
	public function testGetCategoryAttributes()
	{
		print_r($GLOBALS['registry']);
		// $settings = array(
		// 	'category_id' => 24,
		// 	'attributes' => array(),
		// 	'options' => array()
		// );

		// $filter_manager = new FilterManager;
		// $result = $filter_manager->filter($this->db, $settings);


	}


}
