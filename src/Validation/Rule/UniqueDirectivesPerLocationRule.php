<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\DirectivesAwareInterface;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\ValidationException;
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
    public function enterNode(NodeInterface $node): VisitorResult
    {
        if ($node instanceof DirectivesAwareInterface) {
            $directives      = $node->getDirectives();
            $knownDirectives = [];

            foreach ($directives as $directive) {
                $directiveName = $directive->getNameValue();

                if (isset($knownDirectives[$directiveName])) {
                    $this->context->reportError(
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

        return new VisitorResult($node);
    }
}
