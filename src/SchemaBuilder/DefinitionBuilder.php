<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Execution\ValuesResolver;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use Psr\SimpleCache\CacheInterface;
use function Digia\GraphQL\Language\valueFromAST;
use function Digia\GraphQL\Type\assertNullableType;
use function Digia\GraphQL\Type\GraphQLDirective;
use function Digia\GraphQL\Type\GraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLUnionType;
use function Digia\GraphQL\Type\introspectionTypes;
use function Digia\GraphQL\Type\specifiedScalarTypes;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\keyValMap;

class DefinitionBuilder implements DefinitionBuilderInterface
{
    private const CACHE_PREFIX = 'GraphQL_DefinitionBuilder_';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ValuesResolver
     */
    protected $valuesResolver;

    /**
     * @var callable
     */
    protected $resolveTypeFunction;

    /**
     * @var array
     */
    protected $resolverMap;

    /**
     * @var array
     */
    protected $typeDefinitionsMap;

    /**
     * DefinitionBuilder constructor.
     *
     * @param callable       $resolveTypeFunction
     * @param CacheInterface $cache
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct(
        CacheInterface $cache,
        ValuesResolver $valuesResolver,
        ?callable $resolveTypeFunction = null
    ) {
        $this->valuesResolver      = $valuesResolver;
        $this->resolveTypeFunction = $resolveTypeFunction ?? [$this, 'defaultTypeResolver'];
        $this->typeDefinitionsMap  = [];

        $builtInTypes = keyMap(
            array_merge(specifiedScalarTypes(), introspectionTypes()),
            function (NamedTypeInterface $type) {
                return $type->getName();
            }
        );

        foreach ($builtInTypes as $name => $type) {
            $cache->set($this->getCacheKey($name), $type);
        }

        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function setTypeDefinitionMap(array $typeDefinitionMap): DefinitionBuilder
    {
        $this->typeDefinitionsMap = $typeDefinitionMap;
        return $this;
    }

    /**
     * @param array $resolverMap
     * @return DefinitionBuilder
     */
    public function setResolverMap(array $resolverMap): DefinitionBuilder
    {
        $this->resolverMap = $resolverMap;
        return $this;
    }

    /**
     * @param NamedTypeNode|TypeDefinitionNodeInterface $node
     * @inheritdoc
     */
    public function buildType(NodeInterface $node): TypeInterface
    {
        $typeName = $node->getNameValue();

        if (!$this->cache->has($this->getCacheKey($typeName))) {
            if ($node instanceof NamedTypeNode) {
                $definition = $this->getTypeDefinition($typeName);

                $this->cache->set(
                    $this->getCacheKey($typeName),
                    null !== $definition ? $this->buildNamedType($definition) : $this->resolveType($node)
                );
            } else {
                $this->cache->set($this->getCacheKey($typeName), $this->buildNamedType($node));
            }
        }

        return $this->cache->get($this->getCacheKey($typeName));
    }

    /**
     * @inheritdoc
     */
    public function buildDirective(DirectiveDefinitionNode $node): DirectiveInterface
    {
        return GraphQLDirective([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'locations'   => array_map(function (NameNode $node) {
                return $node->getValue();
            }, $node->getLocations()),
            'arguments'   => $node->hasArguments() ? $this->buildArguments($node->getArguments()) : [],
            'astNode'     => $node,
        ]);
    }

    /**
     * @param TypeNodeInterface $typeNode
     * @return TypeInterface
     * @throws InvariantException
     * @throws InvalidTypeException
     */
    protected function buildWrappedType(TypeNodeInterface $typeNode): TypeInterface
    {
        $typeDefinition = $this->buildType($this->getNamedTypeNode($typeNode));
        return buildWrappedType($typeDefinition, $typeNode);
    }

    /**
     * @param FieldDefinitionNode|InputValueDefinitionNode $node
     * @return array
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected function buildField($node, array $resolverMap): array
    {
        return [
            'type'              => $this->buildWrappedType($node->getType()),
            'description'       => $node->getDescriptionValue(),
            'arguments'         => $node->hasArguments() ? $this->buildArguments($node->getArguments()) : [],
            'deprecationReason' => $this->getDeprecationReason($node),
            'resolve'           => $resolverMap[$node->getNameValue()] ?? null,
            'astNode'           => $node,
        ];
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function buildArguments(array $nodes): array
    {
        return keyValMap(
            $nodes,
            function (InputValueDefinitionNode $value) {
                return $value->getNameValue();
            },
            function (InputValueDefinitionNode $value): array {
                $type = $this->buildWrappedType($value->getType());
                return [
                    'type'         => $type,
                    'description'  => $value->getDescriptionValue(),
                    'defaultValue' => valueFromAST($value->getDefaultValue(), $type),
                    'astNode'      => $value,
                ];
            });
    }

    /**
     * @param TypeDefinitionNodeInterface $node
     * @return NamedTypeInterface
     * @throws LanguageException
     */
    protected function buildNamedType(TypeDefinitionNodeInterface $node): NamedTypeInterface
    {
        if ($node instanceof ObjectTypeDefinitionNode) {
            return $this->buildObjectType($node);
        }
        if ($node instanceof InterfaceTypeDefinitionNode) {
            return $this->buildInterfaceType($node);
        }
        if ($node instanceof EnumTypeDefinitionNode) {
            return $this->buildEnumType($node);
        }
        if ($node instanceof UnionTypeDefinitionNode) {
            return $this->buildUnionType($node);
        }
        if ($node instanceof ScalarTypeDefinitionNode) {
            return $this->buildScalarType($node);
        }
        if ($node instanceof InputObjectTypeDefinitionNode) {
            return $this->buildInputObjectType($node);
        }

        throw new LanguageException(sprintf('Type kind "%s" not supported.', $node->getKind()));
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return ObjectType
     */
    protected function buildObjectType(ObjectTypeDefinitionNode $node): ObjectType
    {
        return GraphQLObjectType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'fields'      => function () use ($node) {
                return $this->buildFields($node);
            },
            'interfaces'  => function () use ($node) {
                return $node->hasInterfaces() ? array_map(function (NodeInterface $interface) {
                    return $this->buildType($interface);
                }, $node->getInterfaces()) : [];
            },
        ]);
    }

    /**
     * @param ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|InputObjectTypeDefinitionNode $node
     * @return array
     */
    protected function buildFields($node): array
    {
        $resolverMap = $this->resolverMap[$node->getNameValue()] ?? [];

        return $node->hasFields() ? keyValMap(
            $node->getFields(),
            function ($value) {
                /** @noinspection PhpUndefinedMethodInspection */
                return $value->getNameValue();
            },
            function ($value) use ($resolverMap) {
                return $this->buildField($value, $resolverMap);
            }
        ) : [];
    }

    /**
     * @param InterfaceTypeDefinitionNode $node
     * @return InterfaceType
     */
    protected function buildInterfaceType(InterfaceTypeDefinitionNode $node): InterfaceType
    {
        return GraphQLInterfaceType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'fields'      => function () use ($node): array {
                return $this->buildFields($node);
            },
            'astNode'     => $node,
        ]);
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return EnumType
     */
    protected function buildEnumType(EnumTypeDefinitionNode $node): EnumType
    {
        return GraphQLEnumType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'values'      => $node->hasValues() ? keyValMap(
                $node->getValues(),
                function (EnumValueDefinitionNode $value): string {
                    return $value->getNameValue();
                },
                function (EnumValueDefinitionNode $value): array {
                    return [
                        'description'       => $value->getDescriptionValue(),
                        'deprecationReason' => $this->getDeprecationReason($value),
                        'astNode'           => $value,
                    ];
                }
            ) : [],
            'astNode'     => $node,
        ]);
    }

    /**
     * @param UnionTypeDefinitionNode $node
     * @return UnionType
     */
    protected function buildUnionType(UnionTypeDefinitionNode $node): UnionType
    {
        return GraphQLUnionType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'types'       => $node->hasTypes() ? array_map(function (TypeNodeInterface $type) {
                return $this->buildType($type);
            }, $node->getTypes()) : [],
            'astNode'     => $node,
        ]);
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return ScalarType
     */
    protected function buildScalarType(ScalarTypeDefinitionNode $node): ScalarType
    {
        return GraphQLScalarType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'serialize'   => function ($value) {
                return $value;
            },
            'astNode'     => $node,
        ]);
    }

    /**
     * @param InputObjectTypeDefinitionNode $node
     * @return InputObjectType
     */
    protected function buildInputObjectType(InputObjectTypeDefinitionNode $node): InputObjectType
    {
        return GraphQLInputObjectType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'fields'      => $node->hasFields() ? function () use ($node) {
                return keyValMap(
                    $node->getFields(),
                    function (InputValueDefinitionNode $value): string {
                        return $value->getNameValue();
                    },
                    function (InputValueDefinitionNode $value): array {
                        $type = $this->buildWrappedType($value->getType());
                        return [
                            'type'         => $type,
                            'description'  => $value->getDescriptionValue(),
                            'defaultValue' => valueFromAST($value->getDefaultValue(), $type),
                            'astNode'      => $value,
                        ];
                    }
                );
            } : [],
            'astNode'     => $node,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function resolveType(NamedTypeNode $node): ?NamedTypeInterface
    {
        return \call_user_func($this->resolveTypeFunction, $node);
    }

    /**
     * @param NamedTypeNode $node
     * @return NamedTypeInterface|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function defaultTypeResolver(NamedTypeNode $node): ?NamedTypeInterface
    {
        return $this->cache->get($this->getCacheKey($node->getNameValue())) ?? null;
    }

    /**
     * @param string $typeName
     * @return TypeDefinitionNodeInterface|null
     */
    protected function getTypeDefinition(string $typeName): ?TypeDefinitionNodeInterface
    {
        return $this->typeDefinitionsMap[$typeName] ?? null;
    }

    /**
     * @param TypeNodeInterface $typeNode
     * @return NamedTypeNode
     */
    protected function getNamedTypeNode(TypeNodeInterface $typeNode): NamedTypeNode
    {
        $namedType = $typeNode;

        while ($namedType instanceof ListTypeNode || $namedType instanceof NonNullTypeNode) {
            $namedType = $namedType->getType();
        }

        return $namedType;
    }

    /**
     * @param NodeInterface|EnumValueDefinitionNode|FieldDefinitionNode $node
     * @return null|string
     * @throws InvariantException
     * @throws ExecutionException
     * @throws InvalidTypeException
     */
    protected function getDeprecationReason(NodeInterface $node): ?string
    {
        $deprecated = $this->valuesResolver->getDirectiveValues(GraphQLDeprecatedDirective(), $node);
        return $deprecated['reason'] ?? null;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        return self::CACHE_PREFIX . $key;
    }
}

/**
 * @param TypeInterface                        $innerType
 * @param NamedTypeInterface|TypeNodeInterface $inputTypeNode
 * @return TypeInterface
 * @throws InvariantException
 * @throws InvalidTypeException
 */
function buildWrappedType(TypeInterface $innerType, TypeNodeInterface $inputTypeNode): TypeInterface
{
    if ($inputTypeNode instanceof ListTypeNode) {
        return GraphQLList(buildWrappedType($innerType, $inputTypeNode->getType()));
    }

    if ($inputTypeNode instanceof NonNullTypeNode) {
        $wrappedType = buildWrappedType($innerType, $inputTypeNode->getType());
        return GraphQLNonNull(assertNullableType($wrappedType));
    }

    return $innerType;
}
