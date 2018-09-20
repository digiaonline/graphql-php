<?php
/**
 * Created by PhpStorm.
 * User: hunguyen
 * Date: 20/09/2018
 * Time: 10.55
 */

namespace Digia\GraphQL\Test\Functional\Schema;


use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\graphql;

class SchemaBuilderTest extends TestCase
{
    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testSpecifyingInterfaceTypeUsingTypeNameMetaFieldDefinition()
    {
        $source = '
           type Query {
            characters: [Character]
          }
    
          interface Character {
            name: String!
          }
    
          type Human implements Character {
            name: String!
            totalCredits: Int
          }
    
          type Droid implements Character {
            name: String!
            primaryFunction: String
          }
      ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema($source);

        $query = '
         {
            characters {
              name
              ... on Human {
                totalCredits
              }
              ... on Droid {
                primaryFunction
              }
            }
          }
        ';

        $rootValue = [
            'characters' => [
                [
                    'name'         => 'Han Solo',
                    'totalCredits' => 10,
                    '__typename'   => 'Human',
                ],
                [
                    'name'            => 'R2-D2',
                    'primaryFunction' => 'Astromech',
                    '__typename'      => 'Droid',
                ],
            ],
        ];

        $result = graphql($schema, $query, $rootValue);

        $this->assertEquals([
            'data' => [
                'characters' => [
                    [
                        'name'         => 'Han Solo',
                        'totalCredits' => 10,
                    ],
                    [
                        'name'            => 'R2-D2',
                        'primaryFunction' => 'Astromech',
                    ],
                ],
            ],
        ], $result);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testSpecifyingUnionTypeUsingTypeNameMetaFieldDefinition()
    {
        $source = '
          type Query {
            fruits: [Fruit]
          }
    
          union Fruit = Apple | Banana
    
          type Apple {
            color: String
          }
    
          type Banana {
            length: Int
          }
      ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema($source);

        $query = '
          {
            fruits {
              ... on Apple {
                color
              }
              ... on Banana {
                length
              }
            }
          }
        ';

        $rootValue = [
            'fruits' => [
                [
                    'color'      => 'green',
                    '__typename' => 'Apple',
                ],
                [
                    'length'     => 5,
                    '__typename' => 'Banana',
                ],
            ],
        ];

        $result = graphql($schema, $query, $rootValue);

        $this->assertEquals([
            'data' => [
                'fruits' => [
                    [
                        'color' => 'green',
                    ],
                    [
                        'length' => 5,
                    ],
                ],
            ],
        ], $result);
    }
}