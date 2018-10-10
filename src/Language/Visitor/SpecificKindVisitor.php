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
use Digia\GraphQL\Language\Node\SchemaExtensionNode;
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
    public function enterNode(NodeInterface $node): VisitorResult
    {
        $enterMethod = 'enter' . $node->getKind();
        return $this->{$enterMethod}($node);
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): VisitorResult
    {
        $leaveMethod = 'leave' . $node->getKind();
        return $this->{$leaveMethod}($node);
    }

    /**
     * @param ArgumentNode $node
     * @return VisitorResult
     */
    protected function enterArgument(ArgumentNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ArgumentNode $node
     * @return VisitorResult
     */
    protected function leaveArgument(ArgumentNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param BooleanValueNode $node
     * @return VisitorResult
     */
    protected function enterBooleanValue(BooleanValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param BooleanValueNode $node
     * @return VisitorResult
     */
    protected function leaveBooleanValue(BooleanValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param DirectiveDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterDirectiveDefinition(DirectiveDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param DirectiveDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveDirectiveDefinition(DirectiveDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param DirectiveNode $node
     * @return VisitorResult
     */
    protected function enterDirective(DirectiveNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param DirectiveNode $node
     * @return VisitorResult
     */
    protected function leaveDirective(DirectiveNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param DocumentNode $node
     * @return VisitorResult
     */
    protected function enterDocument(DocumentNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param DocumentNode $node
     * @return VisitorResult
     */
    protected function leaveDocument(DocumentNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterEnumTypeDefinition(EnumTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveEnumTypeDefinition(EnumTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function enterEnumTypeExtension(EnumTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function leaveEnumTypeExtension(EnumTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumValueDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterEnumValueDefinition(EnumValueDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumValueDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveEnumValueDefinition(EnumValueDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumValueNode $node
     * @return VisitorResult
     */
    protected function enterEnumValue(EnumValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param EnumValueNode $node
     * @return VisitorResult
     */
    protected function leaveEnumValue(EnumValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FieldDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterFieldDefinition(FieldDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FieldDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveFieldDefinition(FieldDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FieldNode $node
     * @return VisitorResult
     */
    protected function enterField(FieldNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FieldNode $node
     * @return VisitorResult
     */
    protected function leaveField(FieldNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FloatValueNode $node
     * @return VisitorResult
     */
    protected function enterFloatValue(FloatValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FloatValueNode $node
     * @return VisitorResult
     */
    protected function leaveFloatValue(FloatValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FragmentDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FragmentDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveFragmentDefinition(FragmentDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FragmentSpreadNode $node
     * @return VisitorResult
     */
    protected function enterFragmentSpread(FragmentSpreadNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param FragmentSpreadNode $node
     * @return VisitorResult
     */
    protected function leaveFragmentSpread(FragmentSpreadNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InlineFragmentNode $node
     * @return VisitorResult
     */
    protected function enterInlineFragment(InlineFragmentNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InlineFragmentNode $node
     * @return VisitorResult
     */
    protected function leaveInlineFragment(InlineFragmentNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InputObjectTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterInputObjectTypeDefinition(InputObjectTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InputObjectTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveInputObjectTypeDefinition(InputObjectTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InputObjectTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function enterInputObjectTypeExtension(InputObjectTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InputObjectTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function leaveInputObjectTypeExtension(InputObjectTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InputValueDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterInputValueDefinition(InputValueDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InputValueDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveInputValueDefinition(InputValueDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param IntValueNode $node
     * @return VisitorResult
     */
    protected function enterIntValue(IntValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param IntValueNode $node
     * @return VisitorResult
     */
    protected function leaveIntValue(IntValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InterfaceTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterInterfaceTypeDefinition(InterfaceTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InterfaceTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveInterfaceTypeDefinition(InterfaceTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InterfaceTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function enterInterfaceTypeExtension(InterfaceTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param InterfaceTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function leaveInterfaceTypeExtension(InterfaceTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ListTypeNode $node
     * @return VisitorResult
     */
    protected function enterListType(ListTypeNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ListTypeNode $node
     * @return VisitorResult
     */
    protected function leaveListType(ListTypeNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ListValueNode $node
     * @return VisitorResult
     */
    protected function enterListValue(ListValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ListValueNode $node
     * @return VisitorResult
     */
    protected function leaveListValue(ListValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NamedTypeNode $node
     * @return VisitorResult
     */
    protected function enterNamedType(NamedTypeNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NamedTypeNode $node
     * @return VisitorResult
     */
    protected function leaveNamedType(NamedTypeNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NameNode $node
     * @return VisitorResult
     */
    protected function enterName(NameNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NameNode $node
     * @return VisitorResult
     */
    protected function leaveName(NameNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NonNullTypeNode $node
     * @return VisitorResult
     */
    protected function enterNonNullType(NonNullTypeNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NonNullTypeNode $node
     * @return VisitorResult
     */
    protected function leaveNonNullType(NonNullTypeNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NullValueNode $node
     * @return VisitorResult
     */
    protected function enterNullValue(NullValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param NullValueNode $node
     * @return VisitorResult
     */
    protected function leaveNullValue(NullValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectFieldNode $node
     * @return VisitorResult
     */
    protected function enterObjectField(ObjectFieldNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectFieldNode $node
     * @return VisitorResult
     */
    protected function leaveObjectField(ObjectFieldNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterObjectTypeDefinition(ObjectTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveObjectTypeDefinition(ObjectTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function enterObjectTypeExtension(ObjectTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function leaveObjectTypeExtension(ObjectTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectValueNode $node
     * @return VisitorResult
     */
    protected function enterObjectValue(ObjectValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ObjectValueNode $node
     * @return VisitorResult
     */
    protected function leaveObjectValue(ObjectValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param OperationDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param OperationDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param OperationTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterOperationTypeDefinition(OperationTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param OperationTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveOperationTypeDefinition(OperationTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterScalarTypeDefinition(ScalarTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveScalarTypeDefinition(ScalarTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ScalarTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function enterScalarTypeExtension(ScalarTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param ScalarTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function leaveScalarTypeExtension(ScalarTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param SchemaDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterSchemaDefinition(SchemaDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param SchemaDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveSchemaDefinition(SchemaDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param SchemaExtensionNode $node
     * @return VisitorResult
     */
    protected function enterSchemaExtension(SchemaExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param SchemaExtensionNode $node
     * @return VisitorResult
     */
    protected function leaveSchemaExtension(SchemaExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param SelectionSetNode $node
     * @return VisitorResult
     */
    protected function enterSelectionSet(SelectionSetNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param SelectionSetNode $node
     * @return VisitorResult
     */
    protected function leaveSelectionSet(SelectionSetNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param StringValueNode $node
     * @return VisitorResult
     */
    protected function enterStringValue(StringValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param StringValueNode $node
     * @return VisitorResult
     */
    protected function leaveStringValue(StringValueNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param UnionTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterUnionTypeDefinition(UnionTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param UnionTypeDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveUnionTypeDefinition(UnionTypeDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param UnionTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function enterUnionTypeExtension(UnionTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param UnionTypeExtensionNode $node
     * @return VisitorResult
     */
    protected function leaveUnionTypeExtension(UnionTypeExtensionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param VariableDefinitionNode $node
     * @return VisitorResult
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param VariableDefinitionNode $node
     * @return VisitorResult
     */
    protected function leaveVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param VariableNode $node
     * @return VisitorResult
     */
    protected function enterVariable(VariableNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }

    /**
     * @param VariableNode $node
     * @return VisitorResult
     */
    protected function leaveVariable(VariableNode $node): VisitorResult
    {
        return new VisitorResult($node);
    }
}
