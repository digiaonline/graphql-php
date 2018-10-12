<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\ValidationException;
use function Digia\GraphQL\Validation\duplicateFragmentMessage;

/**
 * Unique fragment names
 *
 * A GraphQL document is only valid if all defined fragments have unique names.
 */
class UniqueFragmentNamesRule extends AbstractRule
{
    /**
     * @var NameNode[]
     */
    protected $knownFragmentNames = [];

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        return new VisitorResult(null); // Fragments cannot be defined inside operation definitions.
    }

    /**
     * @inheritdoc
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): VisitorResult
    {
        $fragmentName = $node->getNameValue();

        if (isset($this->knownFragmentNames[$fragmentName])) {
            $this->context->reportError(
                new ValidationException(
                    duplicateFragmentMessage($fragmentName),
                    [$this->knownFragmentNames[$fragmentName], $node->getName()]
                )
            );
        } else {
            $this->knownFragmentNames[$fragmentName] = $node->getName();
        }

        return new VisitorResult(null);
    }
}
