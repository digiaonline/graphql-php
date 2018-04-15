<?php

// TODO: Move this file under the Node namespace

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\BooleanValueNode;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumTypeExtensionNode;
use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
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
use Digia\GraphQL\Language\Node\ListValueNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
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

class NodeBuilder implements NodeBuilderInterface
{
    /**
     * @param array $ast
     * @return NodeInterface
     * @throws LanguageException
     */
    public function build(array $ast): NodeInterface
    {
        if (!isset($ast['kind'])) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new LanguageException(\sprintf('Nodes must specify a kind, got %s', \json_encode($ast)));
        }

        ['kind' => $kind] = $ast;

        switch ($kind) {
            case NodeKindEnum::ARGUMENT:
                return $this->buildArgument($ast);
            case NodeKindEnum::BOOLEAN:
                return $this->buildBoolean($ast);
            case NodeKindEnum::DIRECTIVE_DEFINITION:
                return $this->buildDirectiveDefinition($ast);
            case NodeKindEnum::DIRECTIVE:
                return $this->buildDirective($ast);
            case NodeKindEnum::DOCUMENT:
                return $this->buildDocument($ast);
            case NodeKindEnum::ENUM:
                return $this->buildEnum($ast);
            case NodeKindEnum::ENUM_TYPE_DEFINITION:
                return $this->buildEnumTypeDefinition($ast);
            case NodeKindEnum::ENUM_TYPE_EXTENSION:
                return $this->buildEnumTypeExtension($ast);
            case NodeKindEnum::ENUM_VALUE_DEFINITION:
                return $this->buildEnumValueDefinition($ast);
            case NodeKindEnum::FIELD:
                return $this->buildField($ast);
            case NodeKindEnum::FIELD_DEFINITION:
                return $this->buildFieldDefinition($ast);
            case NodeKindEnum::FLOAT:
                return $this->buildFloat($ast);
            case NodeKindEnum::FRAGMENT_DEFINITION:
                return $this->buildFragmentDefinition($ast);
            case NodeKindEnum::FRAGMENT_SPREAD:
                return $this->buildFragmentSpread($ast);
            case NodeKindEnum::INLINE_FRAGMENT:
                return $this->buildInlineFragment($ast);
            case NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION:
                return $this->buildInputObjectTypeDefinition($ast);
            case NodeKindEnum::INPUT_OBJECT_TYPE_EXTENSION:
                return $this->buildInputObjectTypeExtension($ast);
            case NodeKindEnum::INPUT_VALUE_DEFINITION:
                return $this->buildInputValueDefinition($ast);
            case NodeKindEnum::INTERFACE_TYPE_DEFINITION:
                return $this->buildInterfaceTypeDefinition($ast);
            case NodeKindEnum::INTERFACE_TYPE_EXTENSION:
                return $this->buildInterfaceTypeExtension($ast);
            case NodeKindEnum::INT:
                return $this->buildInt($ast);
            case NodeKindEnum::LIST_TYPE:
                return $this->buildListType($ast);
            case NodeKindEnum::LIST:
                return $this->buildList($ast);
            case NodeKindEnum::NAMED_TYPE:
                return $this->buildNamedType($ast);
            case NodeKindEnum::NAME:
                return $this->buildName($ast);
            case NodeKindEnum::NON_NULL_TYPE:
                return $this->buildNonNullType($ast);
            case NodeKindEnum::NULL:
                return $this->buildNull($ast);
            case NodeKindEnum::OBJECT_FIELD:
                return $this->buildObjectField($ast);
            case NodeKindEnum::OBJECT_TYPE_DEFINITION:
                return $this->buildObjectTypeDefinition($ast);
            case NodeKindEnum::OBJECT_TYPE_EXTENSION:
                return $this->buildObjectTypeExtension($ast);
            case NodeKindEnum::OBJECT:
                return $this->buildObject($ast);
            case NodeKindEnum::OPERATION_DEFINITION:
                return $this->buildOperationDefinition($ast);
            case NodeKindEnum::OPERATION_TYPE_DEFINITION:
                return $this->buildOperationTypeDefinition($ast);
            case NodeKindEnum::SCALAR_TYPE_DEFINITION:
                return $this->buildScalarTypeDefinition($ast);
            case NodeKindEnum::SCALAR_TYPE_EXTENSION:
                return $this->buildScalarTypeExtension($ast);
            case NodeKindEnum::SCHEMA_DEFINITION:
                return $this->buildSchemaDefinition($ast);
            case NodeKindEnum::SELECTION_SET:
                return $this->buildSelectionSet($ast);
            case NodeKindEnum::STRING:
                return $this->buildString($ast);
            case NodeKindEnum::UNION_TYPE_DEFINITION:
                return $this->buildUnionTypeDefinition($ast);
            case NodeKindEnum::UNION_TYPE_EXTENSION:
                return $this->buildUnionTypeExtension($ast);
            case NodeKindEnum::VARIABLE_DEFINITION:
                return $this->buildVariableDefinition($ast);
            case NodeKindEnum::VARIABLE:
                return $this->buildVariable($ast);
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new LanguageException(\sprintf('Node of kind "%s" not supported.', $kind));
    }

    /**
     * @param array $ast
     * @return ArgumentNode
     * @throws LanguageException
     */
    protected function buildArgument(array $ast): ArgumentNode
    {
        return new ArgumentNode(
            $this->buildNode($ast, 'name'),
            $this->buildNode($ast, 'value'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return BooleanValueNode
     */
    protected function buildBoolean(array $ast): BooleanValueNode
    {
        return new BooleanValueNode(
            $this->getValue($ast, 'value'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return DirectiveDefinitionNode
     * @throws LanguageException
     */
    protected function buildDirectiveDefinition(array $ast): DirectiveDefinitionNode
    {
        return new DirectiveDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'arguments'),
            $this->buildNodes($ast, 'locations'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return DirectiveNode
     * @throws LanguageException
     */
    protected function buildDirective(array $ast): DirectiveNode
    {
        return new DirectiveNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'arguments'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return DocumentNode
     * @throws LanguageException
     */
    protected function buildDocument(array $ast): DocumentNode
    {
        return new DocumentNode(
            $this->buildNodes($ast, 'definitions'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return EnumValueNode
     */
    protected function buildEnum(array $ast): EnumValueNode
    {
        return new EnumValueNode(
            $this->getValue($ast, 'value'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return EnumTypeDefinitionNode
     * @throws LanguageException
     */
    protected function buildEnumTypeDefinition(array $ast): EnumTypeDefinitionNode
    {
        return new EnumTypeDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'values'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return EnumTypeExtensionNode
     * @throws LanguageException
     */
    protected function buildEnumTypeExtension(array $ast): EnumTypeExtensionNode
    {
        return new EnumTypeExtensionNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'values'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return EnumValueDefinitionNode
     * @throws LanguageException
     */
    protected function buildEnumValueDefinition(array $ast): EnumValueDefinitionNode
    {
        return new EnumValueDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return FieldDefinitionNode
     * @throws LanguageException
     */
    protected function buildFieldDefinition(array $ast): FieldDefinitionNode
    {
        return new FieldDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'arguments'),
            $this->buildNode($ast, 'type'),
            $this->buildNodes($ast, 'directives'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return FieldNode
     * @throws LanguageException
     */
    protected function buildField(array $ast): FieldNode
    {
        return new FieldNode(
            $this->buildNode($ast, 'alias'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'arguments'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNode($ast, 'selectionSet'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return FloatValueNode
     */
    protected function buildFloat(array $ast): FloatValueNode
    {
        return new FloatValueNode(
            $this->getValue($ast, 'value'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return FragmentDefinitionNode
     * @throws LanguageException
     */
    protected function buildFragmentDefinition(array $ast): FragmentDefinitionNode
    {
        return new FragmentDefinitionNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'variableDefinitions'),
            $this->buildNode($ast, 'typeCondition'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNode($ast, 'selectionSet'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return FragmentSpreadNode
     * @throws LanguageException
     */
    protected function buildFragmentSpread(array $ast): FragmentSpreadNode
    {
        return new FragmentSpreadNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNode($ast, 'selectionSet'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return InlineFragmentNode
     * @throws LanguageException
     */
    protected function buildInlineFragment(array $ast): InlineFragmentNode
    {
        return new InlineFragmentNode(
            $this->buildNode($ast, 'typeCondition'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNode($ast, 'selectionSet'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return InputObjectTypeDefinitionNode
     * @throws LanguageException
     */
    protected function buildInputObjectTypeDefinition(array $ast): InputObjectTypeDefinitionNode
    {
        return new InputObjectTypeDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'fields'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return InputObjectTypeExtensionNode
     * @throws LanguageException
     */
    protected function buildInputObjectTypeExtension(array $ast): InputObjectTypeExtensionNode
    {
        return new InputObjectTypeExtensionNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'fields'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return InputValueDefinitionNode
     * @throws LanguageException
     */
    protected function buildInputValueDefinition(array $ast): InputValueDefinitionNode
    {
        return new InputValueDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNode($ast, 'type'),
            $this->buildNode($ast, 'defaultValue'),
            $this->buildNodes($ast, 'directives'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return InterfaceTypeDefinitionNode
     * @throws LanguageException
     */
    protected function buildInterfaceTypeDefinition(array $ast): InterfaceTypeDefinitionNode
    {
        return new InterfaceTypeDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'fields'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return InterfaceTypeExtensionNode
     * @throws LanguageException
     */
    protected function buildInterfaceTypeExtension(array $ast): InterfaceTypeExtensionNode
    {
        return new InterfaceTypeExtensionNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'fields'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return IntValueNode
     */
    protected function buildInt(array $ast): IntValueNode
    {
        return new IntValueNode(
            $this->getValue($ast, 'value'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return ListTypeNode
     * @throws LanguageException
     */
    protected function buildListType(array $ast): ListTypeNode
    {
        return new ListTypeNode(
            $this->buildNode($ast, 'type'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return ListValueNode
     * @throws LanguageException
     */
    protected function buildList(array $ast): ListValueNode
    {
        return new ListValueNode(
            $this->buildNodes($ast, 'values'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return NamedTypeNode
     * @throws LanguageException
     */
    protected function buildNamedType(array $ast): NamedTypeNode
    {
        return new NamedTypeNode(
            $this->buildNode($ast, 'name'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return NameNode
     */
    protected function buildName(array $ast): NameNode
    {
        return new NameNode(
            $this->getValue($ast, 'value'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return NonNullTypeNode
     * @throws LanguageException
     */
    protected function buildNonNullType(array $ast): NonNullTypeNode
    {
        return new NonNullTypeNode(
            $this->buildNode($ast, 'type'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return NullValueNode
     */
    protected function buildNull(array $ast): NullValueNode
    {
        return new NullValueNode($this->createLocation($ast));
    }

    /**
     * @param array $ast
     * @return ObjectFieldNode
     * @throws LanguageException
     */
    protected function buildObjectField(array $ast): ObjectFieldNode
    {
        return new ObjectFieldNode(
            $this->buildNode($ast, 'name'),
            $this->buildNode($ast, 'value'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return ObjectTypeDefinitionNode
     * @throws LanguageException
     */
    protected function buildObjectTypeDefinition(array $ast): ObjectTypeDefinitionNode
    {
        return new ObjectTypeDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'interfaces'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'fields'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return ObjectTypeExtensionNode
     * @throws LanguageException
     */
    protected function buildObjectTypeExtension(array $ast): ObjectTypeExtensionNode
    {
        return new ObjectTypeExtensionNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'interfaces'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'fields'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return ObjectValueNode
     * @throws LanguageException
     */
    protected function buildObject(array $ast): ObjectValueNode
    {
        return new ObjectValueNode(
            $this->buildNodes($ast, 'fields'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return OperationDefinitionNode
     * @throws LanguageException
     */
    protected function buildOperationDefinition(array $ast): OperationDefinitionNode
    {
        return new OperationDefinitionNode(
            $this->getValue($ast, 'operation'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'variableDefinitions'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNode($ast, 'selectionSet'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return OperationTypeDefinitionNode
     * @throws LanguageException
     */
    protected function buildOperationTypeDefinition(array $ast): OperationTypeDefinitionNode
    {
        return new OperationTypeDefinitionNode(
            $this->getValue($ast, 'operation'),
            $this->buildNode($ast, 'type'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return ScalarTypeDefinitionNode
     * @throws LanguageException
     */
    protected function buildScalarTypeDefinition(array $ast): ScalarTypeDefinitionNode
    {
        return new ScalarTypeDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return ScalarTypeExtensionNode
     * @throws LanguageException
     */
    protected function buildScalarTypeExtension(array $ast): ScalarTypeExtensionNode
    {
        return new ScalarTypeExtensionNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return SchemaDefinitionNode
     * @throws LanguageException
     */
    protected function buildSchemaDefinition(array $ast): SchemaDefinitionNode
    {
        return new SchemaDefinitionNode(
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'operationTypes'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return SelectionSetNode
     * @throws LanguageException
     */
    protected function buildSelectionSet(array $ast): SelectionSetNode
    {
        return new SelectionSetNode(
            $this->buildNodes($ast, 'selections'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return StringValueNode
     */
    protected function buildString(array $ast): StringValueNode
    {
        return new StringValueNode(
            $this->getValue($ast, 'value'),
            $this->getValue($ast, 'block', false),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return UnionTypeDefinitionNode
     * @throws LanguageException
     */
    protected function buildUnionTypeDefinition(array $ast): UnionTypeDefinitionNode
    {
        return new UnionTypeDefinitionNode(
            $this->buildNode($ast, 'description'),
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'types'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return UnionTypeExtensionNode
     * @throws LanguageException
     */
    protected function buildUnionTypeExtension(array $ast): UnionTypeExtensionNode
    {
        return new UnionTypeExtensionNode(
            $this->buildNode($ast, 'name'),
            $this->buildNodes($ast, 'directives'),
            $this->buildNodes($ast, 'types'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return VariableDefinitionNode
     * @throws LanguageException
     */
    protected function buildVariableDefinition(array $ast): VariableDefinitionNode
    {
        return new VariableDefinitionNode(
            $this->buildNode($ast, 'variable'),
            $this->buildNode($ast, 'type'),
            $this->buildNode($ast, 'defaultValue'),
            $this->createLocation($ast)
        );
    }

    /**
     * @param array $ast
     * @return VariableNode
     * @throws LanguageException
     */
    protected function buildVariable(array $ast): VariableNode
    {
        return new VariableNode(
            $this->buildNode($ast, 'name'),
            $this->createLocation($ast)
        );
    }

    /**
     * Creates a location object.
     *
     * @param array $ast
     * @return Location|null
     */
    protected function createLocation(array $ast): ?Location
    {
        return isset($ast['loc']['start'], $ast['loc']['end'])
            ? new Location($ast['loc']['start'], $ast['loc']['end'], $ast['loc']['source'] ?? null)
            : null;
    }

    /**
     * Returns the value of a single property in the given AST.
     *
     * @param array  $ast
     * @param string $propertyName
     * @param null   $defaultValue
     * @return mixed|null
     */
    protected function getValue(array $ast, string $propertyName, $defaultValue = null)
    {
        return $ast[$propertyName] ?? $defaultValue;
    }

    /**
     * Builds a single item from the given AST.
     *
     * @param array  $ast
     * @param string $propertyName
     * @return mixed|null
     * @throws LanguageException
     */
    protected function buildNode(array $ast, string $propertyName)
    {
        return isset($ast[$propertyName]) ? $this->build($ast[$propertyName]) : null;
    }

    /**
     * Builds many items from the given AST.
     *
     * @param array  $ast
     * @param string $propertyName
     * @return array
     * @throws LanguageException
     */
    protected function buildNodes(array $ast, string $propertyName): array
    {
        $array = [];

        if (isset($ast[$propertyName]) && \is_array($ast[$propertyName])) {
            foreach ($ast[$propertyName] as $subAst) {
                $array[] = $this->build($subAst);
            }
        }

        return $array;
    }
}
