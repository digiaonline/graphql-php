<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
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
    protected function enterOperationDefinition(OperationDefinitionNode $node): ?NodeInterface
    {
        return null; // Fragments cannot be defined inside operation definitions.
    }

    /**
     * @inheritdoc
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): ?NodeInterface
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

        return null;
    }
}
