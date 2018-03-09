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
