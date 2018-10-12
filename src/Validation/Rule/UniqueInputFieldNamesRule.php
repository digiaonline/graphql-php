<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\ObjectFieldNode;
use Digia\GraphQL\Language\Node\ObjectValueNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\ValidationException;
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
     * @var array[]
     */
    protected $knownInputNamesStack = [];

    /**
     * @var NameNode[]
     */
    protected $knownInputNames = [];

    /**
     * @inheritdoc
     */
    protected function enterObjectValue(ObjectValueNode $node): VisitorResult
    {
        $this->knownInputNamesStack[] = $this->knownInputNames;
        $this->knownInputNames        = [];

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function enterObjectField(ObjectFieldNode $node): VisitorResult
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

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function leaveObjectValue(ObjectValueNode $node): VisitorResult
    {
        $this->knownInputNames = \array_pop($this->knownInputNamesStack);

        return new VisitorResult($node);
    }
}
