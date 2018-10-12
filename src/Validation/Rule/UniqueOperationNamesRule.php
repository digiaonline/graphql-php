<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\ValidationException;
use function Digia\GraphQL\Validation\duplicateOperationMessage;

/**
 * Unique operation names
 *
 * A GraphQL document is only valid if all defined operations have unique names.
 */
class UniqueOperationNamesRule extends AbstractRule
{
    /**
     * @var NameNode[]
     */
    protected $knownOperationNames = [];

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $operationName = $node->getNameValue();

        if (null !== $operationName) {
            if (isset($this->knownOperationNames[$operationName])) {
                $this->context->reportError(
                    new ValidationException(
                        duplicateOperationMessage($operationName),
                        [$this->knownOperationNames[$operationName], $node->getName()]
                    )
                );
            } else {
                $this->knownOperationNames[$operationName] = $node->getName();
            }
        }

        return new VisitorResult($node);
    }
}
