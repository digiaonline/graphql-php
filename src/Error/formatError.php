<?php

namespace Digia\GraphQL\Error;

use function Digia\GraphQL\Util\invariant;

/**
 * @param GraphQLException|null $error
 * @return array
 * @throws InvariantException
 */
function formatError(?GraphQLException $error): array
{
    invariant(null !== $error, 'Received null error.');

    return [
        'message'   => $error->getMessage(),
        'locations' => $error->getLocationsAsArray(),
        'path'      => $error->getPath(),
    ];
}
