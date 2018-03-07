<?php

namespace Digia\GraphQL\Util;

use const Digia\GraphQL\Type\MAX_INT;
use const Digia\GraphQL\Type\MIN_INT;

/**
 * @param $value
 * @return bool
 * @throws \TypeError
 */
function coerceBoolean($value): bool
{
    if (!is_scalar($value)) {
        throw new \TypeError(sprintf('Boolean cannot represent a non-scalar value: %s', $value));
    }

    return (bool)$value;
}

/**
 * @param $value
 * @return float
 * @throws \TypeError
 */
function coerceFloat($value): float
{
    if ($value === '') {
        throw new \TypeError('Float cannot represent non numeric value: (empty string)');
    }

    if (is_numeric($value) || \is_bool($value)) {
        return (float)$value;
    }

    throw new \TypeError(sprintf('Float cannot represent non numeric value: %s', $value));
}

/**
 * @param $value
 * @return int
 * @throws \TypeError
 */
function coerceInt($value)
{
    if ($value === '') {
        throw new \TypeError('Int cannot represent non 32-bit signed integer value: (empty string)');
    }

    if (\is_bool($value)) {
        $value = (int)$value;
    }

    if (!\is_int($value) || $value > MAX_INT || $value < MIN_INT) {
        throw new \TypeError(sprintf('Int cannot represent non 32-bit signed integer value: %s', $value));
    }

    $intValue   = (int)$value;
    $floatValue = (float)$value;

    if ($floatValue != $intValue || floor($floatValue) !== $floatValue) {
        throw new \TypeError(sprintf('Int cannot represent non-integer value: %s', $value));
    }

    return $intValue;
}

/**
 * @param $value
 * @return string
 * @throws \TypeError
 */
function coerceString($value): string
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

    if (!is_scalar($value)) {
        throw new \TypeError('String cannot represent a non-scalar value');
    }

    return (string)$value;
}
