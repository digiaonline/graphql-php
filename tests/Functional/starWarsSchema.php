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
            'hero' => function ($value, $arguments) {
                return getHero($arguments['episode'] ?? null);
            },
            'human' => function ($value, $arguments) {
                return getHuman($arguments['id']);
            },
            'droid' => function ($value, $arguments) {
                return getDroid($arguments['id']);
            },
        ],
        'Human' => [
            'friends' => function ($human) {
                return getFriends($human);
            },
            'secretBackstory' => function () {
                throw new \Exception('secretBackstory is secret.');
            },
        ],
        'Droid' => [
            'friends' => function ($droid) {
                return getFriends($droid);
            },
            'secretBackstory' => function () {
                throw new \Exception('secretBackstory is secret.');
            },
        ],
    ]);
}
