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
    $count    = \count($selected);
    $index    = 0;

    return $count === 1
        ? $selected[0]
        : array_reduce($selected, function ($list, $item) use ($count, &$index) {
            $list .= ($index > 0 && $index < ($count - 1) ? ', ' : '') . ($index === ($count - 1) ? ' or ' : '') .
                $item;
            $index++;
            return $list;
        }, '');
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
