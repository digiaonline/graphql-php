<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;

/**
 * Class UnionTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property UnionType $type
 */
class UnionTypeTest extends TypeTestCase
{

    protected function setUp()
    {
        $this->config = [
            'name'        => 'Pet',
            'description' => 'Union of pets.',
            'types'       => [new DogType(), new CatType()],
            'resolveType' => function ($value) {
                if ($value instanceof DogType) {
                    return new DogType();
                }
                if ($value instanceof CatType) {
                    return new CatType();
                }
            },
            'astNode'     => null,
        ];

        $this->type = new UnionType($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testConstructor()
    {
        $this->assertEquals($this->config['name'], $this->type->getName());
        $this->assertEquals($this->config['description'], $this->type->getDescription());
        $this->assertEquals($this->config['astNode'], $this->type->getAstNode());
        $this->assertEquals($this->config['resolveType'], $this->type->getResolveType());
        $this->assertEquals($this->config, $this->type->getConfig());
    }

    /**
     * @throws \Exception
     */
    public function testGetTypes()
    {
        $types = $this->type->getTypes();

        $this->assertTrue(is_array($types));
        $this->assertEquals(2, count($types));
        $this->assertInstanceOf(TypeInterface::class, array_pop($types));
    }
}

class DogType extends ObjectType
{

}

class CatType extends ObjectType
{

}
