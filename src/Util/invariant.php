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
