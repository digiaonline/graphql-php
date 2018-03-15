<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class AbstractTest extends TestCase
{
    // EXECUTE: HANDLES EXECUTION OF ABSTRACT TYPES

    /**
     * isTypeOf used to resolve runtime type for Interface
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveFunctionForInterface()
    {
        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => GraphQLList(PetType()),
                        'resolve' => function ($source, $args, $context, $info) {
                            return [new Dog('Odie', true), new Cat('Garfield', false)];
                        }
                    ]
                ]
            ]),
            'types' => [DogType(), CatType()]
        ]);

        $source = '{
          pets {
            name
            ... on Dog {
              woofs
            }
            ... on Cat {
              meows
            }
          }
        }';

        /** @var ExecutionResult $executionResult */
        $result = execute($schema, parse($source));

        $expected = new ExecutionResult([
            'pets' => [
                [
                    'name'  => 'Odie',
                    'woofs' => true,
                ],
                [
                    'name'  => 'Garfield',
                    'meows' => false,
                ]
            ]
        ], []);

        $this->assertEquals($expected->getData(), $result->getData());
    }
}

class Human
{
    public $name;

    public function __construct(string $name)
    {
        $this->name;
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
    public $woofs;

    public function __construct(string $name, bool $meows)
    {
        $this->name  = $name;
        $this->meows = $meows;
    }
}

function PetType(): InterfaceType
{
    return GraphQLInterfaceType([
        'name'        => 'Pet',
        'fields'      => [
            'name' => ['type' => GraphQLString()]
        ],
        'interfaces'  => [DogType(), CatType()],
        'resolveType' => function ($result, $context, $info) {
            if ($result instanceof Dog) {
                return DogType();
            }

            if ($result instanceof Cat) {
                return CatType();
            }
        }
    ]);
}

function DogType(): ObjectType
{
    return GraphQLObjectType([
        'name'     => 'Dog',
        'fields'   => [
            'name'  => ['type' => GraphQLString()],
            'woofs' => ['type' => GraphQLBoolean()],
        ],
        'isTypeOf' => function ($obj) {
            return $obj instanceof Dog;
        }
    ]);
}

function CatType(): ObjectType
{
    return GraphQLObjectType([
        'name'     => 'Cat',
        'fields'   => [
            'name'  => ['type' => GraphQLString()],
            'meows' => ['type' => GraphQLBoolean()],
        ],
        'isTypeOf' => function ($obj) {
            return $obj instanceof Cat;
        }
    ]);
}