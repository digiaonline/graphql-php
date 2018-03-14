<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\DirectiveLocationEnum;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumTypeExtensionNode;
use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeExtensionNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Language\Node\UnionTypeExtensionNode;
use Digia\GraphQL\Language\Visitor\AcceptsVisitorsInterface;
use Digia\GraphQL\Type\Definition\Directive;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Validation\misplacedDirectiveMessage;
use function Digia\GraphQL\Validation\unknownDirectiveMessage;

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
    protected function enterDirective(DirectiveNode $node): ?NodeInterface
    {
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

        if (null !== $location && !\in_array($location, $directiveDefinition->getLocations())) {
            $this->validationContext->reportError(
                new ValidationException(misplacedDirectiveMessage((string)$node, $location), [$node])
            );
        }

        return $node;
    }

    /**
     * @param NodeInterface|AcceptsVisitorsInterface $node
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
