# Change log

## 1.1.0

* Add initial support for execution strategies
* Add support for error handling middleware 
* Fix some bugs in the error handling
* Fix some type-hint issues
* Run Travis CI tests on PHP 7.3 too, improve build times by caching

## 1.0.3

* Drastically reduce the number of container `make()` ([#332](https://github.com/digiaonline/graphql-php/pull/332))

## 1.0.2

* Expanded the README table of contents somewhat ([#329](https://github.com/digiaonline/graphql-php/pull/329))
* Don't validate the schema again while validating the query against it ([#328](https://github.com/digiaonline/graphql-php/pull/328))
* Fix some incorrect type-hints in `ExecutionResult` ([#327](https://github.com/digiaonline/graphql-php/pull/327))
* Fix resolver example in the README ([#313](https://github.com/digiaonline/graphql-php/pull/313))

## 1.0.1

* Fix a bug where you could not use `false` as value for `Boolean!` input types ([#311](https://github.com/digiaonline/graphql-php/pull/311))
* Add a code of conduct ([#312](https://github.com/digiaonline/graphql-php/pull/312))
* Fix resolver middleware example ([#309](https://github.com/digiaonline/graphql-php/pull/309))
* Introduce `VisitorInfo` concept ([#308](https://github.com/digiaonline/graphql-php/pull/308))

## 1.0.0

* Initial release
