<?php

namespace Digia\GraphQL\Test\Functional\Execution;

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


class Person
{
    public $name;
    public $pets;
    public $friends;

    public function __construct(string $name, array $pets = [], array $friends = [])
    {
        $this->name    = $name;
        $this->friends = $friends;
        $this->pets    = $pets;
    }
}

class Dog
{
    public $name;
    public $woofs;

    public function __construct(string $name, bool $woofs)
    {
        $this->name  = $name;
        $this->woofs = $woofs;
    }
}

class Cat
{
    public $name;
    public $meows;

    public function __construct(string $name, bool $meows)
    {
        $this->name  = $name;
        $this->meows = $meows;
    }
}


class UnionInterfaceTest extends TestCase
{
    private $schema;


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

        $garfield = new Cat('Garfield', false);
        $odie     = new Dog('Odie', true);
        $liz      = new Person('Liz');
        $john     = new Person('John', [$garfield, $odie], [$liz, $odie]);


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
}

