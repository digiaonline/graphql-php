<?php

namespace Digia\GraphQL\Type\Coercer;

use Digia\GraphQL\Error\InvalidTypeException;

class FloatCoercer extends AbstractCoercer
{
    /**
     * @inheritdoc
     */
    public function coerce($value)
    {
        if ($value === '') {
            throw new InvalidTypeException('Float cannot represent non numeric value: (empty string)');
        }

        if (\is_numeric($value) || \is_bool($value)) {
            return (float)$value;
        }

        throw new InvalidTypeException(\sprintf('Float cannot represent non numeric value: %s', $value));
    }
}
