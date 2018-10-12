<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\ValidationException;
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
    public function enterFragmentSpread(FragmentSpreadNode $node): VisitorResult
    {
        $fragmentName = $node->getNameValue();
        $fragment     = $this->context->getFragment($fragmentName);

        if (null === $fragment) {
            $this->context->reportError(
                new ValidationException(unknownFragmentMessage($fragmentName), [$node->getName()])
            );
        }

        return new VisitorResult($node);
    }
}
