<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\Resolver\ResolveInfo;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\GraphQLUnionType;


class UnionInterfaceTest extends TestCase
{
    private $schema;

    private $garfield;

    private $odie;

    private $liz;

    private $john;

    public function setUp()
    {
        parent::setUp();

        $NamedType = GraphQLInterfaceType([
            'name'   => 'Named',
            'fields' => [
                'name' => ['type' => GraphQLString()]
            ]
        ]);

        $DogType = GraphQLObjectType([
            'name'       => 'Dog',
            'interfaces' => [$NamedType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = GraphQLObjectType([
            'name'       => 'Cat',
            'interfaces' => [$NamedType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        $PetType = GraphQLUnionType([
            'name'        => 'Pet',
            'types'       => [$DogType, $CatType],
            'resolveType' => function ($result, $context, $info) use ($DogType, $CatType) {
                if ($result instanceof Dog) {
                    return $DogType;
                }

                if ($result instanceof Cat) {
                    return $CatType;
                }

                return null;
            }
        ]);

        $PersonType = GraphQLObjectType([
            'name'       => 'Person',
            'interfaces' => [$NamedType],
            'fields'     => [
                'name'    => ['type' => GraphQLString()],
                'pets'    => ['type' => GraphQLList($PetType)],
                'friends' => ['type' => GraphQLList($NamedType)],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Person;
            }
        ]);

        $schema = GraphQLSchema([
            'query' => $PersonType
        ]);

        $this->garfield = new Cat('Garfield', false);
        $this->odie     = new Dog('Odie', true);
        $this->liz      = new Person('Liz');
        $this->john     = new Person('John', [$this->garfield, $this->odie], [$this->liz, $this->odie]);


        $this->schema = $schema;
    }

    //EXECUTE: UNION AND INTERSECTION TYPES

    /**
     * can introspect on union and intersection types
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testCanIntrospectOnUnionAndIntersectionTypes()
    {
        $source = '{
        Named: __type(name: "Named") {
          kind
          name
          fields { name }
          interfaces { name }
          possibleTypes { name }
          enumValues { name }
          inputFields { name }
        }
        Pet: __type(name: "Pet") {
          kind
          name
          fields { name }
          interfaces { name }
          possibleTypes { name }
          enumValues { name }
          inputFields { name }
        }
      }';

        $expected = [
            'Named' => [
                'kind'          => 'INTERFACE',
                'name'          => 'Named',
                'fields'        => [
                    ['name' => 'name']
                ],
                'interfaces'    => null,
                'possibleTypes' => [
                    ['name' => 'Person'],
                    ['name' => 'Dog'],
                    ['name' => 'Cat']
                ],
                'enumValues'    => null,
                'inputFields'   => null
            ],
            'Pet'   => [
                'kind'          => 'UNION',
                'name'          => 'Pet',
                'fields'        => null,
                'interfaces'    => null,
                'possibleTypes' => [
                    ['name' => 'Dog'],
                    ['name' => 'Cat']
                ],
                'enumValues'    => null,
                'inputFields'   => null
            ]
        ];

        $this->assertEquals($expected, execute($this->schema, parse($source))->getData());
    }

    /**
     * executes using union types
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesUsingUnionTypes()
    {
        $source = '  {
        __typename
        name
        pets {
          __typename
          name
          woofs
          meows
        }
      }';

        $expected = [
            '__typename' => 'Person',
            'name'       => 'John',
            'pets'       => [
                ['__typename' => 'Cat', 'name' => 'Garfield', 'meows' => false],
                ['__typename' => 'Dog', 'name' => 'Odie', 'woofs' => true],
            ]
        ];

        $this->assertEquals($expected, execute($this->schema, parse($source), $this->john)->getData());
    }

    /**
     * executes union types with inline fragments
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesUnionTypesWithInlineFragments()
    {
        $source = '{
        __typename
        name
        pets {
          __typename
          ... on Dog {
            name
            woofs
          }
          ... on Cat {
            name
            meows
          }
        }
      }';

        $expected = [
            '__typename' => 'Person',
            'name'       => 'John',
            'pets'       => [
                ['__typename' => 'Cat', 'name' => 'Garfield', 'meows' => false],
                ['__typename' => 'Dog', 'name' => 'Odie', 'woofs' => true],
            ]
        ];

        $this->assertEquals($expected, execute($this->schema, parse($source), $this->john)->getData());
    }

    /**
     * executes using interface types
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesUsingInterfaceTypes()
    {
        $source = '{
        __typename
        name
        friends {
          __typename
          name
          woofs
          meows
        }
      }';

        $expected = [
            '__typename' => 'Person',
            'name'       => 'John',
            'friends'    => [
                ['__typename' => 'Person', 'name' => 'Liz'],
                ['__typename' => 'Dog', 'name' => 'Odie', 'woofs' => true],
            ]
        ];

        $this->assertEquals($expected, execute($this->schema, parse($source), $this->john)->getData());
    }

    /**
     * executes union types with inline fragments
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesUnionTypesWithInlineFragmentsTwo()
    {
        $source = '{
        __typename
        name
        friends {
          __typename
          name
          ... on Dog {
            woofs
          }
          ... on Cat {
            meows
          }
        }
      }';

        $expected = [
            '__typename' => 'Person',
            'name'       => 'John',
            'friends'    => [
                ['__typename' => 'Person', 'name' => 'Liz'],
                ['__typename' => 'Dog', 'name' => 'Odie', 'woofs' => true],
            ]
        ];

        $this->assertEquals($expected, execute($this->schema, parse($source), $this->john)->getData());
    }

    /**
     * allows fragment conditions to be abstract types
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsFragmentConditionsToBeAbstractTypes()
    {
        $source = '{
        __typename
        name
        pets { ...PetFields }
        friends { ...FriendFields }
      }

      fragment PetFields on Pet {
        __typename
        ... on Dog {
          name
          woofs
        }
        ... on Cat {
          name
          meows
        }
      }

      fragment FriendFields on Named {
        __typename
        name
        ... on Dog {
          woofs
        }
        ... on Cat {
          meows
        }
      }';

        $expected = [
            '__typename' => 'Person',
            'name'       => 'John',
            'pets'       => [
                ['__typename' => 'Cat', 'name' => 'Garfield', 'meows' => false],
                ['__typename' => 'Dog', 'name' => 'Odie', 'woofs' => true]
            ],
            'friends'    => [
                ['__typename' => 'Person', 'name' => 'Liz'],
                ['__typename' => 'Dog', 'name' => 'Odie', 'woofs' => true]
            ]
        ];

        $this->assertEquals($expected, execute($this->schema, parse($source), $this->john)->getData());
    }

    /**
     * gets execution info in resolver
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testGetsExecutionInfoInResolver()
    {

        $encounteredContext   = null;
        $encounteredSchema    = null;
        $encounteredRootValue = null;
        $PersonType2          = null;

        $NamedType2 = GraphQLInterfaceType([
            'name'        => 'Named',
            'fields'      => [
                'name' => ['type' => GraphQLString()]
            ],
            'resolveType' => function ($obj, $context, ResolveInfo $info) use (
                &$encounteredContext,
                &$encounteredSchema,
                &$encounteredRootValue,
                &$PersonType2
            ) {
                $encounteredContext   = $context;
                $encounteredSchema    = $info->getSchema();
                $encounteredRootValue = $info->getRootValue();
                return $PersonType2;
            }
        ]);

        $PersonType2 = GraphQLObjectType([
            'name'       => 'Person',
            'interfaces' => [$NamedType2],
            'fields'     => [
                'name'    => ['type' => GraphQLString()],
                'friends' => ['type' => GraphQLList($NamedType2)],
            ],
        ]);

        $schema2 = GraphQLSchema([
            'query' => $PersonType2
        ]);

        $john2 = new Person('John', [], [$this->liz]);

        $context = ['authToken' => '123abc'];

        $this->assertEquals(
            ['name' => 'John', 'friends' => [['name' => 'Liz']]],
            execute($schema2, parse('{ name, friends { name } }'), $john2, $context)->getData()
        );

        $this->assertSame($context, $encounteredContext);
        $this->assertSame($schema2, $encounteredSchema);
        $this->assertSame($john2, $encounteredRootValue);
    }
}

