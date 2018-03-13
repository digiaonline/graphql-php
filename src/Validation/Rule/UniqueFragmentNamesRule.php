<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
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
     * @var string[]
     */
    protected $knownFragmentNames = [];

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            // Fragments cannot be defined inside operation definitions.
            return null;
        }

        if ($node instanceof FragmentDefinitionNode) {
            $fragmentName = $node->getNameValue();

            if (isset($this->knownFragmentNames[$fragmentName])) {
                $this->validationContext->reportError(
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

        return $node;
    }
}
