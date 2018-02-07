<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Language\AST\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;

/**
 * Class EnumTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property EnumType $type
 */
class EnumTypeTest extends AbstractTypeTestCase
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
            'astNode'     => new EnumTypeDefinitionNode(),
        ];

        $this->type = new EnumType($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConfig()
    {
        $this->assertEquals($this->config['name'], $this->type->getName());
        $this->assertEquals($this->config['description'], $this->type->getDescription());
        $this->assertEquals($this->config, $this->type->getConfig());
        $this->assertInstanceOf(EnumTypeDefinitionNode::class, $this->type->getAstNode());
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
    public function testSerializeWithInvalidValue()
    {
        $this->assertNull($this->type->serialize(42));
    }

    /**
     * @throws \Exception
     */
    public function testParseValue()
    {
        $this->assertEquals(1, $this->type->parseValue('GREEN'));
    }

    /**
     * @throws \Exception
     */
    public function testParseWithNonString()
    {
        $this->assertNull($this->type->parseValue(42));
        $this->assertNull($this->type->parseValue('PURPLE'));
    }

    // TODO: Test parseLiteral when it's implemented
}
