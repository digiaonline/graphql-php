<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\InvariantException;

/**
 * @param bool   $condition
 * @param string $message
 * @throws InvariantException
 */
function invariant(bool $condition, string $message)
{
    if (!$condition) {
        throw new InvariantException($message);
    }
}

/**
 * Given `[A, B, C]` returns `'A, B or C'`.
 *
 * @param array $items
 * @return string
 */
function orList(array $items): string
{
    static $MAX_LENGTH = 5;

    $selected = \array_slice($items, 0, $MAX_LENGTH);
    $count    = \count($selected);
    $index    = 0;

    return $count === 1
        ? $selected[0]
        : \array_reduce($selected, function ($list, $item) use ($count, &$index) {
            $list .= ($index > 0 && $index < ($count - 1) ? ', ' : '') . ($index === ($count - 1) ? ' or ' : '') .
                $item;
            $index++;
            return $list;
        }, '');
}

/**
 * Given an invalid input string and a list of valid options, returns a filtered
 * list of valid options sorted based on their similarity with the input.
 *
 * @param string $input
 * @param array  $options
 * @return array
 */
function suggestionList(string $input, array $options): array
{
    $optionsByDistance = [];
    $oLength = \count($options);
    $inputThreshold = \strlen($input) / 2;

    /** @noinspection ForeachInvariantsInspection */
    for ($i = 0; $i < $oLength; $i++) {
        // Comparison must be case-insenstive.
        $distance = \levenshtein(\strtolower($input), \strtolower($options[$i]));
        $threshold = \max($inputThreshold, \strlen($options[$i]) / 2, 1);
        if ($distance <= $threshold) {
            $optionsByDistance[$options[$i]] = $distance;
        }
    }

    $result = \array_keys($optionsByDistance);

    \usort($result, function ($a, $b) use ($optionsByDistance) {
        return $optionsByDistance[$a] - $optionsByDistance[$b];
    });

    return $result;
}

/**
 * Given `[A, B, C]` returns `'"A", "B" or "C"'`.
 *
 * @param array $items
 * @return string
 */
function quotedOrList(array $items): string
{
    return orList(array_map(function ($item) {
        return '"' . $item . '"';
    }, $items));
}



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
function keyValueMap(array $array, callable $keyFn, callable $valFn): array
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
