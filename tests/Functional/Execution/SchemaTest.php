<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\Boolean;
use function Digia\GraphQL\Type\ID;
use function Digia\GraphQL\Type\Int;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\String;


class SchemaTest extends TestCase
{
    // Execute: Handles execution with a complex schema

    /**
     * Executes using a schema
     *
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesUsingAschema()
    {
        $BlogImage = newObjectType([
            'name'   => 'Image',
            'fields' => [
                'url'    => ['type' => String()],
                'height' => ['type' => Int()],
                'width'  => ['type' => Int()],
            ]
        ]);

        $BlogAuthor = newObjectType([
            'name'   => 'Author',
            'fields' => function () use (&$BlogArticle, &$BlogImage) {
                return [
                    'id'            => ['type' => String()],
                    'name'          => ['type' => String()],
                    'pic'           => [
                        'type'    => $BlogImage,
                        'args'    => [
                            'width'  => ['type' => Int()],
                            'height' => ['type' => Int()]
                        ],
                        'resolve' => function ($root, $args) {
                            ['width' => $width, 'height' => $height] = $args;
                            return $root['pic']($width, $height);
                        }
                    ],
                    'recentArticle' => ['type' => $BlogArticle],

                ];
            }
        ]);

        $BlogArticle = newObjectType([
            'name'   => 'Article',
            'fields' => [
                'id'          => ['type' => newNonNull(String())],
                'isPublished' => ['type' => Boolean()],
                'author'      => ['type' => $BlogAuthor],
                'title'       => ['type' => String()],
                'body'        => ['type' => String()],
                'keywords'    => ['type' => newList(String())],
            ]
        ]);

        $Query = newObjectType([
            'name'   => 'Query',
            'fields' => [
                'article' => [
                    'type'    => $BlogArticle,
                    'args'    => [
                        'id' => ['type' => ID()]
                    ],
                    'resolve' => function ($root, $args) use (&$article) {
                        return $article($args['id']);
                    }
                ],
                'feed'   => [
                    'type'    => newList($BlogArticle),
                    'resolve' => function ($root, $args) use (&$article) {
                        return [
                            $article(1),
                            $article(2),
                            $article(3),
                            $article(4),
                            $article(5),
                            $article(6),
                            $article(7),
                            $article(8),
                            $article(9),
                            $article(10),
                        ];
                    }
                ]
            ]
        ]);

        $article = function ($id) use (&$johnSmith) {
            return [
                'id'          => $id,
                'isPublished' => 'true',
                'author'      => $johnSmith,
                'title'       => 'My Article ' . $id,
                'body'        => 'This is a post',
                'hidden'      => 'This data is not exposed in the schema',
                'keywords'    => ['foo', 'bar', 1, true, null],
            ];
        };

        $getPic = function ($uid, $width, $height) {
            return [
                'url'    => "cdn://${uid}",
                'width'  => $width,
                'height' => $height,
            ];
        };

        $johnSmith = function () use (&$article, &$getPic) {
            return [
                'id'            => 123,
                'name'          => 'John Smith',
                'pic'           => function ($width, $height) use (&$getPic) {
                    return $getPic(123, $width, $height);
                },
                'recentArticle' => $article(1)
            ];
        };

        $BlogSchema = newSchema([
            'query' => $Query
        ]);

        $request = '
          {
            feed {
              id,
              title
            },
            article(id: "1") {
              ...articleFields,
              author {
                id,
                name,
                pic(width: 640, height: 480) {
                  url,
                  width,
                  height
                },
                recentArticle {
                  ...articleFields,
                  keywords
                }
              }
            }
          }
    
          fragment articleFields on Article {
            id,
            isPublished,
            title,
            body,
            hidden,
            notdefined
          }
        ';

        $result = execute($BlogSchema, parse($request));

        $this->assertEquals([
            'data' => [
                'feed'    => [
                    [
                        'id'    => '1',
                        'title' => 'My Article 1',
                    ],
                    [
                        'id'    => '2',
                        'title' => 'My Article 2',
                    ],
                    [
                        'id'    => '3',
                        'title' => 'My Article 3',
                    ],
                    [
                        'id'    => '4',
                        'title' => 'My Article 4',
                    ],
                    [
                        'id'    => '5',
                        'title' => 'My Article 5',
                    ],
                    [
                        'id'    => '6',
                        'title' => 'My Article 6',
                    ],
                    [
                        'id'    => '7',
                        'title' => 'My Article 7',
                    ],
                    [
                        'id'    => '8',
                        'title' => 'My Article 8',
                    ],
                    [
                        'id'    => '9',
                        'title' => 'My Article 9',
                    ],
                    [
                        'id'    => '10',
                        'title' => 'My Article 10',
                    ],
                ],
                'article' => [
                    'id'          => '1',
                    'isPublished' => true,
                    'title'       => 'My Article 1',
                    'body'        => 'This is a post',
                    'author'      => [
                        'id'            => '123',
                        'name'          => 'John Smith',
                        'pic'           => [
                            'url'    => 'cdn://123',
                            'width'  => 640,
                            'height' => 480,
                        ],
                        'recentArticle' => [
                            'id'          => '1',
                            'isPublished' => true,
                            'title'       => 'My Article 1',
                            'body'        => 'This is a post',
                            'keywords'    => ['foo', 'bar', '1', 'true', null],
                        ],
                    ]
                ]
            ]
        ], $result->toArray());
    }
}