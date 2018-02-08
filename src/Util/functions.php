<?php

namespace Digia\GraphQL\Util;

/**
 * @param string $className
 * @param mixed  $instance
 * @return mixed
 */
function instantiateIfNecessary(string $className, $instance)
{
    return $instance instanceof $className ? $instance : new $className($instance);
}

/**
 * @param bool   $condition
 * @param string $message
 * @throws \Exception
 */
function invariant(bool $condition, string $message)
{
    if (!$condition) {
        throw new \Exception($message);
    }
}

function toString($value)
{
    if (is_object($value) && method_exists($value, '__toString')) {
        return $value;
    }
    if (is_object($value)) {
        return 'instance of ' . get_class($value);
    }
    if (is_array($value)) {
        return json_encode($value);
    }
    if ($value === '') {
        return '(empty string)';
    }
    if ($value === null) {
        return 'null';
    }
    if ($value === true) {
        return 'true';
    }
    if ($value === false) {
        return 'false';
    }
    if (is_string($value)) {
        return "\"{$value}\"";
    }
    if (is_scalar($value)) {
        return (string) $value;
    }
    return gettype($value);
}
