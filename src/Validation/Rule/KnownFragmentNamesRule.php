<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

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
            $fragment = $this->context->getFragment($fragmentName);

            if (null === $fragment) {
                $this->context->reportError(
                    new ValidationException(unknownFragmentMessage($fragmentName), [$node->getName()])
                );
            }
        }

        return $node;
    }
}
