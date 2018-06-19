<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Node\DocumentNode;

/**
 * Interface ParserInterface
 * @package Digia\GraphQL\Language
 *
 * @method parseValue($source, array $options = []): ValueNodeInterface
 * @method parseType($source, array $options = []): TypeNodeInterface
 * @method parseName($source, array $options = []): NameNode
 * @method parseOperationDefinition($source, array $options = []): OperationDefinitionNode
 * @method parseVariableDefinition($source, array $options = []): VariableDefinitionNode
 * @method parseVariable($source, array $options = []): VariableNode
 * @method parseSelectionSet($source, array $options = []): SelectionSetNode
 * @method parseField($source, array $options = []): FieldNode
 * @method parseArgument($source, array $options = []): ArgumentNode
 * @method parseFragment($source, array $options = []): FragmentNodeInterface
 * @method parseFragmentDefinition($source, array $options = []): FragmentDefinitionNode
 * @method parseDirective($source, array $options = []): DirectiveNode
 * @method parseNamedType($source, array $options = []): NamedTypeNode
 * @method parseDescription($source, array $options = []): ?StringValueNode
 * @method parseSchemaDefinition($source, array $options = []): SchemaDefinitionNode
 * @method parseOperationTypeDefinition($source, array $options = []): OperationTypeDefinitionNode
 * @method parseScalarTypeDefinition($source, array $options = []): ScalarTypeDefinitionNode
 * @method parseObjectTypeDefinition($source, array $options = []): ObjectTypeDefinitionNode
 * @method parseFieldDefinition($source, array $options = []): FieldDefinitionNode
 * @method parseInputValueDefinition($source, array $options = []): InputValueDefinitionNode
 * @method parseInterfaceTypeDefinition($source, array $options = []): InterfaceTypeDefinitionNode
 * @method parseUnionTypeDefinition($source, array $options = []): UnionTypeDefinitionNode
 * @method parseEnumTypeDefinition($source, array $options = []): EnumTypeDefinitionNode
 * @method parseEnumValueDefinition($source, array $options = []): EnumValueDefinitionNode
 * @method parseInputObjectTypeDefinition($source, array $options = []): InputObjectTypeDefinitionNode
 * @method parseScalarTypeExtension($source, array $options = []): ScalarTypeExtensionNode
 * @method parseObjectTypeExtension($source, array $options = []): ObjectTypeExtensionNode
 * @method parseInterfaceTypeExtension($source, array $options = []): InterfaceTypeExtensionNode
 * @method parseUnionTypeExtension($source, array $options = []): UnionTypeExtensionNode
 * @method parseEnumTypeExtension($source, array $options = []): EnumTypeExtensionNode
 * @method parseInputObjectTypeExtension($source, array $options = []): InputObjectTypeExtensionNode
 * @method parseDirectiveDefinition($source, array $options = []): DirectiveDefinitionNode
 */
interface ParserInterface
{
    /**
     * Given a GraphQL source, parses it into a Document.
     *
     * @param Source|string $source
     * @param array         $options
     * @return DocumentNode
     */
    public function parse($source, array $options = []): DocumentNode;
}
