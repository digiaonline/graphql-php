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
The one from Webonyx is a direct port of the reference implementation and it includes a lot of things that we think 
should be provided as separate libraries. The other implementation from Youshido has some neat ideas, but unfortunately 
it does not fulfill the GraphQL specification. After doing some research, we concluded that neither of these
implementations met our needs, so we decided to start coding and see what we would come up with.

## Architecture

### Packages

#### Execution

Takes care of executing operation against a GraphQL schema.

**Package lead: [@hungneox](https://github.com/hungneox/)**

#### Resolving data

Resolving data is a very important part of any GraphQL implementation, so we spent quite a lot of time figuring out how 
we would solve this issue in a way that is easy to build on. The current plan is to use the 
[Front Controller pattern](https://en.wikipedia.org/wiki/Front_controller) and a front resolver which maps each 
operation to a corresponding resolver class, very much like a modern router would.

The way this work in practice is that we need to resolve the result for an operation we call the resolver method on
the front resolver after which it finds the correct resolver, calls it resolver and returns the result. This approach
also allows us to support middleware for resolver, which is very handy for e.g. transformation or authorization checks.   

### Language

Defines the GraphQL language and the associated AST.

**Package lead: [@crisu83](https://github.com/crisu83/)**

#### Parsing

We want to encourage developers to use the official GraphQL parser written in C++ through a PHP extension because its 
performance is outstanding. However, we will also provide a shim for the parser, which will allow developers to use 
this library without installing a custom PHP extension in their environment.

The official GraphQL parser takes a GQL string as its input and returns the corresponding Abstract Syntax Tree (AST), 
an associative array in PHP, as its output. Most of the GraphQL implementations (across all languages) take a
different approach where they convert the AST directly into nodes (class instances in PHP). While this approach might 
be a bit faster, it introduces tight coupling between the parser and the rest of the library, which we think is 
short-sighted. Instead we decided to take a different approach, where the parser produces the AST as an associative 
array. This allows developers to use the parser written in C++ if they want more performance.

#### AST representation

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

## Usage

TODO

## Contributing

TODO

## License

See [LICENCE](LICENSE).
