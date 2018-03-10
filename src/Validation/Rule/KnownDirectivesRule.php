<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\DirectiveLocationEnum;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;
use Digia\GraphQL\Language\AST\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\EnumTypeExtensionNode;
use Digia\GraphQL\Language\AST\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\InlineFragmentNode;
use Digia\GraphQL\Language\AST\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\AST\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\ScalarTypeExtensionNode;
use Digia\GraphQL\Language\AST\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\AST\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Language\AST\Node\UnionTypeExtensionNode;
use Digia\GraphQL\Language\AST\Visitor\AcceptVisitorTrait;
use Digia\GraphQL\Type\Definition\Directive;
use function Digia\GraphQL\Util\find;

/**
 * Known directives
 *
 * A GraphQL document is only valid if all `@directives` are known by the
 * schema and legally positioned.
 */
class KnownDirectivesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof DirectiveNode) {
            /** @var Directive $directiveDefinition */
            $directiveDefinition = find(
                $this->validationContext->getSchema()->getDirectives(),
                function (Directive $definition) use ($node) {
                    return $definition->getName() === $node->getNameValue();
                }
            );

            if (null == $directiveDefinition) {
                $this->validationContext->reportError(
                    new ValidationException(unknownDirectiveMessage((string)$node), [$node])
                );

                return $node;
            }

            $location = $this->getDirectiveLocationFromASTPath($node);

            if (null !== $location && !in_array($location, $directiveDefinition->getLocations())) {
                $this->validationContext->reportError(
                    new ValidationException(misplacedDirectiveMessage((string)$node, $location), [$node])
                );
            }
        }

        return $node;
    }

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @return string|null
     */
    protected function getDirectiveLocationFromASTPath(NodeInterface $node): ?string
    {
        /** @var NodeInterface $appliedTo */
        $appliedTo = $node->getAncestor();

        if ($appliedTo instanceof OperationDefinitionNode) {
            switch ($appliedTo->getOperation()) {
                case 'query':
                    return DirectiveLocationEnum::QUERY;
                case 'mutation':
                    return DirectiveLocationEnum::MUTATION;
                case 'subscription':
                    return DirectiveLocationEnum::SUBSCRIPTION;
                default:
                    return null;
            }
        }
        if ($appliedTo instanceof FieldNode) {
            return DirectiveLocationEnum::FIELD;
        }
        if ($appliedTo instanceof FragmentSpreadNode) {
            return DirectiveLocationEnum::FRAGMENT_SPREAD;
        }
        if ($appliedTo instanceof InlineFragmentNode) {
            return DirectiveLocationEnum::INLINE_FRAGMENT;
        }
        if ($appliedTo instanceof FragmentDefinitionNode) {
            return DirectiveLocationEnum::FRAGMENT_DEFINITION;
        }
        if ($appliedTo instanceof SchemaDefinitionNode) {
            return DirectiveLocationEnum::SCHEMA;
        }
        if ($appliedTo instanceof ScalarTypeDefinitionNode || $appliedTo instanceof ScalarTypeExtensionNode) {
            return DirectiveLocationEnum::SCALAR;
        }
        if ($appliedTo instanceof ObjectTypeDefinitionNode || $appliedTo instanceof ObjectTypeExtensionNode) {
            return DirectiveLocationEnum::OBJECT;
        }
        if ($appliedTo instanceof FieldDefinitionNode) {
            return DirectiveLocationEnum::FIELD_DEFINITION;
        }
        if ($appliedTo instanceof InterfaceTypeDefinitionNode || $appliedTo instanceof InterfaceTypeExtensionNode) {
            return DirectiveLocationEnum::INTERFACE;
        }
        if ($appliedTo instanceof UnionTypeDefinitionNode || $appliedTo instanceof UnionTypeExtensionNode) {
            return DirectiveLocationEnum::UNION;
        }
        if ($appliedTo instanceof EnumTypeDefinitionNode || $appliedTo instanceof EnumTypeExtensionNode) {
            return DirectiveLocationEnum::ENUM;
        }
        if ($appliedTo instanceof EnumValueDefinitionNode) {
            return DirectiveLocationEnum::ENUM_VALUE;
        }
        if ($appliedTo instanceof InputObjectTypeDefinitionNode || $appliedTo instanceof InputObjectTypeExtensionNode) {
            return DirectiveLocationEnum::INPUT_OBJECT;
        }
        if ($appliedTo instanceof InputValueDefinitionNode) {
            $parentNode = $node->getAncestor(2);
            return $parentNode instanceof InputObjectTypeDefinitionNode
                ? DirectiveLocationEnum::INPUT_FIELD_DEFINITION
                : DirectiveLocationEnum::ARGUMENT_DEFINITION;
        }

        return null;
    }
}
