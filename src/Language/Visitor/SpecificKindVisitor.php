<?php

namespace Digia\GraphQL\Language\Visitor;

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

class SpecificKindVisitor implements VisitorInterface
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        $enterMethod = 'enter' . $node->getKind();
        return $this->{$enterMethod}($node);
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        $leaveMethod = 'leave' . $node->getKind();
        return $this->{$leaveMethod}($node);
    }

    /**
     * @param ArgumentNode $node
     * @return NodeInterface|null
     */
    protected function enterArgument(ArgumentNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ArgumentNode $node
     * @return NodeInterface|null
     */
    protected function leaveArgument(ArgumentNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param BooleanValueNode $node
     * @return NodeInterface|null
     */
    protected function enterBooleanValue(BooleanValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param BooleanValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveBooleanValue(BooleanValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param DirectiveDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterDirectiveDefinition(DirectiveDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param DirectiveDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveDirectiveDefinition(DirectiveDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param DirectiveNode $node
     * @return NodeInterface|null
     */
    protected function enterDirective(DirectiveNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param DirectiveNode $node
     * @return NodeInterface|null
     */
    protected function leaveDirective(DirectiveNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param DocumentNode $node
     * @return NodeInterface|null
     */
    protected function enterDocument(DocumentNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param DocumentNode $node
     * @return NodeInterface|null
     */
    protected function leaveDocument(DocumentNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterEnumTypeDefinition(EnumTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveEnumTypeDefinition(EnumTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function enterEnumTypeExtension(EnumTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function leaveEnumTypeExtension(EnumTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumValueDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterEnumValueDefinition(EnumValueDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumValueDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveEnumValueDefinition(EnumValueDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumValueNode $node
     * @return NodeInterface|null
     */
    protected function enterEnumValue(EnumValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param EnumValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveEnumValue(EnumValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FieldDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterFieldDefinition(FieldDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FieldDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveFieldDefinition(FieldDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FieldNode $node
     * @return NodeInterface|null
     */
    protected function enterField(FieldNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FieldNode $node
     * @return NodeInterface|null
     */
    protected function leaveField(FieldNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FloatValueNode $node
     * @return NodeInterface|null
     */
    protected function enterFloatValue(FloatValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FloatValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveFloatValue(FloatValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FragmentDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FragmentDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveFragmentDefinition(FragmentDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FragmentSpreadNode $node
     * @return FragmentSpreadNode|null
     */
    protected function enterFragmentSpread(FragmentSpreadNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param FragmentSpreadNode $node
     * @return NodeInterface|null
     */
    protected function leaveFragmentSpread(FragmentSpreadNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InlineFragmentNode $node
     * @return NodeInterface|null
     */
    protected function enterInlineFragment(InlineFragmentNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InlineFragmentNode $node
     * @return NodeInterface|null
     */
    protected function leaveInlineFragment(InlineFragmentNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InputObjectTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterInputObjectTypeDefinition(InputObjectTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InputObjectTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveInputObjectTypeDefinition(InputObjectTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InputObjectTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function enterInputObjectTypeExtension(InputObjectTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InputObjectTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function leaveInputObjectTypeExtension(InputObjectTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InputValueDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterInputValueDefinition(InputValueDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InputValueDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveInputValueDefinition(InputValueDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param IntValueNode $node
     * @return NodeInterface|null
     */
    protected function enterIntValue(IntValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param IntValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveIntValue(IntValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InterfaceTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterInterfaceTypeDefinition(InterfaceTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InterfaceTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveInterfaceTypeDefinition(InterfaceTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InterfaceTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function enterInterfaceTypeExtension(InterfaceTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param InterfaceTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function leaveInterfaceTypeExtension(InterfaceTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ListTypeNode $node
     * @return NodeInterface|null
     */
    protected function enterListType(ListTypeNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ListTypeNode $node
     * @return NodeInterface|null
     */
    protected function leaveListType(ListTypeNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ListValueNode $node
     * @return NodeInterface|null
     */
    protected function enterListValue(ListValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ListValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveListValue(ListValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NamedTypeNode $node
     * @return NodeInterface|null
     */
    protected function enterNamedType(NamedTypeNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NamedTypeNode $node
     * @return NodeInterface|null
     */
    protected function leaveNamedType(NamedTypeNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NameNode $node
     * @return NodeInterface|null
     */
    protected function enterName(NameNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NameNode $node
     * @return NodeInterface|null
     */
    protected function leaveName(NameNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NonNullTypeNode $node
     * @return NodeInterface|null
     */
    protected function enterNonNullType(NonNullTypeNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NonNullTypeNode $node
     * @return NodeInterface|null
     */
    protected function leaveNonNullType(NonNullTypeNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NullValueNode $node
     * @return NodeInterface|null
     */
    protected function enterNullValue(NullValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param NullValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveNullValue(NullValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectFieldNode $node
     * @return NodeInterface|null
     */
    protected function enterObjectField(ObjectFieldNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectFieldNode $node
     * @return NodeInterface|null
     */
    protected function leaveObjectField(ObjectFieldNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterObjectTypeDefinition(ObjectTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveObjectTypeDefinition(ObjectTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function enterObjectTypeExtension(ObjectTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function leaveObjectTypeExtension(ObjectTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectValueNode $node
     * @return NodeInterface|null
     */
    protected function enterObjectValue(ObjectValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ObjectValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveObjectValue(ObjectValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param OperationDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param OperationDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveOperationDefinition(OperationDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param OperationTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterOperationTypeDefinition(OperationTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param OperationTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveOperationTypeDefinition(OperationTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterScalarTypeDefinition(ScalarTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveScalarTypeDefinition(ScalarTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ScalarTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function enterScalarTypeExtension(ScalarTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param ScalarTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function leaveScalarTypeExtension(ScalarTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param SchemaDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterSchemaDefinition(SchemaDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param SchemaDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveSchemaDefinition(SchemaDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param SelectionSetNode $node
     * @return NodeInterface|null
     */
    protected function enterSelectionSet(SelectionSetNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param SelectionSetNode $node
     * @return NodeInterface|null
     */
    protected function leaveSelectionSet(SelectionSetNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param StringValueNode $node
     * @return NodeInterface|null
     */
    protected function enterStringValue(StringValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param StringValueNode $node
     * @return NodeInterface|null
     */
    protected function leaveStringValue(StringValueNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param UnionTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterUnionTypeDefinition(UnionTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param UnionTypeDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveUnionTypeDefinition(UnionTypeDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param UnionTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function enterUnionTypeExtension(UnionTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param UnionTypeExtensionNode $node
     * @return NodeInterface|null
     */
    protected function leaveUnionTypeExtension(UnionTypeExtensionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param VariableDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param VariableDefinitionNode $node
     * @return NodeInterface|null
     */
    protected function leaveVariableDefinition(VariableDefinitionNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param VariableNode $node
     * @return NodeInterface|null
     */
    protected function enterVariable(VariableNode $node): ?NodeInterface
    {
        return $node;
    }

    /**
     * @param VariableNode $node
     * @return NodeInterface|null
     */
    protected function leaveVariable(VariableNode $node): ?NodeInterface
    {
        return $node;
    }
}
