<?php

namespace Digia\GraphQL\Language\NodeBuilder;

class SupportedNodeBuilders
{
    /**
     * @var NodeBuilderInterface[]
     */
    private static $builders;

    /**
     * @var array
     */
    private static $supportedBuilders = [
        ArgumentNodeBuilder::class,
        BooleanNodeBuilder::class,
        DirectiveNodeBuilder::class,
        DocumentNodeBuilder::class,
        EnumNodeBuilder::class,
        FieldNodeBuilder::class,
        FloatValueNodeBuilder::class,
        FragmentDefinitionNodeBuilder::class,
        FragmentSpreadNodeBuilder::class,
        InlineFragmentNodeBuilder::class,
        IntValueNodeBuilder::class,
        ListValueNodeBuilder::class,
        ListTypeNodeBuilder::class,
        NameNodeBuilder::class,
        NamedTypeNodeBuilder::class,
        NonNullTypeNodeBuilder::class,
        NullValueNodeBuilder::class,
        ObjectValueNodeBuilder::class,
        ObjectFieldNodeBuilder::class,
        OperationDefinitionNodeBuilder::class,
        SelectionSetNodeBuilder::class,
        StringValueNodeBuilder::class,
        VariableNodeBuilder::class,
        VariableDefinitionNodeBuilder::class,
        // Schema Definition Language (SDL)
        SchemaDefinitionNodeBuilder::class,
        OperationTypeDefinitionNodeBuilder::class,
        FieldDefinitionNodeBuilder::class,
        ScalarTypeDefinitionNodeBuilder::class,
        ObjectTypeDefinitionNodeBuilder::class,
        InterfaceTypeDefinitionNodeBuilder::class,
        UnionTypeDefinitionNodeBuilder::class,
        EnumTypeDefinitionNodeBuilder::class,
        EnumValueDefinitionNodeBuilder::class,
        InputObjectTypeDefinitionNodeBuilder::class,
        InputValueDefinitionNodeBuilder::class,
        ScalarTypeExtensionNodeBuilder::class,
        ObjectTypeExtensionNodeBuilder::class,
        InterfaceTypeExtensionNodeBuilder::class,
        EnumTypeExtensionNodeBuilder::class,
        UnionTypeExtensionNodeBuilder::class,
        InputObjectTypeExtensionNodeBuilder::class,
        DirectiveDefinitionNodeBuilder::class,
    ];

    /**
     * @return array
     */
    public static function get(): array
    {
        if (null === self::$builders) {
            self::$builders = [];

            foreach (self::$supportedBuilders as $className) {
                self::$builders[] = new $className();
            }
        }

        return self::$builders;
    }
}
