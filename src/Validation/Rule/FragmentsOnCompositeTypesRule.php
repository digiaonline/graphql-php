<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\InlineFragmentNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\CompositeTypeInterface;
use function Digia\GraphQL\Util\typeFromAST;


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
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof InlineFragmentNode) {
            $this->validateFragementNode($node, function (NodeInterface $node) {
                /** @noinspection PhpUndefinedMethodInspection */
                return inlineFragmentOnNonCompositeMessage((string)$node->getTypeCondition());
            });
        }

        if ($node instanceof FragmentDefinitionNode) {
            $this->validateFragementNode($node, function (NodeInterface $node) {
                /** @noinspection PhpUndefinedMethodInspection */
                return fragmentOnNonCompositeMessage((string)$node, (string)$node->getTypeCondition());
            });
        }

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
                $this->validationContext->reportError(new ValidationException($errorMessageFunction($node), [$typeCondition]));
            }
        }
    }
}
