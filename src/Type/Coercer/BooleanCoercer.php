<?php

namespace Digia\GraphQL\Type\Coercer;

use Digia\GraphQL\Error\InvalidTypeException;

class BooleanCoercer extends AbstractCoercer
{

    /**
     * @inheritdoc
     */
    public function coerce($value)
    {
        if (!\is_scalar($value)) {
            throw new InvalidTypeException(\sprintf('Boolean cannot represent a non-scalar value: %s',
                $value));
        }

        return (bool)$value;
    }
}
