<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

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
