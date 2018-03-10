<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;

/**
 * Lone anonymous operation
 *
 * A GraphQL document is only valid if when it contains an anonymous operation
 * (the query short-hand) that it contains only that one operation definition.
 */
class LoneAnonymousOperationRule extends AbstractRule
{
    /**
     * @var int
     */
    protected $operationCount = 0;

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof DocumentNode) {
            $this->operationCount = \count(array_filter($node->getDefinitions(), function ($definition) {
                return $definition instanceof OperationDefinitionNode;
            }));
        }

        if ($node instanceof OperationDefinitionNode) {
            if (null === $node->getName() && $this->operationCount > 1) {
                $this->context->reportError(
                    new ValidationException(anonymousOperationNotAloneMessage(), [$node])
                );
            }
        }

        return $node;
    }
}
