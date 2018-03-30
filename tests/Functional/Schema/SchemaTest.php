<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\Type\newGraphQLDirective;
use function Digia\GraphQL\Type\newGraphQLInputObjectType;
use function Digia\GraphQL\Type\newGraphQLInterfaceType;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

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
     * @inheritdoc
     */
    public function setUp()
    {
        $this->interfaceType = newGraphQLInterfaceType([
            'name'   => 'Interface',
            'fields' => [
                'fieldName' => [
                    'type' => GraphQLString(),
                ],
            ],
        ]);

        $this->implementingType = newGraphQLObjectType([
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

        $this->directiveInputType = newGraphQLInputObjectType([
            'name'   => 'DirInput',
            'fields' => [
                'field' => [
                    'type' => GraphQLString(),
                ]
            ],
        ]);

        $this->wrappedDirectiveInputType = newGraphQLInputObjectType([
            'name'   => 'WrappedDirInput',
            'fields' => [
                'field' => [
                    'type' => GraphQLString(),
                ],
            ],
        ]);

        $this->directive = newGraphQLDirective([
            'name'      => 'dir',
            'locations' => ['OBJECT'],
            'args'      => [
                'arg'     => [
                    'type' => $this->directiveInputType,
                ],
                'argList' => [
                    'type' => newGraphQLList($this->wrappedDirectiveInputType),
                ],
            ],
            'fields'    => [
                'field' => [
                    'type' => GraphQLString(),
                ],
            ],
        ]);

        $this->schema = newGraphQLSchema([
            'query'      => newGraphQLObjectType([
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

    public function testIncludesInputTypesOnlyUsedInDirectives()
    {
        $typeMap = $this->schema->getTypeMap();

        $this->assertArrayHasKey('DirInput', $typeMap);
        $this->assertArrayHasKey('WrappedDirInput', $typeMap);
    }
}
