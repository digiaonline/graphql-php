<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\StringType;
use Digia\GraphQL\Type\Definition\TypeInterface;

/**
 * Class UnionTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property InterfaceType $type
 */
class InterfaceAbstractTypeTest extends AbstractTypeTestCase
{

    protected function setUp()
    {
        $this->config = [
            'name'        => 'Entity',
            'description' => 'Entity interface.',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => new StringType(),
                ],
            ],
            'astNode'     => null,
        ];

        $this->type = new InterfaceType($this->config);
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
    public function testGetFields()
    {
        $fields = $this->type->getFields();

        $this->assertTrue(is_array($fields));
        $this->assertEquals(1, count($fields));
        $this->assertInstanceOf(Field::class, array_pop($fields));
    }
}
