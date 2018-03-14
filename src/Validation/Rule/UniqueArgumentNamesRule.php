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
    protected function enterField(FieldNode $node): ?NodeInterface
    {
        $this->knownArgumentNames = [];

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function enterDirective(DirectiveNode $node): ?NodeInterface
    {
        $this->knownArgumentNames = [];

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function enterArgument(ArgumentNode $node): ?NodeInterface
    {
        $argumentName = $node->getNameValue();

        if (isset($this->knownArgumentNames[$argumentName])) {
            $this->context->reportError(
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
}
