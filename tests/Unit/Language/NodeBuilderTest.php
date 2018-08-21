<?php

namespace Digia\GraphQL\Test\Unit\Language;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\BooleanValueNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumTypeExtensionNode;
use Digia\GraphQL\Language\Node\EnumValueNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FloatValueNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\NullValueNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeExtensionNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Language\Node\UnionTypeExtensionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Language\NodeBuilderInterface;
use Digia\GraphQL\Test\TestCase;

class NodeBuilderTest extends TestCase
{
    /**
     * @var NodeBuilderInterface
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = GraphQL::make(NodeBuilderInterface::class);
    }

    public function testBuildArgumentNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::ARGUMENT,
            'name'  => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someArgument',
            ],
            'value' => [
                'kind'  => NodeKindEnum::STRING,
                'value' => 'someValue',
            ],
        ]);

        $this->assertInstanceOf(ArgumentNode::class, $node);
    }

    public function testBuildBooleanValueNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::BOOLEAN,
            'value' => true,
        ]);

        $this->assertInstanceOf(BooleanValueNode::class, $node);
    }

    public function testBuildDirectiveNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::DIRECTIVE,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someDirective',
            ],
        ]);

        $this->assertInstanceOf(DirectiveNode::class, $node);
    }

    public function testBuildDocumentNode()
    {
        $node = $this->builder->build([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                'query' => [
                    'kind'   => NodeKindEnum::OBJECT,
                    'name'   => [
                        'kind'  => NodeKindEnum::NAME,
                        'value' => 'Query',
                    ],
                    'fields' => [
                        [
                            'kind' => NodeKindEnum::FIELD,
                            'name' => [
                                'kind'  => NodeKindEnum::NAME,
                                'value' => 'queryField',
                            ],
                            'type' => [
                                'kind' => NodeKindEnum::NAMED_TYPE,
                                'name' => [
                                    'kind'  => NodeKindEnum::NAME,
                                    'value' => 'String',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(DocumentNode::class, $node);
    }

    public function testBuildEnumValueNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::ENUM,
            'value' => [
                'kind'  => NodeKindEnum::STRING,
                'value' => 'someValue',
            ],
        ]);

        $this->assertInstanceOf(EnumValueNode::class, $node);
    }

    public function testBuildEnumTypeDefinitionNode()
    {
        $node = $this->builder->build([
            'kind'   => NodeKindEnum::ENUM_TYPE_DEFINITION,
            'name'   => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someEnum',
            ],
            'values' => [
                [
                    'kind'  => NodeKindEnum::ENUM,
                    'value' => [
                        'kind'  => NodeKindEnum::STRING,
                        'value' => 'someValue',
                    ],
                ],
                [
                    'kind'  => NodeKindEnum::ENUM,
                    'value' => [
                        'kind'  => NodeKindEnum::STRING,
                        'value' => 'someOtherValue',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(EnumTypeDefinitionNode::class, $node);
    }

    public function testBuildEnumTypeExtensionNode()
    {
        $node = $this->builder->build([
            'kind'   => NodeKindEnum::ENUM_TYPE_EXTENSION,
            'name'   => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someEnum',
            ],
            'values' => [
                [
                    'kind'  => NodeKindEnum::ENUM,
                    'value' => [
                        'kind'  => NodeKindEnum::STRING,
                        'value' => 'someOtherValue',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(EnumTypeExtensionNode::class, $node);
    }

    public function testBuildFieldDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::FIELD_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someField',
            ],
            'type' => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'value' => 'String',
                ],
            ],
        ]);

        $this->assertInstanceOf(FieldDefinitionNode::class, $node);
    }

    public function testBuildFieldNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::FIELD,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someField',
            ],
            'type' => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'value' => 'String',
                ],
            ],
        ]);

        $this->assertInstanceOf(FieldNode::class, $node);
    }

    public function testBuildFloatValueNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::FLOAT,
            'value' => 0.42,
        ]);

        $this->assertInstanceOf(FloatValueNode::class, $node);
    }

    public function testBuildFragmentDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::FRAGMENT_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someFragment',
            ],
        ]);

        $this->assertInstanceOf(FragmentDefinitionNode::class, $node);
    }

    public function testBuildFragmentSpreadNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::FRAGMENT_SPREAD,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someFragment',
            ],
        ]);

        $this->assertInstanceOf(FragmentSpreadNode::class, $node);
    }

    public function testBuildInlineFragmentNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::INLINE_FRAGMENT,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someFragment',
            ],
        ]);

        $this->assertInstanceOf(InlineFragmentNode::class, $node);
    }

    public function testBuildInputObjectTypeDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someInputObject',
            ],
        ]);

        $this->assertInstanceOf(InputObjectTypeDefinitionNode::class, $node);
    }

    public function testBuildInputObjectTypeExtensionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someOtherInputObject',
            ],
        ]);

        $this->assertInstanceOf(InputObjectTypeExtensionNode::class, $node);
    }

    public function testBuildInputValueDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::INPUT_VALUE_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someInputValue',
            ],
            'type' => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'value' => 'String',
                ],
            ],
        ]);

        $this->assertInstanceOf(InputValueDefinitionNode::class, $node);
    }

    public function testBuildInterfaceTypeDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::INTERFACE_TYPE_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someInterface',
            ],
        ]);

        $this->assertInstanceOf(InterfaceTypeDefinitionNode::class, $node);
    }

    public function testBuildInterfaceTypeExtensionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::INTERFACE_TYPE_EXTENSION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someOtherInterface',
            ],
        ]);

        $this->assertInstanceOf(InterfaceTypeExtensionNode::class, $node);
    }

    public function testBuildIntValueNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::INT,
            'value' => 42,
        ]);

        $this->assertInstanceOf(IntValueNode::class, $node);
    }

    public function testBuildListTypeNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::LIST_TYPE,
            'type' => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'value' => 'String',
                ],
            ],
        ]);

        $this->assertInstanceOf(ListTypeNode::class, $node);
    }

    public function testBuildNamedTypeNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::NAMED_TYPE,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'String',
            ],
        ]);

        $this->assertInstanceOf(NamedTypeNode::class, $node);
    }

    public function testBuildNameNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::NAME,
            'value' => 'SomeName',
        ]);

        $this->assertInstanceOf(NameNode::class, $node);
    }

    public function testBuildNullValueNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::NULL,
        ]);

        $this->assertInstanceOf(NullValueNode::class, $node);
    }

    public function testBuildObjectFieldNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::OBJECT_FIELD,
            'name'  => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someObjectField',
            ],
            'value' => [
                'kind'  => NodeKindEnum::STRING,
                'value' => 'someValue',
            ],
        ]);

        $this->assertInstanceOf(ObjectFieldNode::class, $node);
    }

    public function testBuildObjectTypeDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::OBJECT_TYPE_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someObject',
            ],
        ]);

        $this->assertInstanceOf(ObjectTypeDefinitionNode::class, $node);
    }

    public function testBuildObjectTypeExtensionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::OBJECT_TYPE_EXTENSION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someOtherObject',
            ],
        ]);

        $this->assertInstanceOf(ObjectTypeExtensionNode::class, $node);
    }

    public function testBuildObjectValueNode()
    {
        $node = $this->builder->build([
            'kind'   => NodeKindEnum::OBJECT,
            'fields' => [
                [
                    'kind' => NodeKindEnum::FIELD,
                    'name' => [
                        'kind'  => NodeKindEnum::NAME,
                        'value' => 'someField',
                    ],
                    'type' => [
                        'kind' => NodeKindEnum::NAMED_TYPE,
                        'name' => [
                            'kind'  => NodeKindEnum::NAME,
                            'value' => 'String',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(ObjectValueNode::class, $node);
    }

    public function testBuildOperationDefinitionNode()
    {
        $node = $this->builder->build([
            'kind'      => NodeKindEnum::OPERATION_DEFINITION,
            'operation' => 'query',
            'name'      => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'Query',
            ],
        ]);

        $this->assertInstanceOf(OperationDefinitionNode::class, $node);
    }

    public function testBuildOperationTypeDefinitionNode()
    {
        $node = $this->builder->build([
            'kind'      => NodeKindEnum::OPERATION_TYPE_DEFINITION,
            'operation' => 'query',
            'type'      => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'value' => 'String',
                ],
            ],
        ]);

        $this->assertInstanceOf(OperationTypeDefinitionNode::class, $node);
    }

    public function testBuildScalarTypeDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::SCALAR_TYPE_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'SomeScalar',
            ],
        ]);

        $this->assertInstanceOf(ScalarTypeDefinitionNode::class, $node);
    }

    public function testBuildScalarTypeExtensionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::SCALAR_TYPE_EXTENSION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'SomeOtherScalar',
            ],
        ]);

        $this->assertInstanceOf(ScalarTypeExtensionNode::class, $node);
    }

    public function testBuildSchemaDefinitionNode()
    {
        $node = $this->builder->build([
            'kind'           => NodeKindEnum::SCHEMA_DEFINITION,
            'operationTypes' => [
                [
                    'kind'      => NodeKindEnum::OPERATION_TYPE_DEFINITION,
                    'operation' => 'query',
                    'type'      => [
                        'kind' => NodeKindEnum::NAMED_TYPE,
                        'name' => [
                            'kind'  => NodeKindEnum::NAME,
                            'value' => 'String',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(SchemaDefinitionNode::class, $node);
    }

    public function testBuildSelectionSetNode()
    {
        $node = $this->builder->build([
            'kind'       => NodeKindEnum::SELECTION_SET,
            'selections' => [
                [
                    'kind' => NodeKindEnum::FIELD,
                    'name' => [
                        'kind'  => NodeKindEnum::NAME,
                        'value' => 'someField',
                    ],
                    'type' => [
                        'kind' => NodeKindEnum::NAMED_TYPE,
                        'name' => [
                            'kind'  => NodeKindEnum::NAME,
                            'value' => 'String',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(SelectionSetNode::class, $node);
    }

    public function testBuildStringValueNode()
    {
        $node = $this->builder->build([
            'kind'  => NodeKindEnum::STRING,
            'value' => 'someValue',
        ]);

        $this->assertInstanceOf(StringValueNode::class, $node);
    }

    public function testBuildUnionTypeDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::UNION_TYPE_DEFINITION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'SomeUnion',
            ],
            'types' => [
                [
                    'kind' => NodeKindEnum::NAMED_TYPE,
                    'name' => [
                        'kind'  => NodeKindEnum::NAME,
                        'value' => 'SomeType',
                    ],
                ],
                [
                    'kind' => NodeKindEnum::NAMED_TYPE,
                    'name' => [
                        'kind'  => NodeKindEnum::NAME,
                        'value' => 'SomeOtherType',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(UnionTypeDefinitionNode::class, $node);
    }

    public function testBuildUnionTypeExtensionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::UNION_TYPE_EXTENSION,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'SomeOtherUnion',
            ],
            'types' => [
                [
                    'kind' => NodeKindEnum::NAMED_TYPE,
                    'name' => [
                        'kind'  => NodeKindEnum::NAME,
                        'value' => 'SomeOtherType',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(UnionTypeExtensionNode::class, $node);
    }

    public function testBuildVariableDefinitionNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::VARIABLE_DEFINITION,
            'variable' => [
                'kind' => NodeKindEnum::VARIABLE,
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'value' => 'someVariable',
                ],
            ],
            'type' => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'value' => 'String',
                ],
            ],
        ]);

        $this->assertInstanceOf(VariableDefinitionNode::class, $node);
    }

    public function testBuildVariableNode()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::VARIABLE,
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'value' => 'someVariable',
            ],
        ]);

        $this->assertInstanceOf(VariableNode::class, $node);
    }

    public function testCreateLocation()
    {
        $node = $this->builder->build([
            'kind' => NodeKindEnum::NAME,
            'value' => 'SomeType',
            'loc' => ['start' => 0, 'end' => 8],
        ]);

        $this->assertInstanceOf(Location::class, $node->getLocation());
    }
}
