<?php

namespace Digia\GraphQL\Util;

/**
 * @param array    $array
 * @param callable $fn
 * @return bool
 */
function arrayEvery(array $array, callable $fn): bool
{
    return array_reduce($array, function ($result, $value) use ($fn) {
        return $result && $fn($value);
    }, true);
}

/**
 * @param array    $array
 * @param callable $fn
 * @return mixed
 */
function arraySome(array $array, callable $fn)
{
    return array_reduce($array, function ($result, $value) use ($fn) {
        return $result || $fn($value);
    });
}

/**
 * @param array    $array
 * @param callable $predicate
 * @return mixed|null
 */
function find(array $array, callable $predicate)
{
    foreach ($array as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }

    return null;
}

/**
 * @param array    $array
 * @param callable $keyFn
 * @return array
 */
function keyMap(array $array, callable $keyFn): array
{
    return array_reduce($array, function ($map, $item) use ($keyFn) {
        $map[$keyFn($item)] = $item;
        return $map;
    }, []);
}

/**
 * @param array    $array
 * @param callable $keyFn
 * @param callable $valFn
 * @return array
 */
function keyValMap(array $array, callable $keyFn, callable $valFn): array
{
    return array_reduce($array, function ($map, $item) use ($keyFn, $valFn) {
        $map[$keyFn($item)] = $valFn($item);
        return $map;
    }, []);
}

/**
 * @param $value
 * @return string
 */
function toString($value): string
{
    if (\is_object($value) && method_exists($value, '__toString')) {
        return $value;
    }
    if (\is_object($value)) {
        return 'Object';
    }
    if (\is_array($value)) {
        return 'Array';
    }
    if (\is_callable($value)) {
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
    if (\is_string($value)) {
        return "\"{$value}\"";
    }
    if (is_scalar($value)) {
        return (string)$value;
    }
    return \gettype($value);
}

/**
 * @param $value
 * @return string
 */
function jsonEncode($value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE);
}

/**
 * @param string $path
 * @return string
 */
function readFile(string $path): string
{
    return mb_convert_encoding(file_get_contents($path), 'UTF-8');
}
