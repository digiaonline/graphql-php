<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Validation\unknownFragmentMessage;

/**
 * Known fragment names
 *
 * A GraphQL document is only valid if all `...Fragment` fragment spreads refer
 * to fragments defined in the same document.
 */
class KnownFragmentNamesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof FragmentSpreadNode) {
            $fragmentName = $node->getNameValue();
            $fragment     = $this->validationContext->getFragment($fragmentName);

            if (null === $fragment) {
                $this->validationContext->reportError(
                    new ValidationException(unknownFragmentMessage($fragmentName), [$node->getName()])
                );
            }
        }

        return $node;
    }
}
