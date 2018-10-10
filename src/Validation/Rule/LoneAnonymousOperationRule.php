<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
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
    protected function enterDocument(DocumentNode $node): VisitorResult
    {
        $this->operationCount = \count(\array_filter($node->getDefinitions(), function ($definition) {
            return $definition instanceof OperationDefinitionNode;
        }));

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        if (null === $node->getName() && $this->operationCount > 1) {
            $this->context->reportError(
                new ValidationException(anonymousOperationNotAloneMessage(), [$node])
            );
        }

        return new VisitorResult($node);
    }
}
