<?php

namespace Digia\GraphQL\Test\Functional\Schema;

use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\Type\newDirective;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\String;

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
     * @var Directive
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
        $this->interfaceType = newInterfaceType([
            'name'   => 'Interface',
            'fields' => [
                'fieldName' => [
                    'type' => String(),
                ],
            ],
        ]);

        $this->implementingType = newObjectType([
            'name'       => 'Object',
            'interfaces' => [$this->interfaceType],
            'fields'     => [
                'fieldName' => [
                    'type'    => String(),
                    'resolve' => function () {
                        return '';
                    },
                ]
            ],
        ]);

        $this->directiveInputType = newInputObjectType([
            'name'   => 'DirInput',
            'fields' => [
                'field' => [
                    'type' => String(),
                ]
            ],
        ]);

        $this->wrappedDirectiveInputType = newInputObjectType([
            'name'   => 'WrappedDirInput',
            'fields' => [
                'field' => [
                    'type' => String(),
                ],
            ],
        ]);

        $this->directive = newDirective([
            'name'      => 'dir',
            'locations' => ['OBJECT'],
            'args'      => [
                'arg'     => [
                    'type' => $this->directiveInputType,
                ],
                'argList' => [
                    'type' => newList($this->wrappedDirectiveInputType),
                ],
            ],
            'fields'    => [
                'field' => [
                    'type' => String(),
                ],
            ],
        ]);

        $this->schema = newSchema([
            'query'      => newObjectType([
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
