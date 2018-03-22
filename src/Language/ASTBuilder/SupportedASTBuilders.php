<?php

namespace Digia\GraphQL\Language\ASTBuilder;

class SupportedASTBuilders
{
    /**
     * @var ASTBuilderInterface[]
     */
    private static $builders;

    /**
     * @var array
     */
    private static $supportedBuilders = [
        // Standard
        DocumentASTBuilder::class,
        OperationDefinitionASTBuilder::class,
        SelectionSetASTBuilder::class,
        VariableDefinitionASTBuilder::class,
        FragmentASTBuilder::class,
        FieldASTBuilder::class,
        NameASTBuilder::class,
        NamedTypeASTBuilder::class,
        TypeReferenceASTBuilder::class,
        DirectivesASTBuilder::class,
        ArgumentsASTBuilder::class,
        ValueLiteralASTBuilder::class,
        StringLiteralASTBuilder::class,
        VariableASTBuilder::class,
        // Schema Definition Language
        SchemaDefinitionASTBuilder::class,
        ScalarTypeDefinitionASTBuilder::class,
        ScalarTypeExtensionASTBuilder::class,
        ObjectTypeDefinitionASTBuilder::class,
        ObjectTypeExtensionASTBuilder::class,
        ImplementsInterfacesASTBuilder::class,
        InterfaceTypeDefinitionASTBuilder::class,
        InterfaceTypeExtensionASTBuilder::class,
        EnumTypeDefinitionASTBuilder::class,
        EnumTypeExtensionASTBuilder::class,
        EnumValuesDefinitionASTBuilder::class,
        UnionTypeDefinitionASTBuilder::class,
        UnionTypeExtensionASTBuilder::class,
        UnionMemberTypesASTBuilder::class,
        InputObjectTypeDefinitionASTBuilder::class,
        InputObjectTypeExtensionASTBuilder::class,
        InputFieldsDefinitionASTBuilder::class,
        InputValueDefinitionASTBuilder::class,
        DirectiveDefinitionASTBuilder::class,
        DescriptionASTBuilder::class,
        FragmentDefinitionASTBuilder::class,
        FieldsDefinitionASTBuilder::class,
        ArgumentsDefinitionASTBuilder::class,
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
