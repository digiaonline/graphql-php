<?php

namespace Digia\GraphQL\Test\Functional;

use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\Test\readFileContents;

function starWarsSchema()
{
    $source = readFileContents(__DIR__ . '/starWars.graphqls');

    /** @noinspection PhpUnhandledExceptionInspection */
    return buildSchema($source, [
        'Query' => [
            'hero' => function ($rootValue, $arguments) {
                return getHero($arguments['episode'] ?? null);
            },
        ],
    ]);
}
