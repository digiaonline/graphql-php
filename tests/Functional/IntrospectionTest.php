<?php

namespace Digia\GraphQL\Test\Functional;

use function Digia\GraphQL\buildSchema;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\newScalarType;

class IntrospectionTest extends TestCase
{
    // Star Wars Introspection Tests

    // Basic Introspection

    // Allows querying the schema for types

    public function testAllowsQueryingTheSchemaForTypes()
    {
        $query = '
        query IntrospectionTypeQuery {
          __schema {
            types {
              name
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__schema' => [
                    'types' => [
                        ['name' => 'Query'],
                        ['name' => 'Episode'],
                        ['name' => 'Character'],
                        ['name' => 'String'],
                        ['name' => 'Human'],
                        ['name' => 'Droid'],
                        ['name' => '__Schema'],
                        ['name' => '__Type'],
                        ['name' => '__TypeKind'],
                        ['name' => 'Boolean'],
                        ['name' => '__Field'],
                        ['name' => '__InputValue'],
                        ['name' => '__EnumValue'],
                        ['name' => '__Directive'],
                        ['name' => '__DirectiveLocation'],
                    ],
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for query type

    public function testAllowsQueryingTheSchemaForQueryType()
    {
        $query = '
        query IntrospectionQueryTypeQuery {
          __schema {
            queryType {
              name
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'name' => 'Query',
                    ],
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for a specific type

    public function testAllowsQueryingTheSchemaForASpecificType()
    {
        $query = '
        query IntrospectionDroidTypeQuery {
          __type(name: "Droid") {
            name
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name' => 'Droid',
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for an object kind

    public function testAllowsQueryingTheSchemaForAnObjectKind()
    {
        $query = '
        query IntrospectionDroidKindQuery {
          __type(name: "Droid") {
            name
            kind
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name' => 'Droid',
                    'kind' => 'OBJECT',
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for an interface kind

    public function testAllowsQueryingTheSchemaForAnInterfaceKind()
    {
        $query = '
        query IntrospectionCharacterKindQuery {
          __type(name: "Character") {
            name
            kind
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name' => 'Character',
                    'kind' => 'INTERFACE',
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for object fields

    public function testAllowsQueryingTheSchemaForObjectFields()
    {
        $query = '
        query IntrospectionDroidFieldsQuery {
          __type(name: "Droid") {
            name
            fields {
              name
              type {
                name
                kind
              }
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name'   => 'Droid',
                    'fields' => [
                        [
                            'name' => 'id',
                            'type' => [
                                'name' => null,
                                'kind' => 'NON_NULL',
                            ],
                        ],
                        [
                            'name' => 'name',
                            'type' => [
                                'name' => 'String',
                                'kind' => 'SCALAR',
                            ],
                        ],
                        [
                            'name' => 'friends',
                            'type' => [
                                'name' => null,
                                'kind' => 'LIST',
                            ],
                        ],
                        [
                            'name' => 'appearsIn',
                            'type' => [
                                'name' => null,
                                'kind' => 'LIST',
                            ],
                        ],
                        [
                            'name' => 'secretBackstory',
                            'type' => [
                                'name' => 'String',
                                'kind' => 'SCALAR',
                            ],
                        ],
                        [
                            'name' => 'primaryFunction',
                            'type' => [
                                'name' => 'String',
                                'kind' => 'SCALAR',
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for nested object fields

    public function testAllowsQueryingTheSchemaForNestedObjectFields()
    {
        $query = '
        query IntrospectionDroidNestedFieldsQuery {
          __type(name: "Droid") {
            name
            fields {
              name
              type {
                name
                kind
                ofType {
                  name
                  kind
                }
              }
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name'   => 'Droid',
                    'fields' => [
                        [
                            'name' => 'id',
                            'type' => [
                                'name'   => null,
                                'kind'   => 'NON_NULL',
                                'ofType' => [
                                    'name' => 'String',
                                    'kind' => 'SCALAR',
                                ],
                            ],
                        ],
                        [
                            'name' => 'name',
                            'type' => [
                                'name'   => 'String',
                                'kind'   => 'SCALAR',
                                'ofType' => null,
                            ],
                        ],
                        [
                            'name' => 'friends',
                            'type' => [
                                'name'   => null,
                                'kind'   => 'LIST',
                                'ofType' => [
                                    'name' => 'Character',
                                    'kind' => 'INTERFACE',
                                ],
                            ],
                        ],
                        [
                            'name' => 'appearsIn',
                            'type' => [
                                'name'   => null,
                                'kind'   => 'LIST',
                                'ofType' => [
                                    'name' => 'Episode',
                                    'kind' => 'ENUM',
                                ],
                            ],
                        ],
                        [
                            'name' => 'secretBackstory',
                            'type' => [
                                'name'   => 'String',
                                'kind'   => 'SCALAR',
                                'ofType' => null,
                            ],
                        ],
                        [
                            'name' => 'primaryFunction',
                            'type' => [
                                'name'   => 'String',
                                'kind'   => 'SCALAR',
                                'ofType' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for field args

    public function testAllowsQueryingTheSchemaForFieldArguments()
    {
        $query = '
        query IntrospectionQueryTypeQuery {
          __schema {
            queryType {
              fields {
                name
                args {
                  name
                  description
                  type {
                    name
                    kind
                    ofType {
                      name
                      kind
                    }
                  }
                  defaultValue
                }
              }
            }
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'fields' => [
                            [
                                'name' => 'hero',
                                'args' => [
                                    [
                                        'defaultValue' => null,
                                        'description'  =>
                                            'If omitted, returns the hero of the whole ' .
                                            'saga. If provided, returns the hero of ' .
                                            'that particular episode.',
                                        'name'         => 'episode',
                                        'type'         => [
                                            'kind'   => 'ENUM',
                                            'name'   => 'Episode',
                                            'ofType' => null,
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'name' => 'human',
                                'args' => [
                                    [
                                        'name'         => 'id',
                                        'description'  => 'id of the human',
                                        'type'         => [
                                            'kind'   => 'NON_NULL',
                                            'name'   => null,
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'String',
                                            ],
                                        ],
                                        'defaultValue' => null,
                                    ],
                                ],
                            ],
                            [
                                'name' => 'droid',
                                'args' => [
                                    [
                                        'name'         => 'id',
                                        'description'  => 'id of the droid',
                                        'type'         => [
                                            'kind'   => 'NON_NULL',
                                            'name'   => null,
                                            'ofType' => [
                                                'kind' => 'SCALAR',
                                                'name' => 'String',
                                            ],
                                        ],
                                        'defaultValue' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    // Allows querying the schema for documentation

    public function testAllowsQueryingTheSchemaForDocumentation()
    {
        $query = '
        query IntrospectionDroidDescriptionQuery {
          __type(name: "Droid") {
            name
            description
          }
        }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql(starWarsSchema(), $query);

        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name'        => 'Droid',
                    'description' => 'A mechanical creature in the Star Wars universe.',
                ],
            ],
        ], $result);
    }

    /**
     * Test to check that we can introspect on a scalar, which does *not*
     * have a name which clashes with a global PHP function or class
     */
    public function testCanIntrospectOnANonClashingScalar()
    {
        $schema = '
        scalar Postcode
        type Query {
            hello: Postcode
        }
        ';


        $schema = buildSchema($schema);

        $query = '
        query IntrospectionDroidDescriptionQuery {
          __type(name: "Postcode") {
            name
          }
        }
        ';

        $result = graphql($schema, $query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name'        => 'Postcode',
                ],
            ],
        ], $result);
    }

    /**
     * Test to check that we can introspect on a scalar, which *does*
     * have a name which clashes with a global PHP function or class
     */
    public function testCanIntrospectOnScalarWithClashingName()
    {
        $schema = '
        scalar Date
        type Query {
            hello: Date
        }
        ';


        $schema = buildSchema($schema);

        $query = '
        query IntrospectionDroidDescriptionQuery {
          __type(name: "Date") {
            name
          }
        }
        ';

        $result = graphql($schema, $query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals([
            'data' => [
                '__type' => [
                    'name'        => 'Date',
                ],
            ],
        ], $result);
    }

}
