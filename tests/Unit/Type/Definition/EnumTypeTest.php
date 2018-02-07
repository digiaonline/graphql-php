<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;

/**
 * Class EnumTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property EnumType $type
 */
class EnumTypeTest extends TypeTestCase
{

    protected function setUp()
    {
        $this->config = [
            'name'        => 'RGB',
            'description' => 'Enumerable for the RGB-colors.',
            'values'      => [
                [
                    'name'  => 'RED',
                    'value' => 0,
                ],
                [
                    'name'  => 'GREEN',
                    'value' => 1,
                ],
                [
                    'name'  => 'BLUE',
                    'value' => 2,
                ],
            ],
            'astNode'     => null,
        ];

        $this->type = new EnumType($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConstructor()
    {
        $this->assertEquals($this->config['name'], $this->type->getName());
        $this->assertEquals($this->config['description'], $this->type->getDescription());
        $this->assertEquals($this->config['astNode'], $this->type->getAstNode());
        $this->assertEquals($this->config, $this->type->getConfig());
    }

    /**
     * @throws \Exception
     */
    public function testGetValue()
    {
        $value = $this->type->getValue('RED');

        $this->assertInstanceOf(EnumValue::class, $value);
        $this->assertEquals('RED', $value->getName());
    }

    /**
     * @throws \Exception
     */
    public function testGetValues()
    {
        $values = $this->type->getValues();

        $this->assertTrue(is_array($values));
        $this->assertEquals(3, count($values));
    }

    /**
     * @throws \Exception
     */
    public function testSerialize()
    {
        $this->assertEquals('GREEN', $this->type->serialize(1));
    }

    /**
     * @throws \Exception
     */
    public function testParseValue()
    {
        $this->assertEquals(1, $this->type->parseValue('GREEN'));
    }

    // TODO: Test parseLiteral when it's implemented
}
