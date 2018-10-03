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
    protected static $directorsIds = [];

    protected static $authors = [];

    public static function add(int $id)
    {
        self::$directorsIds[] = $id;
    }

    public static function get(int $id)
    {
        return self::$authors[$id];
    }

    public static function loadBuffered(): void
    {
        self::$authors = [
            42 => [
                'name' => 'George Lucas',
            ],
            43 => [
                'name' => 'Irvin Kershner'
            ]
        ];
    }
}

class DeferredResolverTest extends ResolveTest
{

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
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

        $directorType = newObjectType([
            'name'        => 'Director',
            'description' => 'Director of the movie',
            'fields'      => [
                'name' => [
                    'type' => stringType(),
                ]
            ]
        ]);

        $movieType = newObjectType([
            'name'        => 'Movie',
            'description' => 'A movie',
            'fields'      => [
                'title'    => ['type' => stringType()],
                'director' => [
                    'type'    => $directorType,
                    'resolve' => function ($movie, $args) {
                        DirectorBuffer::add($movie['directorId']);

                        return new Promise(function (callable $resolve, callable $reject) use ($movie) {
                            DirectorBuffer::loadBuffered();
                            $resolve(DirectorBuffer::get($movie['directorId']));
                        });
                    }
                ]
            ]
        ]);

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

        $result = graphql($schema, $query, $movies);

        $this->assertEquals([
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