<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\Contract\DirectiveInterface;
use function Digia\GraphQL\Type\GraphQLDirective;
use function Digia\GraphQL\Type\GraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use Digia\GraphQL\Type\Schema;

class SchemaTest extends TestCase
{

    /**
     * @var InterfaceType
     */
    protected $interfaceType;

    /**
     * @var ObjectType
     */
    protected $implementingType;

    /**
     * @var InputObjectType
     */
    protected $directiveInputType;

    /**
     * @var InputObjectType
     */
    protected $wrappedDirectiveInputType;

    /**
     * @var DirectiveInterface
     */
    protected $directive;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @throws \TypeError
     */
    public function setUp()
    {
        $this->interfaceType = GraphQLInterfaceType([
            'name'   => 'Interface',
            'fields' => [
                'fieldName' => [
                    'type' => GraphQLString(),
                ],
            ],
        ]);

        $this->implementingType = GraphQLObjectType([
            'name'       => 'Object',
            'interfaces' => [$this->interfaceType],
            'fields'     => [
                'fieldName' => [
                    'type'    => GraphQLString(),
                    'resolve' => function () {
                        return '';
                    },
                ]
            ],
        ]);

        $this->directiveInputType = GraphQLInputObjectType([
            'name'   => 'DirInput',
            'fields' => [
                'field' => [
                    'type' => GraphQLString(),
                ]
            ],
        ]);

        $this->wrappedDirectiveInputType = GraphQLInputObjectType([
            'name'   => 'WrappedDirInput',
            'fields' => [
                'field' => [
                    'type' => GraphQLString(),
                ],
            ],
        ]);

        $this->directive = GraphQLDirective([
            'name'      => 'dir',
            'locations' => ['OBJECT'],
            'args' => [
                'arg'     => [
                    'type' => $this->directiveInputType,
                ],
                'argList' => [
                    'type' => GraphQLList($this->wrappedDirectiveInputType),
                ],
            ],
            'fields'    => [
                'field' => [
                    'type' => GraphQLString(),
                ],
            ],
        ]);

        $this->schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'getObject' => [
                        'type'    => $this->interfaceType,
                        'resolve' => function () {
                            return '';
                        }
                    ],
                ],
            ]),
            'directives' => [
                $this->directive,
            ],
        ]);

        $this->schema;
    }

    /**
     * @expectedException \Exception
     */
    public function testThrowsHumanReadableErrorIfSchemaTypesIsNotDefined()
    {
        $this->schema->isPossibleType($this->interfaceType, $this->implementingType);
    }

    /**
     * @throws \Exception
     */
    public function testIncludesInputTypesOnlyUsedInDirectives()
    {
        $typeMap = $this->schema->getTypeMap();

        $this->assertArrayHasKey('DirInput', $typeMap);
        $this->assertArrayHasKey('WrappedDirInput', $typeMap);
    }
}
