<?php

namespace Digia\GraphQL\Test\Unit\Type\Definition;

use Digia\GraphQL\Type\Definition\FloatType;
use Digia\GraphQL\Type\Definition\InputField;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\NonNullType;

/**
 * Class InputObjectTypeTest
 *
 * @package Digia\GraphQL\Test\Unit\Type\Definition
 * @property InputObjectType $type
 */
class InputObjectTypeTest extends TypeTestCase
{

    /**
     * @throws \TypeError
     */
    protected function setUp()
    {
        /**
         * const GeoPoint = new GraphQLInputObjectType({
         *       name: 'GeoPoint',
         *       fields: {
         *         lat: { type: GraphQLNonNull(GraphQLFloat) },
         *         lon: { type: GraphQLNonNull(GraphQLFloat) },
         *         alt: { type: GraphQLFloat, defaultValue: 0 },
         *       }
         *     });
         */

        $this->config = [
            'name'        => 'GeoPoint',
            'description' => 'GeoPoint input.',
            'fields'      => [
                [
                    'name' => 'lat',
                    'type' => new NonNullType(new FloatType()),
                ],
                [
                    'name' => 'lon',
                    'type' => new NonNullType(new FloatType()),
                ],
                [
                    'name'         => 'alt',
                    'type'         => new FloatType(),
                    'defaultValue' => 0,
                ],
            ],
            'astNode'     => null,
        ];

        $this->type = new InputObjectType($this->config);
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
        $this->assertEquals(3, count($fields));
        $this->assertInstanceOf(InputField::class, array_pop($fields));
    }
}
