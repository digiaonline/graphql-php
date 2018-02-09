<?php

namespace Digia\GraphQL\Type\Schema;

use Digia\GraphQL\Behavior\ConfigTrait;
use Digia\GraphQL\Language\AST\Node\NodeTrait;
use Digia\GraphQL\Language\AST\Node\SchemaDefinitionNode;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\Contract\AbstractTypeInterface;
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
    private $query;

    /**
     * @var ObjectType
     */
    private $mutation;

    /**
     * @var ObjectType
     */
    private $subscription;

    /**
     * @var TypeInterface
     */
    private $types = [];

    /**
     * @var array
     */
    private $directives = [];

    /**
     * @var bool
     */
    private $assumeValid = false;

    /**
     * @var array
     */
    private $_typeMap = [];

    /**
     * @var array
     */
    private $_implementations = [];

    /**
     * @var array
     */
    private $_possibleTypeMap = [];

    /**
     * @return ObjectType
     */
    public function getQuery(): ObjectType
    {
        return $this->query;
    }

    /**
     * @return ObjectType
     */
    public function getMutation(): ObjectType
    {
        return $this->mutation;
    }

    /**
     * @return ObjectType
     */
    public function getSubscription(): ObjectType
    {
        return $this->subscription;
    }

    /**
     * @return NamedTypeInterface[]
     */
    public function getTypeMap(): array
    {
        return $this->_typeMap;
    }

    /**
     * @return bool
     */
    public function getAssumeValid(): bool
    {
        return $this->assumeValid;
    }

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
     * @return Schema
     */
    public function build(): Schema
    {
        $initialTypes = [
            $this->query,
            $this->mutation,
            $this->subscription,
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
        $this->_typeMap = $typeMap;

        // Keep track of all implementations by interface name.
        foreach ($this->_typeMap as $typeName => $type) {
            if ($type instanceof ObjectType) {
                foreach ($type->getInterfaces() as $interface) {
                    $interfaceName = $interface->getName();

                    if (!isset($this->_implementations[$interfaceName])) {
                        $this->_implementations[$interfaceName] = [];
                    }

                    $this->_implementations[$interfaceName][] = $type;
                }
            }
        }

        return $this;
    }

    /**
     * @param AbstractTypeInterface $abstractType
     * @param TypeInterface         $possibleType
     * @return bool
     * @throws \Exception
     */
    public function isPossibleType(AbstractTypeInterface $abstractType, TypeInterface $possibleType): bool
    {
        $abstractTypeName = $abstractType->getName();
        $possibleTypeName = $possibleType->getName();

        if (!isset($this->_possibleTypeMap[$abstractTypeName])) {
            $possibleTypes = $this->getPossibleTypes($abstractType);

            invariant(
                is_array($possibleTypes),
                sprintf(
                    'Could not find possible implementing types for %s ' .
                    'in schema. Check that schema.types is defined and is an array of ' .
                    'all possible types in the schema.',
                    $abstractTypeName
                )
            );

            $this->_possibleTypeMap = array_reduce($possibleTypes, function (array $map, TypeInterface $type) {
                $map[$type->getName()] = true;
                return $map;
            });
        }

        return isset($this->_possibleTypeMap[$abstractTypeName][$possibleTypeName]);
    }

    /**
     * @param AbstractTypeInterface $abstractType
     * @return null|TypeInterface[]
     */
    protected function getPossibleTypes(AbstractTypeInterface $abstractType): ?array
    {
        if ($abstractType instanceof UnionType) {
            return $abstractType->getTypes();
        }

        return $this->_implementations[$abstractType->getName()] ?? null;
    }

    /**
     * @param ObjectType $query
     * @return Schema
     */
    protected function setQuery(ObjectType $query): Schema
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param ObjectType $mutation
     * @return Schema
     */
    protected function setMutation(ObjectType $mutation): Schema
    {
        $this->mutation = $mutation;

        return $this;
    }

    /**
     * @param ObjectType $subscription
     * @return Schema
     */
    protected function setSubscription(ObjectType $subscription): Schema
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * @param array $types
     * @return Schema
     */
    public function setTypes(array $types): Schema
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
 * @param array              $map
 * @param null|TypeInterface $type
 * @return array
 * @throws \Exception
 */
function typeMapReducer(array $map, ?TypeInterface $type): array
{
    if (!$type) {
        return $map;
    }

    if ($type instanceof WrappingTypeInterface) {
        return typeMapReducer($map, $type->getOfType());
    }

    if (isset($map[$type->getName()])) {
        invariant(
            $map[$type->getName()] instanceof $type,
            sprintf(
                'Schema must contain unique named types but contains multiple types named "%s".',
                $type->getName()
            )
        );

        return $map;
    }

    $map[$type->getName()] = $type;

    $reducedMap = $map;

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
            if ($field->hasArgs()) {
                $fieldArgTypes = array_map(function (Argument $argument) {
                    return $argument->getType();
                }, $field->getArgs());

                $reducedMap = array_reduce($fieldArgTypes, function ($map, $type) {
                    return typeMapReducer($map, $type);
                }, $reducedMap);
            }

            $reducedMap = typeMapReducer($reducedMap, $field->getType());
        }
    }

    if ($type instanceof InputObjectType) {
        foreach ($type->getFields() as $field) {
            $reducedMap = typeMapReducer($reducedMap, $field->getType());
        }
    }

    return $reducedMap;
}

/**
 * @param array                    $map
 * @param null| DirectiveInterface $directive
 * @return array
 */
function typeMapDirectiveReducer(array $map, ?DirectiveInterface $directive): array
{
    if (!$directive || !$directive->hasArgs()) {
        return $map;
    }

    return array_reduce($directive->getArgs(), function ($map, Argument $argument) {
        return typeMapReducer($map, $argument->getType());
    }, $map);
}
