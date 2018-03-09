<?php

namespace Digia\GraphQL\Error;

use function Digia\GraphQL\Util\invariant;

/**
 * @param GraphQLException $error
 * @return array
 * @throws InvariantException
 */
function formatError(GraphQLException $error): array
{
    invariant(null !== $error, 'Received null error.');

    return array_merge($error->getExtensions() ?? [], [
        'message'   => $error->getMessage(),
        'locations' => $error->getLocationsAsArray(),
        'path'      => $error->getPath(),
    ]);
}
