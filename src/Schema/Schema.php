<?php

namespace Digia\GraphQL\Schema;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\ASTNodeTrait;
use Digia\GraphQL\Language\Node\NameAwareInterface;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Definition\WrappingTypeInterface;
use function Digia\GraphQL\Type\__Schema;
use function Digia\GraphQL\Util\find;
use function Digia\GraphQL\Util\invariant;

/**
 * Schema Definition
 *
 * A Schema is created by supplying the root types of each type of operation,
 * query and mutation (optional). A schema definition is then supplied to the
 * validator and executor.
 *
 * Example:
 *
 *     $MyAppSchema = GraphQLSchema([
 *       'query'    => $MyAppQueryRootType,
 *       'mutation' => $MyAppMutationRootType,
 *     ])
 *
 * Note: If an array of `directives` are provided to GraphQLSchema, that will be
 * the exact list of directives represented and allowed. If `directives` is not
 * provided then a default set of the specified directives (e.g. @include and
 * @skip) will be used. If you wish to provide *additional* directives to these
 * specified directives, you must explicitly declare them. Example:
 *
 *     $MyAppSchema = GraphQLSchema([
 *       ...
 *       'directives' => \array_merge(specifiedDirectives(), [$myCustomDirective]),
 *     ])
 */
class Schema implements SchemaInterface, DefinitionInterface
{
    use ASTNodeTrait;

    /**
     * @var NamedTypeInterface|null
     */
    protected $queryType;

    /**
     * @var NamedTypeInterface|null
     */
    protected $mutationType;

    /**
     * @var NamedTypeInterface|null
     */
    protected $subscriptionType;

    /**
     * @var TypeInterface[]
     */
    protected $types = [];

    /**
     * @var array
     */
    protected $directives = [];

    /**
     * @var bool
     */
    protected $assumeValid = false;

    /**
     * @var array
     */
    protected $typeMap = [];

    /**
     * @var array
     */
    protected $implementations = [];

    /**
     * @var array
     */
    protected $possibleTypesMap = [];

    /**
     * Schema constructor.
     *
     * @param SchemaDefinitionNode   $astNode
     * @param TypeInterface|null     $queryType
     * @param TypeInterface|null     $mutationType
     * @param TypeInterface|null     $subscriptionType
     * @param TypeInterface[]        $types
     * @param Directive[]            $directives
     * @param bool                   $assumeValid
     * @param SchemaDefinitionNode[] $astNode
     * @throws InvariantException
     */
    public function __construct(
        ?TypeInterface $queryType,
        ?TypeInterface $mutationType,
        ?TypeInterface $subscriptionType,
        array $types,
        array $directives,
        bool $assumeValid,
        ?SchemaDefinitionNode $astNode
    ) {
        $this->queryType        = $queryType;
        $this->mutationType     = $mutationType;
        $this->subscriptionType = $subscriptionType;
        $this->types            = $types;
        $this->directives       = !empty($directives)
            ? $directives
            : specifiedDirectives();
        $this->assumeValid      = $assumeValid;
        $this->astNode          = $astNode;

        $this->buildTypeMap();
        $this->buildImplementations();
    }

    /**
     * @inheritdoc
     */
    public function getQueryType(): ?NamedTypeInterface
    {
        return $this->queryType;
    }

    /**
     * @inheritdoc
     */
    public function getMutationType(): ?NamedTypeInterface
    {
        return $this->mutationType;
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionType(): ?NamedTypeInterface
    {
        return $this->subscriptionType;
    }

    /**
     * @inheritdoc
     */
    public function getDirective(string $name): ?Directive
    {
        return find($this->directives, function (Directive $directive) use ($name) {
            return $directive->getName() === $name;
        });
    }

    /**
     * @inheritdoc
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * @inheritdoc
     */
    public function getTypeMap(): array
    {
        return $this->typeMap;
    }

    /**
     * @inheritdoc
     */
    public function getAssumeValid(): bool
    {
        return $this->assumeValid;
    }

    /**
     * @inheritdoc
     * @throws InvariantException
     */
    public function isPossibleType(AbstractTypeInterface $abstractType, TypeInterface $possibleType): bool
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $abstractTypeName = $abstractType->getName();
        /** @noinspection PhpUndefinedMethodInspection */
        $possibleTypeName = $possibleType->getName();

        if (!isset($this->possibleTypesMap[$abstractTypeName])) {
            $possibleTypes = $this->getPossibleTypes($abstractType);

            invariant(
                \is_array($possibleTypes),
                \sprintf(
                    'Could not find possible implementing types for %s ' .
                    'in schema. Check that schema.types is defined and is an array of ' .
                    'all possible types in the schema.',
                    $abstractTypeName
                )
            );

            $this->possibleTypesMap[$abstractTypeName] = \array_reduce(
                $possibleTypes,
                function (array $map, TypeInterface $type) {
                    /** @var NameAwareInterface $type */
                    $map[$type->getName()] = true;
                    return $map;
                },
                []
            );
        }

        return isset($this->possibleTypesMap[$abstractTypeName][$possibleTypeName]);
    }

    /**
     * @inheritdoc
     * @throws InvariantException
     */
    public function getPossibleTypes(AbstractTypeInterface $abstractType): ?array
    {
        if ($abstractType instanceof UnionType) {
            return $abstractType->getTypes();
        }

        return $this->implementations[$abstractType->getName()] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getType(string $name): ?TypeInterface
    {
        return $this->typeMap[$name] ?? null;
    }

    /**
     *
     */
    protected function buildTypeMap(): void
    {
        $initialTypes = [
            $this->queryType,
            $this->mutationType,
            $this->subscriptionType,
            __Schema(), // Introspection schema
        ];

        if ($this->types) {
            $initialTypes = \array_merge($initialTypes, $this->types);
        }

        // Keep track of all types referenced within the schema.
        $typeMap = [];

        // First by deeply visiting all initial types.
        $typeMap = \array_reduce($initialTypes, [$this, 'typeMapReducer'], $typeMap);

        // Then by deeply visiting all directive types.
        $typeMap = \array_reduce($this->directives, [$this, 'typeMapDirectiveReducer'], $typeMap);

        // Storing the resulting map for reference by the schema.
        $this->typeMap = $typeMap;
    }

    /**
     * @throws InvariantException
     */
    protected function buildImplementations()
    {
        $implementations = [];

        // Keep track of all implementations by interface name.
        foreach ($this->typeMap as $typeName => $type) {
            if ($type instanceof ObjectType) {
                foreach ($type->getInterfaces() as $interface) {
                    if (!($interface instanceof InterfaceType)) {
                        continue;
                    }

                    $interfaceName = $interface->getName();

                    if (!isset($implementations[$interfaceName])) {
                        $implementations[$interfaceName] = [];
                    }

                    $implementations[$interfaceName][] = $type;
                }
            }
        }

        $this->implementations = $implementations;
    }

    /**
     * @param array                                 $map
     * @param TypeInterface|NameAwareInterface|null $type
     * @return array
     * @throws InvariantException
     */
    protected function typeMapReducer(array $map, ?TypeInterface $type): array
    {
        if (null === $type) {
            return $map;
        }

        if ($type instanceof WrappingTypeInterface) {
            return $this->typeMapReducer($map, $type->getOfType());
        }

        $typeName = $type->getName();

        if (isset($map[$typeName])) {
            invariant(
                $type === $map[$typeName],
                \sprintf(
                    'Schema must contain unique named types but contains multiple types named "%s".',
                    $type->getName()
                )
            );

            return $map;
        }

        $map[$typeName] = $type;

        $reducedMap = $map;

        if ($type instanceof UnionType) {
            $reducedMap = \array_reduce($type->getTypes(), [$this, 'typeMapReducer'], $reducedMap);
        }

        if ($type instanceof ObjectType) {
            $reducedMap = \array_reduce($type->getInterfaces(), [$this, 'typeMapReducer'], $reducedMap);
        }

        if ($type instanceof ObjectType || $type instanceof InterfaceType) {
            foreach ($type->getFields() as $field) {
                if ($field->hasArguments()) {
                    $fieldArgTypes = \array_map(function (Argument $argument) {
                        return $argument->getType();
                    }, $field->getArguments());

                    $reducedMap = \array_reduce($fieldArgTypes, [$this, 'typeMapReducer'], $reducedMap);
                }

                $reducedMap = $this->typeMapReducer($reducedMap, $field->getType());
            }
        }

        if ($type instanceof InputObjectType) {
            foreach ($type->getFields() as $field) {
                $reducedMap = $this->typeMapReducer($reducedMap, $field->getType());
            }
        }

        return $reducedMap;
    }

    /**
     * Note: We do not type-hint the `$directive`, because we want the `SchemaValidator` to catch these errors.
     *
     * @param array      $map
     * @param mixed|null $directive
     * @return array
     */
    protected function typeMapDirectiveReducer(array $map, $directive): array
    {
        if (!($directive instanceof Directive) ||
            ($directive instanceof Directive && !$directive->hasArguments())) {
            return $map;
        }

        return \array_reduce($directive->getArguments(), function ($map, Argument $argument) {
            return $this->typeMapReducer($map, $argument->getType());
        }, $map);
    }
}
