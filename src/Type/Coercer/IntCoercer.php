<?php

namespace Digia\GraphQL\Type\Coercer;

use Digia\GraphQL\Error\InvalidTypeException;

class IntCoercer extends AbstractCoercer
{

    /**
     * @inheritdoc
     */
    public function coerce($value)
    {
        if ($value === '') {
            throw new InvalidTypeException('Int cannot represent non 32-bit signed integer value: (empty string)');
        }

        if (\is_bool($value)) {
            $value = (int)$value;
        }

        if (!\is_int($value) || $value > PHP_INT_MAX || $value < PHP_INT_MIN) {
            throw new InvalidTypeException(
                \sprintf('Int cannot represent non 32-bit signed integer value: %s',
                    $value)
            );
        }

        $intValue = (int)$value;
        $floatValue = (float)$value;

        if ($floatValue != $intValue || \floor($floatValue) !== $floatValue) {
            throw new InvalidTypeException(\sprintf('Int cannot represent non-integer value: %s',
                $value));
        }

        return $intValue;
    }
}
