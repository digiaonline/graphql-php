# GraphQL

[![Build Status](https://travis-ci.org/digiaonline/graphql-php.svg?branch=master)](https://travis-ci.org/digiaonline/graphql-php)
[![Coverage Status](https://coveralls.io/repos/github/digiaonline/graphql-php/badge.svg?branch=master)](https://coveralls.io/github/digiaonline/graphql-php?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/digiaonline/graphql-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/digiaonline/graphql-php/?branch=master)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/digiaonline/graphql-php/master/LICENSE)

This is a PHP implementation of the [GraphQL specification](https://facebook.github.io/graphql/) based on the 
JavaScript [reference implementation](https://github.com/graphql/graphql-js).

## Requirements

- PHP version >= 7.1
- ext-mbstring

## Table of contents

- [Installation](#installation)
- [Example](#example)
- [Creating a schema](#creating-a-schema)
- [Execution](#execution)
- [Scalars](#scalars)
- [Integration](#integration)
- [Relay support](#relay-support)

## Installation

Run the following command to install the package through Composer:

```sh
composer require digiaonline/graphql
```

## Example

Here is a simple example that demonstrates how to build an executable schema from a GraphQL schema file that contains 
the Schema Definition Language (SDL) for a Star Wars-themed schema (for the schema definition itself, see below). In 
this example we use that SDL to build an executable schema and use it to query for the name of the hero. The result 
of that query is an associative array with a structure that resembles the query we ran.

```php
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\graphql;

$source = \file_get_contents(__DIR__ . '/star-wars.graphqls');

$schema = buildSchema($source, [
    'Query' => [
        'hero' => function ($rootValue, $arguments) {
            return getHero($arguments['episode'] ?? null);
        },
    ],
]);

$result = graphql($schema, '
query HeroNameQuery {
  hero {
    name
  }
}');

\print_r($result);
```

The script above produces the following output:

```php
Array
(
    [data] => Array
    (
        [hero] => Array
        (
            [name] => "R2-D2"
        )
        
    )
    
)
```

The GraphQL schema file used in this example contains the following:

```graphql schema
schema {
    query: Query
}

type Query {
    hero(episode: Episode): Character
    human(id: String!): Human
    droid(id: String!): Droid
}

interface Character {
    id: String!
    name: String
    friends: [Character]
    appearsIn: [Episode]
}

type Human implements Character {
    id: String!
    name: String
    friends: [Character]
    appearsIn: [Episode]
    homePlanet: String
}

type Droid implements Character {
    id: String!
    name: String
    friends: [Character]
    appearsIn: [Episode]
    primaryFunction: String
}

enum Episode { NEWHOPE, EMPIRE, JEDI }
```

## Creating a schema

In order to execute queries against your GraphQL API, you first need to define the structure of your API. This is done
by creating a schema. There are two ways to do this, you can either do it using SDL or you can do it programmatically. 
However, we strongly encourage you to use SDL, because it is easier to work with. To make an executable schema from 
SDL you need to call the `buildSchema` function.
 
The `buildSchema` function takes three arguments:

- `$source` The schema definition (SDL) as a string
- `$resolverRegistry` An associative array or a `ResolverRegistry` instance that contains all resolvers
- `$options` The options for building the schema, which also includes custom types and directives

### Resolver registry

The resolver registry is essentially a flat map with the type names as its keys and their corresponding resolver 
instances as its values. For smaller projects you can use an associative array and lambda functions to define your 
resolver registry. However, in larger projects we suggest that you implement your own resolvers instead. You can read 
more about resolvers under the [Resolvers](#resolvers) section.

Associative array example:

```php
$schema = buildSchema($source, [
    'Query' => [
        'hero' => function ($rootValue, $arguments) {
            return getHero($arguments['episode'] ?? null);
        },
    ],
]);
```

Resolver class example:

```php
$schema = buildSchema($source, [
    'Query' => QueryResolver::class,
]);
```

If you want to learn more about schemas you can refer to the [specification](https://graphql.org/learn/schema/).

## Execution

### Queries

To execute a query against your schema you need to call the `graphql` function and pass it your schema and the query 
you wish to execute. You can also run _mutations_ and _subscriptions_ by changing your query.

```php
$query = '
query HeroNameQuery {
  hero {
    name
  }
}';

$result = graphql($schema, $query);
```

If you want to learn more about queries you can refer to the [specification](https://graphql.org/learn/queries/).

### Resolvers

Each type in a schema has a resolver associated with it that allows for resolving the actual value. However, most 
types do not need a custom resolver, because they can be resolved using the default resolver. Usually these resolvers 
are lambda functions, but you can also define your own resolvers by implementing the `ResolverInterface`. 

A resolver function receives four arguments:

- `$rootValue` The parent object, which can also be `null` in some cases
- `$arguments` The arguments provided to the field in the query
- `$context` A value that is passed to every resolver that can hold important contextual information
- `$info` A value which holds field-specific information relevant to the current query

Lambda function example:

```php
function ($rootValue, array $arguments, $context, ResolveInfo $info): string {
    return [
        'type'       => 'Human',
        'id'         => '1000',
        'name'       => 'Luke Skywalker',
        'friends'    => ['1002', '1003', '2000', '2001'],
        'appearsIn'  => ['NEWHOPE', 'EMPIRE', 'JEDI'],
        'homePlanet' => 'Tatooine',
    ];
}
``` 

Resolver class example:

```php
class QueryResolver implements ResolverInterface
{
    public function resolveHero($rootValue, array $arguments, $context, ResolveInfo $info): string
    {
       return [
           'type'       => 'Human',
           'id'         => '1000',
           'name'       => 'Luke Skywalker',
           'friends'    => ['1002', '1003', '2000', '2001'],
           'appearsIn'  => ['NEWHOPE', 'EMPIRE', 'JEDI'],
           'homePlanet' => 'Tatooine',
       ];
    }
}
```

### Variables

You can pass in variables when executing a query by passing them to the `graphql` function.

```php
$query = '
query HeroNameQuery($id: ID!) {
  hero(id: $id) {
    name
  }
}';

$variables = ['id' => '1000'];

$result = graphql($schema, $query, null, null, $variables);
```

### Context

In case you need to pass in some important contextual information to your queries you can use the `$contextValues` 
argument on `graphql` to do so. This data will be passed to all of your resolvers as the `$context` argument.

```php
$contextValues = [
    'currentlyLoggedInUser' => $currentlyLoggedInUser,
];

$result = graphql($schema, $query, null, $contextValues, $variables);
```

## Scalars

The leaf nodes in a schema are called scalars and each scalar resolves to some concrete data. The built-in, or 
specified scalars in GraphQL are the following:

- Boolean
- Float
- Int
- ID
- String

### Custom scalars

In addition to the specified scalars you can also define your own custom scalars and let your schema know about 
them by passing them to the `buildSchema` function as part of its `$options` argument. 

Custom Date scalar type example:

```php
$dateType = newScalarType([
    'name'         => 'Date',
    'serialize'    => function (DateTime $value) {
        return $value->format('Y-m-d');
    },
    'parseValue'   => function (DateTime $value) {
        return $value->format('Y-m-d');
    },
    'parseLiteral' => function ($node) {
        if ($node instanceof StringValueNode) {
            return new DateTime($node->getValue());
        }
        return null;
    },
]);

$schema = buildSchema($source, [
    'Query' => QueryResolver::class,
    [
        'types' => [$dateType],
    ],
]);
```

Every scalar has to be coerced, which is done by three different functions. The `serialize` function converts a 
PHP value into the corresponding output value. The`parseValue` function converts a variable input value into the
corresponding PHP value and the `parseLiteral` function converts an AST literal into the corresponding PHP value.

## Advanced usage

If you are looking for something that isn't yet covered by this documentation your best bet is to take a look at the 
[tests](./tests) in this project. You'll be surprised how many examples you'll find there.

## Integration

### Laravel

Here is an example that demonstrates how you can use this library in your Laravel project. You need an application 
service to expose this library to your application, a service provider to register that service, a controller and a 
route for handling the GraphQL POST requests.

**app/GraphQL/GraphQLService.php**
```php
class GraphQLService
{
    private $schema;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function executeQuery(string $query, array $variables, ?string $operationName): array
    {
        return graphql($this->schema, $query, null, null, $variables, $operationName);
    }
}
```

**app/GraphQL/GraphQLServiceProvider.php**
```php
class GraphQLServiceProvider
{
    public function register()
    {
        $this->app->singleton(GraphQLService::class, function () {
            $schemaDef = \file_get_contents(__DIR__ . '/schema.graphqls');

            $executableSchema = buildSchema($schemaDef, [
                'Query' => QueryResolver::class,
            ]);

            return new GraphQLService($executableSchema);
        });
    }
}
```

**app/GraphQL/GraphQLController.php**
```php
class GraphQLController extends Controller
{
    private $graphqlService;

    public function __construct(GraphQLService $graphqlService)
    {
        $this->graphqlService = $graphqlService;
    }

    public function handle(Request $request): JsonResponse
    {
        $query         = $request->get('query');
        $variables     = $request->get('variables') ?? [];
        $operationName = $request->get('operationName');

        $result = $this->graphqlService->executeQuery($query, $variables, $operationName);

        return response()->json($result);
    }
}
```

**routes/api.php**
```php
Route::post('/graphql', 'app\GraphQL\GraphQLController@handle');
```

## Relay support

If you want to use [Relay](https://facebook.github.io/relay/) together with this library, you can use our 
[Relay package](https://github.com/digiaonline/graphql-relay-php) to add Relay support. 

## Contributing

Please read our [guidelines](.github/CONTRIBUTING.md).

## License

See [LICENCE](LICENSE).
