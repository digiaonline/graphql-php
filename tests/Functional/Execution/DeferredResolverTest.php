<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use React\Promise\Promise;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;

class DirectorBuffer
{
    protected static $ids = [];

    protected static $data = [];

    protected static $dataLoaded = false;

    public static function add(int $id)
    {
        self::$ids[] = $id;
    }

    public static function get(int $id)
    {
        return self::$data[$id];
    }

    public static function loadData(): void
    {
        if (self::$dataLoaded) {
            return;
        }

        self::$data = [
            42 => [
                'name' => 'George Lucas',
            ],
            43 => [
                'name' => 'Irvin Kershner'
            ]
        ];

        self::$dataLoaded = true;
    }
}

class DeferredResolverTest extends ResolveTest
{

    public function testUsingFieldDeferredResolver()
    {
        $movies = [
            [
                'title'      => 'Episode IV – A New Hope',
                'directorId' => 42
            ],
            [
                'title'      => 'Episode V – The Empire Strikes Back',
                'directorId' => 43
            ]
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $directorType = newObjectType([
            'name'        => 'Director',
            'description' => 'Director of the movie',
            'fields'      => [
                'name' => [
                    'type' => stringType(),
                ]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $movieType = newObjectType([
            'name'        => 'Movie',
            'description' => 'A movie',
            'fields'      => [
                'title'    => ['type' => stringType()],
                'director' => [
                    'type'    => $directorType,
                    'resolve' => function (array $movie) {
                        DirectorBuffer::add($movie['directorId']);

                        return new Promise(function (callable $resolve) use ($movie) {
                            DirectorBuffer::loadData();
                            $resolve(DirectorBuffer::get($movie['directorId']));
                        });
                    }
                ]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'        => 'Query',
                'description' => '',
                'fields'      => [
                    'movies' => [
                        'type'    => newList($movieType),
                        'resolve' => function ($source, $args) use ($movies) {
                            return $movies;
                        }
                    ]
                ]
            ])
        ]);

        $query = '
            {
                movies {
                    title
                    director {
                        name
                    }
                }
            }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql($schema, $query, $movies);

        $this->assertSame([
            'data' => [
                'movies' => [
                    [
                        'title'    => 'Episode IV – A New Hope',
                        'director' => [
                            'name' => 'George Lucas'
                        ]
                    ],
                    [
                        'title'    => 'Episode V – The Empire Strikes Back',
                        'director' => [
                            'name' => 'Irvin Kershner'
                        ]
                    ]
                ]
            ]
        ], $result);
    }
}
