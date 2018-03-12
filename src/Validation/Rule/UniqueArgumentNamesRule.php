<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use function Digia\GraphQL\Validation\duplicateArgumentMessage;

/**
 * Unique argument names
 *
 * A GraphQL field or directive is only valid if all supplied arguments are
 * uniquely named.
 */
class UniqueArgumentNamesRule extends AbstractRule
{
    /**
     * @var string[]
     */
    protected $knownArgumentNames = [];

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof FieldNode || $node instanceof DirectiveNode) {
            $this->knownArgumentNames = [];
        }

        if ($node instanceof ArgumentNode) {
            $argumentName = $node->getNameValue();

            if (isset($this->knownArgumentNames[$argumentName])) {
                $this->validationContext->reportError(
                    new ValidationException(
                        duplicateArgumentMessage($argumentName),
                        [$this->knownArgumentNames[$argumentName], $node->getName()]
                    )
                );
            } else {
                $this->knownArgumentNames[$argumentName] = $node->getName();
            }

            return null;
        }

        return $node;
    }
}
