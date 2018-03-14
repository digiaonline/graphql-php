<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use function Digia\GraphQL\Validation\duplicateInputFieldMessage;

/**
 * Unique input field names
 *
 * A GraphQL input object value is only valid if all supplied fields are
 * uniquely named.
 */
class UniqueInputFieldNamesRule extends AbstractRule
{
    /**
     * @var array[]git
     */
    protected $knownInputNamesStack = [];

    /**
     * @var string[]
     */
    protected $knownInputNames = [];

    /**
     * @inheritdoc
     */
    protected function enterObjectValue(ObjectValueNode $node): ?NodeInterface
    {
        $this->knownInputNamesStack[] = $this->knownInputNames;
        $this->knownInputNames = [];

        return $node;
    }

    protected function enterObjectField(ObjectFieldNode $node): ?NodeInterface
    {
        $fieldName = $node->getNameValue();

        if (isset($this->knownInputNames[$fieldName])) {
            $this->context->reportError(
                new ValidationException(
                    duplicateInputFieldMessage($fieldName),
                    [$this->knownInputNames[$fieldName], $node->getName()]
                )
            );
        } else {
            $this->knownInputNames[$fieldName] = $node->getName();
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function leaveObjectValue(ObjectValueNode $node): ?NodeInterface
    {
        $this->knownInputNames = \array_pop($this->knownInputNamesStack);

        return $node;
    }
}
