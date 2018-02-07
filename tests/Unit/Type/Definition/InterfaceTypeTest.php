<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Language\AST\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\StringType;

/**
 * Class UnionTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property InterfaceType $type
 */
class InterfaceTypeTest extends AbstractTypeTestCase
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
            'astNode'     => new InterfaceTypeDefinitionNode(),
        ];

        $this->type = new InterfaceType($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConfig()
    {
        $this->assertEquals($this->config['name'], $this->type->getName());
        $this->assertEquals($this->config['description'], $this->type->getDescription());
        $this->assertEquals($this->config, $this->type->getConfig());
        $this->assertInstanceOf(InterfaceTypeDefinitionNode::class, $this->type->getAstNode());
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
