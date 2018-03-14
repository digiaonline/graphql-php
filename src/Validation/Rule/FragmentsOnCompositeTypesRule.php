<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use function Digia\GraphQL\Util\typeFromAST;
use function Digia\GraphQL\Validation\fragmentOnNonCompositeMessage;
use function Digia\GraphQL\Validation\inlineFragmentOnNonCompositeMessage;


/**
 * Fragments on composite type
 *
 * Fragments use a type condition to determine if they apply, since fragments
 * can only be spread into a composite type (object, interface, or union), the
 * type condition must also be a composite type.
 */
class FragmentsOnCompositeTypesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): ?NodeInterface
    {
        $this->validateFragementNode($node, function (FragmentDefinitionNode $node) {
            return fragmentOnNonCompositeMessage((string)$node, (string)$node->getTypeCondition());
        });

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function enterInlineFragment(InlineFragmentNode $node): ?NodeInterface
    {
        $this->validateFragementNode($node, function (InlineFragmentNode $node) {
            return inlineFragmentOnNonCompositeMessage((string)$node->getTypeCondition());
        });

        return $node;
    }


    /**
     * @param NodeInterface|InlineFragmentNode|FragmentDefinitionNode $node
     * @param callable                                                $errorMessageFunction
     * @throws \Exception
     * @throws \TypeError
     */
    protected function validateFragementNode(NodeInterface $node, callable $errorMessageFunction)
    {
        $typeCondition = $node->getTypeCondition();

        if (null !== $typeCondition) {
            $type = typeFromAST($this->validationContext->getSchema(), $typeCondition);

            if (null !== $type && !($type instanceof CompositeTypeInterface)) {
                $this->validationContext->reportError(
                    new ValidationException($errorMessageFunction($node),
                    [$typeCondition]
                    )
                );
            }
        }
    }
}
