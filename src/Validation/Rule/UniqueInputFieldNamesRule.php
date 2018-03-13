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
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof ObjectValueNode) {
            $this->knownInputNamesStack[] = $this->knownInputNames;
            $this->knownInputNames = [];
        }

        if ($node instanceof ObjectFieldNode) {
            $fieldName = $node->getNameValue();

            if (isset($this->knownInputNames[$fieldName])) {
                $this->validationContext->reportError(
                    new ValidationException(
                        duplicateInputFieldMessage($fieldName),
                        [$this->knownInputNames[$fieldName], $node->getName()]
                    )
                );
            } else {
                $this->knownInputNames[$fieldName] = $node->getName();
            }
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof ObjectValueNode) {
            $this->knownInputNames = \array_pop($this->knownInputNamesStack);
        }

        return $node;
    }
}
