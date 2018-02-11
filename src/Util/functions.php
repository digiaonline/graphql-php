<?php

namespace Digia\GraphQL\Util;

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

/**
 * @param string $className
 * @param array  $array
 * @return array
 */
function instantiateFromArray(string $className, array $array): array
{
    return array_map(function ($item) use ($className) {
        if ($item instanceof $className) {
            return $item;
        }

        return new $className($item);
    }, $array);
}

/**
 * @param string $className
 * @param array  $array
 * @return array
 */
function instantiateAssocFromArray(string $className, array $array, string $keyName = 'name'): array
{
    $objects = array_map(function ($item, $key) use ($className, $keyName) {
        return new $className(array_merge($item, [$keyName => $key]));
    }, $array, array_keys($array));

    $assoc = [];
    $keyGetter = 'get' . ucfirst($keyName);

    foreach ($objects as $object) {
        $assoc[$object->$keyGetter()] = $object;
    }

    return $assoc;
}

/**
 * @param $value
 * @return string
 */
function toString($value): string
{
    if (is_object($value) && method_exists($value, '__toString')) {
        return $value;
    }
    if (is_object($value)) {
        return 'Object';
    }
    if (is_array($value)) {
        return 'Array';
    }
    if (is_callable($value)) {
        return 'Function';
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
        return (string)$value;
    }
    return gettype($value);
}
