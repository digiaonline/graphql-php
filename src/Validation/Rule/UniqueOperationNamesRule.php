<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use function Digia\GraphQL\Validation\duplicateOperationMessage;

/**
 * Unique operation names
 *
 * A GraphQL document is only valid if all defined operations have unique names.
 */
class UniqueOperationNamesRule extends AbstractRule
{
    /**
     * @var string[]
     */
    protected $knownOperationNames = [];

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $operationName = $node->getNameValue();

            if (null !== $operationName) {
                if (isset($this->knownOperationNames[$operationName])) {
                    $this->validationContext->reportError(
                        new ValidationException(
                            duplicateOperationMessage($operationName),
                            [$this->knownOperationNames[$operationName], $node->getName()]
                        )
                    );
                } else {
                    $this->knownOperationNames[$operationName] = $node->getName();
                }
            }
        }

        return $node;
    }
}
