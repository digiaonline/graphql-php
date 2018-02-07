<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Language\AST\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Test\Unit\TestCase;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\IntType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\StringType;

/**
 * Class ObjectTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property ObjectType $type
 */
class ObjectTypeTest extends AbstractTypeTestCase
{

    protected function setUp()
    {
        $this->config = [
            'name'        => 'Address',
            'description' => 'A street address.',
            'fields'      => [
                [
                    'name' => 'street',
                    'type' => new StringType(),
                ],
                [
                    'name' => 'number',
                    'type' => new IntType(),
                ],
                [
                    'name'    => 'formatted',
                    'type'    => new StringType(),
                    'resolve' => function ($obj) {
                        return $obj['number'] . ' ' . $obj['street'];
                    }
                ]
            ],
            'interfaces'  => [
                [
                    'name'   => 'Owner',
                    'fields' => [
                        [
                            'name' => 'name',
                            'type' => new StringType(),
                        ]
                    ],
                ],
            ],
            'isTypeOf'    => function () {
                return true;
            },
            'astNode'     => new ObjectTypeDefinitionNode(),
        ];

        $this->type = new ObjectType($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConfig()
    {
        $this->assertEquals($this->config['name'], $this->type->getName());
        $this->assertEquals($this->config['description'], $this->type->getDescription());
        $this->assertEquals($this->config, $this->type->getConfig());
        $this->assertTrue(is_callable($this->type->getIsTypeOf()));
        $this->assertInstanceOf(ObjectTypeDefinitionNode::class, $this->type->getAstNode());
    }

    /**
     * @throws \Exception
     */
    public function testGetFields()
    {
        // TODO: Move these this to a new test; FieldTraitTest

        $fields = $this->type->getFields();

        $this->assertTrue(is_array($fields));
        $this->assertEquals(3, count($fields));
        $this->assertInstanceOf(Field::class, array_pop($fields));
    }

    /**
     * @throws \Exception
     */
    public function testGetInterfaces()
    {
        $interfaces = $this->type->getInterfaces();

        $this->assertTrue(is_array($interfaces));
        $this->assertEquals(1, count($interfaces));
        $this->assertInstanceOf(InterfaceType::class, array_pop($interfaces));
    }
}
