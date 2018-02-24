# GraphQL

[![Build Status](https://travis-ci.org/digiaonline/graphql-php.svg?branch=master)](https://travis-ci.org/digiaonline/graphql-php)
[![Coverage Status](https://coveralls.io/repos/github/digiaonline/graphql-php/badge.svg?branch=master)](https://coveralls.io/github/digiaonline/graphql-php?branch=master)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/digiaonline/graphql-php/master/LICENSE)

A PHP7 implementation of the [GraphQL specification](http://facebook.github.io/graphql/).

## Requirements

- PHP version >= 7.1

## Motivation

When we started developing this project there was two GraphQL implementations available for PHP developers; one from 
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

### Language

Defines the GraphQL language and the associated AST.

**Package lead: [@crisu83](https://github.com/crisu83/)**

#### Parsing

The plan is to encourage developers to use the official GraphQL parser written C++ through a PHP extension because
its performance is outstanding. However, we will also provide a shim for the parser, which allows developers to use 
this library without installing a custom PHP extension in their environment.

The official GraphQL parser takes a GQL string as its input and returns the corresponding Abstract Syntax Tree (AST), 
an associative array in PHP, as its output. Most of the GraphQL implementations (across all languages) takes a
different approach where they convert the AST directly into nodes (class instances in PHP). While this approach might 
be a bit faster, it introduces tight-coupling between the parser and the rest of the library, which we think is 
short-sighted. Instead we decided to take a different approach, where the parser produces the AST as an associative 
array. This allows the developers to use the parser written in C++ if they want more performance.

#### AST representation

We introduced a builder system, using the [Builder pattern](https://en.wikipedia.org/wiki/Builder_pattern),
for converting the AST into nodes which allows developers to implement their own builders, without changing our code.

### Type system

Describes the GraphQL type system and schema definition.

**Package lead: [@crisu83](https://github.com/crisu83/)**

#### Schema definition

Most of the existing GraphQL implementations require the developer to create the schema programmatically. However,
GraphQL has an experimental featured which lets you define your schema using its schema definition language. We think
that this is the natural way to define the schema, so we built type system around this idea. This approach will also 
allow developers to define the schema in formats native to different ecosystems, such as PHP array, YAML and even XML.

## Usage

TODO

## Contributing

TODO

## License

See [LICENCE](LICENSE).
