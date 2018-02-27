<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\GraphQLError;

/**
 * @param bool   $condition
 * @param string $message
 * @throws GraphQLError
 */
function invariant(bool $condition, string $message)
{
    if (!$condition) {
        throw new GraphQLError($message);
    }
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
