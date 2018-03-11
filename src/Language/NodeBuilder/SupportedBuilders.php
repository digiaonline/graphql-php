<?php

namespace Digia\GraphQL\Language\NodeBuilder;

class SupportedBuilders
{
    /**
     * @var BuilderInterface[]
     */
    private static $builders;

    /**
     * @var array
     */
    private static $supportedBuilders = [
        ArgumentBuilder::class,
        BooleanBuilder::class,
        DirectiveBuilder::class,
        DocumentBuilder::class,
        EnumBuilder::class,
        FieldBuilder::class,
        FloatBuilder::class,
        FragmentDefinitionBuilder::class,
        FragmentSpreadBuilder::class,
        InlineFragmentBuilder::class,
        IntBuilder::class,
        ListBuilder::class,
        ListTypeBuilder::class,
        NameBuilder::class,
        NamedTypeBuilder::class,
        NonNullTypeBuilder::class,
        NullBuilder::class,
        ObjectBuilder::class,
        ObjectFieldBuilder::class,
        OperationDefinitionBuilder::class,
        SelectionSetBuilder::class,
        StringBuilder::class,
        VariableBuilder::class,
        VariableDefinitionBuilder::class,
        // Schema Definition Language (SDL)
        SchemaDefinitionBuilder::class,
        OperationTypeDefinitionBuilder::class,
        FieldDefinitionBuilder::class,
        ScalarTypeDefinitionBuilder::class,
        ObjectTypeDefinitionBuilder::class,
        InterfaceTypeDefinitionBuilder::class,
        UnionTypeDefinitionBuilder::class,
        EnumTypeDefinitionBuilder::class,
        EnumValueDefinitionBuilder::class,
        InputObjectTypeDefinitionBuilder::class,
        InputValueDefinitionBuilder::class,
        ScalarTypeExtensionBuilder::class,
        ObjectTypeExtensionBuilder::class,
        InterfaceTypeExtensionBuilder::class,
        EnumTypeExtensionBuilder::class,
        UnionTypeExtensionBuilder::class,
        InputObjectTypeExtensionBuilder::class,
        DirectiveDefinitionBuilder::class,
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
