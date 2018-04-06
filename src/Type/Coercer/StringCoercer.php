<?php

namespace Digia\GraphQL\Type\Coercer;

use Digia\GraphQL\Error\InvalidTypeException;

class StringCoercer extends AbstractCoercer
{

    /**
     * @inheritdoc
     */
    public function coerce($value)
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (!\is_scalar($value)) {
            throw new InvalidTypeException('String cannot represent a non-scalar value');
        }

        return (string)$value;
    }
}
