# GraphQL

[![Build Status](https://travis-ci.org/digiaonline/graphql-php.svg?branch=master)](https://travis-ci.org/digiaonline/graphql-php)
[![Coverage Status](https://coveralls.io/repos/github/digiaonline/graphql-php/badge.svg?branch=master)](https://coveralls.io/github/digiaonline/graphql-php?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/digiaonline/graphql-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/digiaonline/graphql-php/?branch=master)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/digiaonline/graphql-php/master/LICENSE)

A PHP7 implementation of the [GraphQL specification](http://facebook.github.io/graphql/).

## Requirements

- PHP version >= 7.1 (this might change to >= 7.0 before the first release)

## Motivation

When we started developing this project there were two GraphQL implementations available for PHP developers; one from 
[Webonyx](https://github.com/webonyx/graphql-php/) and one from [Youshido](https://github.com/youshido/graphql/). 
The one from Webonyx is a direct port of the reference implementation and it does not fully use modern PHP. The other 
implementation from Youshido has some neat ideas, but unfortunately it does not fulfill the GraphQL specification, 
e.g. it does not come with proper query validation. After some research, we concluded that neither of these 
implementations met our needs, so we decided to start coding and see what we would come up with.

## Architecture

### Public API

This library exposes a very simple API using namespaced functions.

#### Dependency injection

We noticed quite early on that wiring together this library was no easy task. Especially if we wanted to allow 
developers to extend the library in a natural way. To solve this issue we introduced a
[Dependency injection](https://en.wikipedia.org/wiki/Dependency_injection) container. This container allows us to 
register each package as a separate service and ensure that shared class instances are treated as such. A good 
example of shared class instances are the specified GraphQL scalar types. The specification states that every type in a 
schema must be unique and by using shared instances through the container we can ensure that this requirement is met.

#### Namespaced functions

This project comes "batteries-included", which means that everything is set up in a way that allows developers to take 
this library into use quickly. We decided to use namespaced functions to keep the API simple.

### Execution

Takes care of executing queries against a GraphQL schema.

**Package lead: [@hungneox](https://github.com/hungneox/)**

#### Resolving data

Resolving data is a very important part of any GraphQL implementation, so we spent quite a lot of time figuring out how 
to solve this problem in a way that does not limit the developers and provides us with a solid foundation to build on. 
The current plan is to use a Resolver map, much like Apollo does in its 
[GraphQL Tools](https://www.apollographql.com/docs/graphql-tools/resolvers.html#Resolver-map) library, which maps each 
field to a corresponding resolver class.

In practice this means that the schema builder takes the schema and the resolver map and wires everything together to
build an executable schema. This approach will also allow us to support middleware for resolver, which is very handy 
for e.g. data transformation or authorization checks.   

#### Language

Defines the GraphQL language and the associated Abstact Syntax Tree (AST).

**Package lead: [@crisu83](https://github.com/crisu83/)**

#### Parsing

We want to encourage developers to use the official GraphQL parser written in C++ through a PHP extension because its 
performance is outstanding. However, we will also provide a shim for the parser, which will allow developers to use 
this library without installing a custom PHP extension in their environment.

The official GraphQL parser takes a GQL string as its input and returns the corresponding Abstract Syntax Tree (AST), 
an associative array in PHP, as its output. Most of the GraphQL implementations (across all languages) take a
different approach where they convert the AST directly into nodes (class instances in PHP). While this approach might 
be a little bit faster, it introduces tight coupling between the parser and the rest of the library, which we think is 
short-sighted. Instead we decided to take a different approach, where the parser produces the AST as an associative 
array. This will allow developers to use the C++ parser if they want more performance.

#### Abstract Syntax Tree

We introduced a builder system, using the [Builder pattern](https://en.wikipedia.org/wiki/Builder_pattern),
for converting the AST into nodes which allows developers to implement their own builders, without changing our code.

### Type system

Describes the GraphQL type system and schema definition.

**Package lead: [@crisu83](https://github.com/crisu83/)**

#### Schema definition

Most of the existing GraphQL implementations encourages the developer to create the schema programmatically. However,
GraphQL has an experimental feature which lets you define your schema using its Schema Definition Language (SDL). We 
think that this is the natural way to define the schema, so we built our type system around this idea. This approach 
will also allow developers to define the schema in formats native to different ecosystems, such as PHP array, YAML and 
even XML.

### Validation

**Package lead: [@crisu83](https://github.com/crisu83/)**

### Schema Validation

TODO

### Query validation

Even though some GraphQL implementations come without any query validation we decided to include it in the first 
version as it is a part of the specification. Query validation is done by evaluating the query AST using validation 
rules. We implemented this using the [Visitor pattern](https://en.wikipedia.org/wiki/Visitor_pattern) very much like 
it is implemented in the reference implementation. 

In practice this means that the query AST is visited by the each rule. Every rule takes care of 
validating the query against a particular part of the 
[Validation specification](http://facebook.github.io/graphql/October2016/#sec-Validation). This also allows 
developers to easily implement their own validation rules.

## Usage

### Installation

This library cannot yet be installed through [Composer](https://getcomposer.org/) because it is still being developed. 
As soon we have the first working version we will list it on [Packagist](https://packagist.org/). In the meanwhile you 
can clone the project and try it out yourself. 

### Example

Please note that the following example cannot be executed yet. However, we plan to include it as soon as it works.

This script demonstrates the public API:

```php
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\graphql;

$source = file_get_contents(__DIR__ . '/star-wars.graphqls');

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

print_r($result);
```

Produces the following output:

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

The schema definition used looks like this:

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

The public API consists of many more functions that can also be used separately. Developers can also choose to not 
use the functions at all and wire everything together themselves.

## Contributing

TODO

## License

See [LICENCE](LICENSE).
