<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DirectivesInterface;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Validation\duplicateDirectiveMessage;

/**
 * Unique directive names per location
 *
 * A GraphQL document is only valid if all directives at a given location
 * are uniquely named.
 */
class UniqueDirectivesPerLocationRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof DirectivesInterface) {
            $directives      = $node->getDirectives();
            $knownDirectives = [];

            foreach ($directives as $directive) {
                $directiveName = $directive->getNameValue();

                if (isset($knownDirectives[$directiveName])) {
                    $this->validationContext->reportError(
                        new ValidationException(
                            duplicateDirectiveMessage($directiveName),
                            [$knownDirectives[$directiveName], $directive]
                        )
                    );
                } else {
                    $knownDirectives[$directiveName] = $directive;
                }
            }
        }

        return $node;
    }
}
