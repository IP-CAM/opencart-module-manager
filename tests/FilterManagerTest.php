<?php 


/**
 * FilterManagerTest
 *
 * @group group
 */
class FilterManagerTest extends \PHPUnit_Framework_TestCase
{

	protected $db;


	public function setUp()
	{
		$this->db = $GLOBALS['registry']->get('db');
	}
    

	/**
	 * @covers FilterManager::demo()
	 */
	public function testItWorks()
	{
		$this->assertTrue(true);
	}


	/**
	 * @covers FilterManager::getRealAttributes()
	 */
	public function testGetRealAttributes()
	{
		// Expected values
		$expected = array(
			array(
				'attr_id' => '12',
				'attr_name' => 'Тип устройства',
				'items' => array(
					array('text' => 'Телефон')
				)
			),
			array(
				'attr_id' => '13',
				'attr_name' => 'Материал',
				'items' => array(
					array('text' => 'Метал'),
					array('text' => 'Пластик')
				)
			),
			array(
				'attr_id' => '14',
				'attr_name' => 'Диагональ экрана',
				'items' => array(
					array('text' => '5.5'),
					array('text' => '4')
				)
			),
			array(
				'attr_id' => '15',
				'attr_name' => 'Процесор',
				'items' => array(
					array('text' => '2300 hz'),
					array('text' => '4000 hz')
				)
			),
			array(
				'attr_id' => '16',
				'attr_name' => 'Операционная система',
				'items' => array(
					array('text' => 'IOS'),
					array('text' => 'Android')
				)
			)
		);

		// Arrange
		$settings = array(
			'category_id' => 24,
			'attributes' => array(),
			'options' => array()
		);

		$filter_manager = new FilterManager;
		$result = $filter_manager->getRealAttributes($this->db, $settings);

		$this->assertEquals($expected, $result, 'Avalible attributes fatched badly');
	}


	/**
	 * @covers FilterManager::getFilteredAttributes()
	 */
	public function testGetFilteredAttributes()
	{
		// Expected values
		$expected = array(
			array(
				'attr_id' => '12',
				'attr_name' => 'Тип устройства',
				'items' => array(
					array(
						'text' => 'Телефон',
						'disabled' => false
					)
				),
				'filters' => array('Метал'),
				'attributes' => array(
					array('text' => 'Телефон')
				)
			),
			array(
				'attr_id' => '13',
				'attr_name' => 'Материал',
				'items' => array(
					array(
						'text' => 'Метал',
						'selected' => true,
						'disabled' => false
					),
					array(
						'text' => 'Пластик',
						'disabled' => false
					),
				),
				'filters' => array(),
				'attributes' => array(
					array('text' => 'Пластик'),
					array('text' => 'Метал'),
				)
			),
			array(
				'attr_id' => '14',
				'attr_name' => 'Диагональ экрана',
				'items' => array(
					array(
						'text' => '5.5',
						'disabled' => true
					),
					array(
						'text' => '4',
						'disabled' => false
					)
				),
				'filters' => array('Метал'),
				'attributes' => array(
					array('text' => '4')
				)
			),
			array(
				'attr_id' => '15',
				'attr_name' => 'Процесор',
				'items' => array(
					array(
						'text' => '2300 hz',
						'disabled' => true
					),
					array(
						'text' => '4000 hz',
						'disabled' => false
					)
				),
				'filters' => array('Метал'),
				'attributes' => array(
					array('text' => '4000 hz')
				)
			),
			array(
				'attr_id' => '16',
				'attr_name' => 'Операционная система',
				'items' => array(
					array(
						'text' => 'IOS',
						'disabled' => false
					),
					array(
						'text' => 'Android',
						'disabled' => true
					)
				),
				'filters' => array('Метал'),
				'attributes' => array(
					array('text' => 'IOS')
				)
			)
		);

		// Arrange
		$settings = array(
			'category_id' => 24,
			'attributes' => array(
				array(
					'attr_group_id' => 13,
					'items' => array('Метал')
				)
			),
			'options' => array()
		);

		$filter_manager = new FilterManager;
		$result = $filter_manager->getFilteredAttributes($this->db, $settings);

		// print_r($result); die();

		$this->assertEquals($expected, $result);
	}


}
