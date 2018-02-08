<?php

namespace Digia\GraphQL\Type\Schema;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Type\Definition\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Directive\DirectiveInterface;

/**
 * Schema Definition
 *
 * A Schema is created by supplying the root types of each type of operation,
 * query and mutation (optional). A schema definition is then supplied to the
 * validator and executor.
 *
 * Example:
 *
 *     const MyAppSchema = new GraphQLSchema({
 *       query: MyAppQueryRootType,
 *       mutation: MyAppMutationRootType,
 *     })
 *
 * Note: If an array of `directives` are provided to GraphQLSchema, that will be
 * the exact list of directives represented and allowed. If `directives` is not
 * provided then a default set of the specified directives (e.g. @include and
 * @skip) will be used. If you wish to provide *additional* directives to these
 * specified directives, you must explicitly declare them. Example:
 *
 *     const MyAppSchema = new GraphQLSchema({
 *       ...
 *       directives: specifiedDirectives.concat([ myCustomDirective ]),
 *     })
 *
 */

/**
 * Class Schema
 *
 * @package Digia\GraphQL\Type
 * @codeCoverageIgnore
 */
class Schema
{

    use NodeTrait;
    use ConfigTrait;

    /**
     * @var ObjectType
     */
    private $queryType;

    /**
     * @var ObjectType
     */
    private $mutationType;

    /**
     * @var ObjectType
     */
    private $subscriptionType;

    /**
     * @var DirectiveInterface[]
     */
    private $directives = [];

    /**
     * @var NamedTypeInterface[]
     */
    private $typeMap = [];

    /**
     * @var InterfaceType[]
     */
    private $implementations = [];

    /**
     * @var bool
     */
    private $assumeValid = false;
}
