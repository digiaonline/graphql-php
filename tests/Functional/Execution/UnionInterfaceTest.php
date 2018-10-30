<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\booleanType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;
use function Digia\GraphQL\Type\newUnionType;

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

        /** @noinspection PhpUnhandledExceptionInspection */
        $NamedType = newInterfaceType([
            'name'   => 'Named',
            'fields' => [
                'name' => ['type' => stringType()]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$NamedType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$NamedType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $PetType = newUnionType([
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $PersonType = newObjectType([
            'name'       => 'Person',
            'interfaces' => [$NamedType],
            'fields'     => [
                'name'    => ['type' => stringType()],
                'pets'    => ['type' => newList($PetType)],
                'friends' => ['type' => newList($NamedType)],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Person;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
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
     */
    public function testCanIntrospectOnUnionAndIntersectionTypes()
    {
        $source = '
        {
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($this->schema, parse($source));

        $this->assertSame([
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
        ], $result->getData());
    }

    /**
     * executes using union types
     */
    public function testExecutesUsingUnionTypes()
    {
        $source = '
        {
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($this->schema, parse($source), $this->john);

        $this->assertEquals($expected, $result->getData());
    }

    /**
     * executes union types with inline fragments
     */
    public function testExecutesUnionTypesWithInlineFragments()
    {
        $source = '
        {
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($this->schema, parse($source), $this->john);

        $this->assertSame($expected, $result->getData());
    }

    /**
     * executes using interface types
     */
    public function testExecutesUsingInterfaceTypes()
    {
        $source = '
        {
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($this->schema, parse($source), $this->john);

        $this->assertSame($expected, $result->getData());
    }

    /**
     * executes union types with inline fragments
     */
    public function testExecutesUnionTypesWithInlineFragmentsTwo()
    {
        $source = '
        {
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($this->schema, parse($source), $this->john);

        $this->assertSame($expected, $result->getData());
    }

    /**
     * allows fragment conditions to be abstract types
     */
    public function testAllowsFragmentConditionsToBeAbstractTypes()
    {
        $source = '
        {
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($this->schema, parse($source), $this->john);

        $this->assertSame($expected, $result->getData());
    }

    /**
     * gets execution info in resolver
     */
    public function testGetsExecutionInfoInResolver()
    {

        $encounteredContext   = null;
        $encounteredSchema    = null;
        $encounteredRootValue = null;
        $PersonType2          = null;

        /** @noinspection PhpUnhandledExceptionInspection */
        $NamedType2 = newInterfaceType([
            'name'        => 'Named',
            'fields'      => [
                'name' => ['type' => stringType()]
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $PersonType2 = newObjectType([
            'name'       => 'Person',
            'interfaces' => [$NamedType2],
            'fields'     => [
                'name'    => ['type' => stringType()],
                'friends' => ['type' => newList($NamedType2)],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema2 = newSchema([
            'query' => $PersonType2
        ]);

        $john2 = new Person('John', [], [$this->liz]);

        $context = ['authToken' => '123abc'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema2, parse('{ name, friends { name } }'), $john2, $context);

        $this->assertSame(
            ['name' => 'John', 'friends' => [['name' => 'Liz']]],
            $result->getData()
        );

        $this->assertSame($context, $encounteredContext);
        $this->assertSame($schema2, $encounteredSchema);
        $this->assertSame($john2, $encounteredRootValue);
    }
}

