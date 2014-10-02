<?php 


/**
 * AttributeGroupConverterTest
 *
 * @group group
 */
class AttributeGroupConverterTest extends \PHPUnit_Framework_TestCase
{


	protected $db;
	protected $attributes;


	public function setUp()
	{
		$this->db = $GLOBALS['registry']->get('db');

		$builder = new FilterBuilder(
			new FilterCaseAttributesGroup,
			new FilterFormatterAttributes
		);
		$factory = new FilterFactory($builder);
		
		$this->attributes = $factory->make(array(
				'category_id' => 24,
				'attributes' => array(),
				'options' => array()
			))
			->resolve($this->db);
	}
    

	/**
	 * @covers AttributeGroupConverter::toGroup()
	 */
	public function testToGroup()
	{
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

		$converter = new AttributeGroupConverter;
		$result = $converter->toGroup($this->attributes);

		$this->assertEquals($expected, $result);
	}


	/**
	 * @covers AttributeGroupConverter::pluckAttributeTextArray()
	 */
	public function testPluckAttributeTextArray()
	{
		$input = array(
			array(
				'id' => '1',
				'attr_text' => 'test1'
			),
			array(
				'id' => '2',
				'attr_text' => 'test2'
			),
			array(
				'id' => '3',
				'attr_text' => 'test3'
			)
		);

		$converter = new AttributeGroupConverter;
		$result = $converter->pluckAttributeTextArray($input);

		$this->assertEquals(array(
			array('text' => 'test1'), 
			array('text' => 'test2'), 
			array('text' => 'test3')
		), $result);
	}


}
