<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\ValidationException;
use function Digia\GraphQL\Validation\duplicateVariableMessage;

/**
 * Unique variable names
 *
 * A GraphQL operation is only valid if all its variables are uniquely named.
 */
class UniqueVariableNamesRule extends AbstractRule
{
    /**
     * @var NameNode[]
     */
    protected $knownVariableNames = [];

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $this->knownVariableNames = [];

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        $variable     = $node->getVariable();
        $variableName = $variable->getNameValue();

        if (isset($this->knownVariableNames[$variableName])) {
            $this->context->reportError(
                new ValidationException(
                    duplicateVariableMessage($variableName),
                    [$this->knownVariableNames[$variableName], $variable->getName()]
                )
            );
        } else {
            $this->knownVariableNames[$variableName] = $variable->getName();
        }

        return new VisitorResult($node);
    }
}
