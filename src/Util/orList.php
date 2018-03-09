<?php

namespace Digia\GraphQL\Util;

const MAX_LENGTH = 5;

/**
 * Given `[A, B, C]` returns `'A, B or C'`.
 *
 * @param array $items
 * @return string
 */
function orList(array $items): string
{
    $selected = \array_slice($items, 0, MAX_LENGTH);
    $allButLast = \array_slice($selected, 0, -1);
    return implode(', ', $allButLast) . ' or ' . $selected[\count($selected) - 1];
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
