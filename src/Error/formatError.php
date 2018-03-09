<?php

namespace Digia\GraphQL\Error;

use function Digia\GraphQL\Util\invariant;

/**
 * @param GraphQLError $error
 * @return array
 * @throws GraphQLError
 */
function formatError(GraphQLError $error): array
{
    invariant(null !== $error, 'Received null error.');

    return array_merge($error->getExtensions() ?? [], [
        'message'   => $error->getMessage(),
        'locations' => $error->getLocations(),
        'path'      => $error->getPath(),
    ]);
}
