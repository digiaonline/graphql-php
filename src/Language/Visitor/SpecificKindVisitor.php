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
     * @return ArgumentNode|null
     */
    protected function enterArgument(ArgumentNode $node): ?ArgumentNode
    {
        return $node;
    }

    /**
     * @param ArgumentNode $node
     * @return ArgumentNode|null
     */
    protected function leaveArgument(ArgumentNode $node): ?ArgumentNode
    {
        return $node;
    }

    /**
     * @param BooleanValueNode $node
     * @return BooleanValueNode|null
     */
    protected function enterBooleanValue(BooleanValueNode $node): ?BooleanValueNode
    {
        return $node;
    }

    /**
     * @param BooleanValueNode $node
     * @return BooleanValueNode|null
     */
    protected function leaveBooleanValue(BooleanValueNode $node): ?BooleanValueNode
    {
        return $node;
    }

    /**
     * @param DirectiveDefinitionNode $node
     * @return DirectiveDefinitionNode|null
     */
    protected function enterDirectiveDefinition(DirectiveDefinitionNode $node): ?DirectiveDefinitionNode
    {
        return $node;
    }

    /**
     * @param DirectiveDefinitionNode $node
     * @return DirectiveDefinitionNode|null
     */
    protected function leaveDirectiveDefinition(DirectiveDefinitionNode $node): ?DirectiveDefinitionNode
    {
        return $node;
    }

    /**
     * @param DirectiveNode $node
     * @return DirectiveNode|null
     */
    protected function enterDirective(DirectiveNode $node): ?DirectiveNode
    {
        return $node;
    }

    /**
     * @param DirectiveNode $node
     * @return DirectiveNode|null
     */
    protected function leaveDirective(DirectiveNode $node): ?DirectiveNode
    {
        return $node;
    }

    /**
     * @param DocumentNode $node
     * @return DocumentNode|null
     */
    protected function enterDocument(DocumentNode $node): ?DocumentNode
    {
        return $node;
    }

    /**
     * @param DocumentNode $node
     * @return DocumentNode|null
     */
    protected function leaveDocument(DocumentNode $node): ?DocumentNode
    {
        return $node;
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return EnumTypeDefinitionNode|null
     */
    protected function enterEnumTypeDefinition(EnumTypeDefinitionNode $node): ?EnumTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return EnumTypeDefinitionNode|null
     */
    protected function leaveEnumTypeDefinition(EnumTypeDefinitionNode $node): ?EnumTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param EnumTypeExtensionNode $node
     * @return EnumTypeExtensionNode|null
     */
    protected function enterEnumTypeExtension(EnumTypeExtensionNode $node): ?EnumTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param EnumTypeExtensionNode $node
     * @return EnumTypeExtensionNode|null
     */
    protected function leaveEnumTypeExtension(EnumTypeExtensionNode $node): ?EnumTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param EnumValueDefinitionNode $node
     * @return EnumValueDefinitionNode|null
     */
    protected function enterEnumValueDefinition(EnumValueDefinitionNode $node): ?EnumValueDefinitionNode
    {
        return $node;
    }

    /**
     * @param EnumValueDefinitionNode $node
     * @return EnumValueDefinitionNode|null
     */
    protected function leaveEnumValueDefinition(EnumValueDefinitionNode $node): ?EnumValueDefinitionNode
    {
        return $node;
    }

    /**
     * @param EnumValueNode $node
     * @return EnumValueNode|null
     */
    protected function enterEnumValue(EnumValueNode $node): ?EnumValueNode
    {
        return $node;
    }

    /**
     * @param EnumValueNode $node
     * @return EnumValueNode|null
     */
    protected function leaveEnumValue(EnumValueNode $node): ?EnumValueNode
    {
        return $node;
    }

    /**
     * @param FieldDefinitionNode $node
     * @return FieldDefinitionNode|null
     */
    protected function enterFieldDefinition(FieldDefinitionNode $node): ?FieldDefinitionNode
    {
        return $node;
    }

    /**
     * @param FieldDefinitionNode $node
     * @return FieldDefinitionNode|null
     */
    protected function leaveFieldDefinition(FieldDefinitionNode $node): ?FieldDefinitionNode
    {
        return $node;
    }

    /**
     * @param FieldNode $node
     * @return FieldNode|null
     */
    protected function enterField(FieldNode $node): ?FieldNode
    {
        return $node;
    }

    /**
     * @param FieldNode $node
     * @return FieldNode|null
     */
    protected function leaveField(FieldNode $node): ?FieldNode
    {
        return $node;
    }

    /**
     * @param FloatValueNode $node
     * @return FloatValueNode|null
     */
    protected function enterFloatValue(FloatValueNode $node): ?FloatValueNode
    {
        return $node;
    }

    /**
     * @param FloatValueNode $node
     * @return FloatValueNode|null
     */
    protected function leaveFloatValue(FloatValueNode $node): ?FloatValueNode
    {
        return $node;
    }

    /**
     * @param FragmentDefinitionNode $node
     * @return FragmentDefinitionNode|null
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): ?FragmentDefinitionNode
    {
        return $node;
    }

    /**
     * @param FragmentDefinitionNode $node
     * @return FragmentDefinitionNode|null
     */
    protected function leaveFragmentDefinition(FragmentDefinitionNode $node): ?FragmentDefinitionNode
    {
        return $node;
    }

    /**
     * @param FragmentSpreadNode $node
     * @return FragmentSpreadNode|null
     */
    protected function enterFragmentSpread(FragmentSpreadNode $node): ?FragmentSpreadNode
    {
        return $node;
    }

    /**
     * @param FragmentSpreadNode $node
     * @return FragmentSpreadNode|null
     */
    protected function leaveFragmentSpread(FragmentSpreadNode $node): ?FragmentSpreadNode
    {
        return $node;
    }

    /**
     * @param InlineFragmentNode $node
     * @return InlineFragmentNode|null
     */
    protected function enterInlineFragment(InlineFragmentNode $node): ?InlineFragmentNode
    {
        return $node;
    }

    /**
     * @param InlineFragmentNode $node
     * @return InlineFragmentNode|null
     */
    protected function leaveInlineFragment(InlineFragmentNode $node): ?InlineFragmentNode
    {
        return $node;
    }

    /**
     * @param InputObjectTypeDefinitionNode $node
     * @return InputObjectTypeDefinitionNode|null
     */
    protected function enterInputObjectTypeDefinition(InputObjectTypeDefinitionNode $node
    ): ?InputObjectTypeDefinitionNode {
        return $node;
    }

    /**
     * @param InputObjectTypeDefinitionNode $node
     * @return InputObjectTypeDefinitionNode|null
     */
    protected function leaveInputObjectTypeDefinition(InputObjectTypeDefinitionNode $node
    ): ?InputObjectTypeDefinitionNode {
        return $node;
    }

    /**
     * @param InputObjectTypeExtensionNode $node
     * @return InputObjectTypeExtensionNode|null
     */
    protected function enterInputObjectTypeExtension(InputObjectTypeExtensionNode $node): ?InputObjectTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param InputObjectTypeExtensionNode $node
     * @return InputObjectTypeExtensionNode|null
     */
    protected function leaveInputObjectTypeExtension(InputObjectTypeExtensionNode $node): ?InputObjectTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param InputValueDefinitionNode $node
     * @return InputValueDefinitionNode|null
     */
    protected function enterInputValueDefinition(InputValueDefinitionNode $node): ?InputValueDefinitionNode
    {
        return $node;
    }

    /**
     * @param InputValueDefinitionNode $node
     * @return InputValueDefinitionNode|null
     */
    protected function leaveInputValueDefinition(InputValueDefinitionNode $node): ?InputValueDefinitionNode
    {
        return $node;
    }

    /**
     * @param IntValueNode $node
     * @return IntValueNode|null
     */
    protected function enterIntValue(IntValueNode $node): ?IntValueNode
    {
        return $node;
    }

    /**
     * @param IntValueNode $node
     * @return IntValueNode|null
     */
    protected function leaveIntValue(IntValueNode $node): ?IntValueNode
    {
        return $node;
    }

    /**
     * @param ListTypeNode $node
     * @return ListTypeNode|null
     */
    protected function enterListType(ListTypeNode $node): ?ListTypeNode
    {
        return $node;
    }

    /**
     * @param ListTypeNode $node
     * @return ListTypeNode|null
     */
    protected function leaveListType(ListTypeNode $node): ?ListTypeNode
    {
        return $node;
    }

    /**
     * @param ListValueNode $node
     * @return ListValueNode|null
     */
    protected function enterListValue(ListValueNode $node): ?ListValueNode
    {
        return $node;
    }

    /**
     * @param ListValueNode $node
     * @return ListValueNode|null
     */
    protected function leaveListValue(ListValueNode $node): ?ListValueNode
    {
        return $node;
    }

    /**
     * @param NamedTypeNode $node
     * @return NamedTypeNode|null
     */
    protected function enterNamedType(NamedTypeNode $node): ?NamedTypeNode
    {
        return $node;
    }

    /**
     * @param NamedTypeNode $node
     * @return NamedTypeNode|null
     */
    protected function leaveNamedType(NamedTypeNode $node): ?NamedTypeNode
    {
        return $node;
    }

    /**
     * @param NameNode $node
     * @return NameNode|null
     */
    protected function enterName(NameNode $node): ?NameNode
    {
        return $node;
    }

    /**
     * @param NameNode $node
     * @return NameNode|null
     */
    protected function leaveName(NameNode $node): ?NameNode
    {
        return $node;
    }

    /**
     * @param NonNullTypeNode $node
     * @return NonNullTypeNode|null
     */
    protected function enterNonNullType(NonNullTypeNode $node): ?NonNullTypeNode
    {
        return $node;
    }

    /**
     * @param NonNullTypeNode $node
     * @return NonNullTypeNode|null
     */
    protected function leaveNonNullType(NonNullTypeNode $node): ?NonNullTypeNode
    {
        return $node;
    }

    /**
     * @param NullValueNode $node
     * @return NullValueNode|null
     */
    protected function enterNullValue(NullValueNode $node): ?NullValueNode
    {
        return $node;
    }

    /**
     * @param NullValueNode $node
     * @return NullValueNode|null
     */
    protected function leaveNullValue(NullValueNode $node): ?NullValueNode
    {
        return $node;
    }

    /**
     * @param ObjectFieldNode $node
     * @return ObjectFieldNode|null
     */
    protected function enterObjectField(ObjectFieldNode $node): ?ObjectFieldNode
    {
        return $node;
    }

    /**
     * @param ObjectFieldNode $node
     * @return ObjectFieldNode|null
     */
    protected function leaveObjectField(ObjectFieldNode $node): ?ObjectFieldNode
    {
        return $node;
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return ObjectTypeDefinitionNode|null
     */
    protected function enterObjectTypeDefinition(ObjectTypeDefinitionNode $node): ?ObjectTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return ObjectTypeDefinitionNode|null
     */
    protected function leaveObjectTypeDefinition(ObjectTypeDefinitionNode $node): ?ObjectTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param ObjectTypeExtensionNode $node
     * @return ObjectTypeExtensionNode|null
     */
    protected function enterObjectTypeExtension(ObjectTypeExtensionNode $node): ?ObjectTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param ObjectTypeExtensionNode $node
     * @return ObjectTypeExtensionNode|null
     */
    protected function leaveObjectTypeExtension(ObjectTypeExtensionNode $node): ?ObjectTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param ObjectValueNode $node
     * @return ObjectValueNode|null
     */
    protected function enterObjectValue(ObjectValueNode $node): ?ObjectValueNode
    {
        return $node;
    }

    /**
     * @param ObjectValueNode $node
     * @return ObjectValueNode|null
     */
    protected function leaveObjectValue(ObjectValueNode $node): ?ObjectValueNode
    {
        return $node;
    }

    /**
     * @param OperationDefinitionNode $node
     * @return OperationDefinitionNode|null
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): ?OperationDefinitionNode
    {
        return $node;
    }

    /**
     * @param OperationDefinitionNode $node
     * @return OperationDefinitionNode|null
     */
    protected function leaveOperationDefinition(OperationDefinitionNode $node): ?OperationDefinitionNode
    {
        return $node;
    }

    /**
     * @param OperationTypeDefinitionNode $node
     * @return OperationTypeDefinitionNode|null
     */
    protected function enterOperationTypeDefinition(OperationTypeDefinitionNode $node): ?OperationTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param OperationTypeDefinitionNode $node
     * @return OperationTypeDefinitionNode|null
     */
    protected function leaveOperationTypeDefinition(OperationTypeDefinitionNode $node): ?OperationTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return ScalarTypeDefinitionNode|null
     */
    protected function enterScalarTypeDefinition(ScalarTypeDefinitionNode $node): ?ScalarTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return ScalarTypeDefinitionNode|null
     */
    protected function leaveScalarTypeDefinition(ScalarTypeDefinitionNode $node): ?ScalarTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param ScalarTypeExtensionNode $node
     * @return ScalarTypeExtensionNode|null
     */
    protected function enterScalarTypeExtension(ScalarTypeExtensionNode $node): ?ScalarTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param ScalarTypeExtensionNode $node
     * @return ScalarTypeExtensionNode|null
     */
    protected function leaveScalarTypeExtension(ScalarTypeExtensionNode $node): ?ScalarTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param SchemaDefinitionNode $node
     * @return SchemaDefinitionNode|null
     */
    protected function enterSchemaDefinition(SchemaDefinitionNode $node): ?SchemaDefinitionNode
    {
        return $node;
    }

    /**
     * @param SchemaDefinitionNode $node
     * @return SchemaDefinitionNode|null
     */
    protected function leaveSchemaDefinition(SchemaDefinitionNode $node): ?SchemaDefinitionNode
    {
        return $node;
    }

    /**
     * @param SelectionSetNode $node
     * @return SelectionSetNode|null
     */
    protected function enterSelectionSet(SelectionSetNode $node): ?SelectionSetNode
    {
        return $node;
    }

    /**
     * @param SelectionSetNode $node
     * @return SelectionSetNode|null
     */
    protected function leaveSelectionSet(SelectionSetNode $node): ?SelectionSetNode
    {
        return $node;
    }

    /**
     * @param StringValueNode $node
     * @return StringValueNode|null
     */
    protected function enterStringValue(StringValueNode $node): ?StringValueNode
    {
        return $node;
    }

    /**
     * @param StringValueNode $node
     * @return StringValueNode|null
     */
    protected function leaveStringValue(StringValueNode $node): ?StringValueNode
    {
        return $node;
    }

    /**
     * @param UnionTypeDefinitionNode $node
     * @return UnionTypeDefinitionNode|null
     */
    protected function enterUnionTypeDefinition(UnionTypeDefinitionNode $node): ?UnionTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param UnionTypeDefinitionNode $node
     * @return UnionTypeDefinitionNode|null
     */
    protected function leaveUnionTypeDefinition(UnionTypeDefinitionNode $node): ?UnionTypeDefinitionNode
    {
        return $node;
    }

    /**
     * @param UnionTypeExtensionNode $node
     * @return UnionTypeExtensionNode|null
     */
    protected function enterUnionTypeExtension(UnionTypeExtensionNode $node): ?UnionTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param UnionTypeExtensionNode $node
     * @return UnionTypeExtensionNode|null
     */
    protected function leaveUnionTypeExtension(UnionTypeExtensionNode $node): ?UnionTypeExtensionNode
    {
        return $node;
    }

    /**
     * @param VariableDefinitionNode $node
     * @return VariableDefinitionNode|null
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): ?VariableDefinitionNode
    {
        return $node;
    }

    /**
     * @param VariableDefinitionNode $node
     * @return VariableDefinitionNode|null
     */
    protected function leaveVariableDefinition(VariableDefinitionNode $node): ?VariableDefinitionNode
    {
        return $node;
    }

    /**
     * @param VariableNode $node
     * @return VariableNode|null
     */
    protected function enterVariable(VariableNode $node): ?VariableNode
    {
        return $node;
    }

    /**
     * @param VariableNode $node
     * @return VariableNode|null
     */
    protected function leaveVariable(VariableNode $node): ?VariableNode
    {
        return $node;
    }
}
