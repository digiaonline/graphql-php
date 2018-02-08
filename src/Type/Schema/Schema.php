<?php

namespace Digia\GraphQL\Type\Schema;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\SchemaDefinitionNode;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\Contract\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\Contract\WrappingTypeInterface;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Directive\DeprecatedDirective;
use Digia\GraphQL\Type\Directive\DirectiveInterface;
use Digia\GraphQL\Type\Directive\IncludeDirective;
use Digia\GraphQL\Type\Directive\SkipDirective;
use function Digia\GraphQL\Type\isWrappingType;
use function Digia\GraphQL\Util\invariant;

/**
 * Schema Definition
 * A Schema is created by supplying the root types of each type of operation,
 * query and mutation (optional). A schema definition is then supplied to the
 * validator and executor.
 * Example:
 *     const MyAppSchema = new GraphQLSchema({
 *       query: MyAppQueryRootType,
 *       mutation: MyAppMutationRootType,
 *     })
 * Note: If an array of `directives` are provided to GraphQLSchema, that will be
 * the exact list of directives represented and allowed. If `directives` is not
 * provided then a default set of the specified directives (e.g. @include and
 * @skip) will be used. If you wish to provide *additional* directives to these
 * specified directives, you must explicitly declare them. Example:
 *     const MyAppSchema = new GraphQLSchema({
 *       ...
 *       directives: specifiedDirectives.concat([ myCustomDirective ]),
 *     })
 */

/**
 * Class Schema
 *
 * @package Digia\GraphQL\Type
 * @property SchemaDefinitionNode $astNode
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
     * @var TypeInterface
     */
    private $types = [];

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

    /**
     * @inheritdoc
     */
    protected function beforeConfig(): void
    {
        $this->setDirectives([
            new IncludeDirective(),
            new SkipDirective(),
            new DeprecatedDirective(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function afterConfig(): void
    {
        $initialTypes = [
            $this->queryType,
            $this->mutationType,
            $this->subscriptionType,
        ];

        if ($this->types) {
            $initialTypes = array_merge($initialTypes, $this->types);
        }

        // Keep track of all types referenced within the schema.
        $typeMap = [];

        // First by deeply visiting all initial types.
        $typeMap = array_reduce($initialTypes, function ($map, $type) {
            return typeMapReducer($map, $type);
        }, $typeMap);

        // Then by deeply visiting all directive types.
        $typeMap = array_reduce($this->directives, function ($map, $directive) {
            return typeMapDirectiveReducer($map, $directive);
        }, $typeMap);

        // Storing the resulting map for reference by the schema.
        $this->typeMap = $typeMap;
    }

    /**
     * @param ObjectType $queryType
     * @return Schema
     */
    protected function setQueryType(ObjectType $queryType): Schema
    {
        $this->queryType = $queryType;

        return $this;
    }

    /**
     * @param ObjectType $mutationType
     * @return Schema
     */
    protected function setMutationType(ObjectType $mutationType): Schema
    {
        $this->mutationType = $mutationType;

        return $this;
    }

    /**
     * @param ObjectType $subscriptionType
     * @return Schema
     */
    protected function setSubscriptionType(ObjectType $subscriptionType): Schema
    {
        $this->subscriptionType = $subscriptionType;

        return $this;
    }

    /**
     * @param TypeInterface $types
     * @return Schema
     */
    public function setTypes(TypeInterface $types): Schema
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @param DirectiveInterface[] $directives
     * @return Schema
     */
    protected function setDirectives(array $directives): Schema
    {
        $this->directives = $directives;

        return $this;
    }

    /**
     * @param NamedTypeInterface[] $typeMap
     * @return Schema
     */
    protected function setTypeMap(array $typeMap): Schema
    {
        $this->typeMap = $typeMap;

        return $this;
    }

    /**
     * @param InterfaceType[] $implementations
     * @return Schema
     */
    protected function setImplementations(array $implementations): Schema
    {
        $this->implementations = $implementations;

        return $this;
    }

    /**
     * @param bool $assumeValid
     * @return Schema
     */
    protected function setAssumeValid(bool $assumeValid): Schema
    {
        $this->assumeValid = $assumeValid;

        return $this;
    }
}

/**
 * @param array         $map
 * @param TypeInterface $type
 * @return array
 * @throws \Exception
 */
function typeMapReducer(array $map, TypeInterface $type)
{
    if ($type instanceof WrappingTypeInterface) {
        return typeMapReducer($map, $type->getOfType());
    }

    invariant(
        isset($map[$type->getName()]),
        sprintf(
            'Schema must contain unique named types but contains multiple types named "%s".',
            $type->getName()
        )
    );

    $map[$type->getName()] = $type;

    $reducedMap = [];

    if ($type instanceof UnionType) {
        $reducedMap = array_reduce($type->getTypes(), function ($map, $type) {
            return typeMapReducer($map, $type);
        }, $reducedMap);
    }

    if ($type instanceof ObjectType) {
        $reducedMap = array_reduce($type->getInterfaces(), function ($map, $type) {
            return typeMapReducer($map, $type);
        }, $reducedMap);
    }

    if ($type instanceof ObjectType || $type instanceof InterfaceType) {
        foreach ($type->getFields() as $field) {
            if ($field->hasArguments()) {
                $fieldArgTypes = array_map(function (Argument $argument) {
                    return $argument->getType();
                }, $field->getArguments());

                $reducedMap = array_reduce($fieldArgTypes, function ($map, $type) {
                    return typeMapReducer($map, $type);
                }, $reducedMap);
            }
        }

        $reducedMap = typeMapReducer($reducedMap, $field->getType());
    }

    if ($type instanceof InputObjectType) {
        foreach ($type->getFields() as $field) {
            $reducedMap = typeMapReducer($reducedMap, $field->getType());
        }
    }

    return $reducedMap;
}

/**
 * @param array              $map
 * @param DirectiveInterface $directive
 * @return array
 */
function typeMapDirectiveReducer(array $map, DirectiveInterface $directive)
{
    return array_reduce($directive->getArguments(), function ($map, Argument $argument) {
        return typeMapReducer($map, $argument->getType());
    }, $map);
}
