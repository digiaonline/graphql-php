<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use function Digia\GraphQL\Validation\anonymousOperationNotAloneMessage;

/**
 * Lone anonymous operation
 *
 * A GraphQL document is only valid when it contains an anonymous operation
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
    protected function enterDocument(DocumentNode $node): ?NodeInterface
    {
        $this->operationCount = \count(\array_filter($node->getDefinitions(), function ($definition) {
            return $definition instanceof OperationDefinitionNode;
        }));

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): ?NodeInterface
    {
        if (null === $node->getName() && $this->operationCount > 1) {
            $this->validationContext->reportError(
                new ValidationException(anonymousOperationNotAloneMessage(), [$node])
            );
        }

        return $node;
    }
}
