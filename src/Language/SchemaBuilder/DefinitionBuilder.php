<?php

namespace Digia\GraphQL\Language\SchemaBuilder;

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

    /**
     * @var ?callable
     */
    protected $resolveTypeFunction;

    /**
     * @var CacheInterface
     */
    protected $cache;

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
    public function __construct(callable $resolveTypeFunction, CacheInterface $cache)
    {
        $this->typeDefinitionsMap  = [];
        $this->resolveTypeFunction = $resolveTypeFunction;

        $builtInTypes = keyMap(
            array_merge(specifiedScalarTypes(), introspectionTypes()),
            function (NamedTypeInterface $type) {
                return $type->getName();
            }
        );

        foreach ($builtInTypes as $name => $type) {
            $cache->set($name, $type);
        }

        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function setTypeDefinitionMap(array $typeDefinitionMap)
    {
        $this->typeDefinitionsMap = $typeDefinitionMap;
        return $this;
    }

    /**
     * @param NamedTypeNode|TypeDefinitionNodeInterface $node
     * @inheritdoc
     */
    public function buildType(NodeInterface $node): TypeInterface
    {
        $typeName = $node->getNameValue();

        if (!$this->cache->has($typeName)) {
            if ($node instanceof NamedTypeNode) {
                $definition = $this->getTypeDefinition($typeName);

                $this->cache->set(
                    $typeName,
                    null !== $definition ? $this->buildNamedType($definition) : $this->resolveType($node)
                );
            } else {
                $this->cache->set($typeName, $this->buildNamedType($node));
            }
        }

        return $this->cache->get($typeName);
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
        $typeDefinition = $this->buildType(getNamedTypeNode($typeNode));
        return buildWrappedType($typeDefinition, $typeNode);
    }

    /**
     * @param FieldDefinitionNode|InputValueDefinitionNode $node
     * @return array
     * @throws ExecutionException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected function buildField($node): array
    {
        return [
            'type'              => $this->buildWrappedType($node->getType()),
            'description'       => $node->getDescriptionValue(),
            'arguments'         => $node->hasArguments() ? $this->buildArguments($node->getArguments()) : [],
            'deprecationReason' => getDeprecationReason($node),
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
                return $node->hasInterfaces() ? array_map(function (InterfaceTypeDefinitionNode $interface) {
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
        return $node->hasFields() ? keyValMap(
            $node->getFields(),
            function ($value) {
                /** @noinspection PhpUndefinedMethodInspection */
                return $value->getNameValue();
            },
            function ($value) {
                return $this->buildField($value);
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
                        'deprecationReason' => getDeprecationReason($value),
                        'astNode'           => $value,
                    ];
                }
            ) : [],
            'astNode'     => $node,
        ]);
    }

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
            'fields'      => $node->hasFields() ? keyValMap(
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
                }) : [],
            'astNode'     => $node,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function resolveType(NodeInterface $node): TypeInterface
    {
        return \call_user_func($this->resolveTypeFunction, $node);
    }

    /**
     * @param string $typeName
     * @return TypeDefinitionNodeInterface|null
     */
    protected function getTypeDefinition(string $typeName): ?TypeDefinitionNodeInterface
    {
        return $this->typeDefinitionsMap[$typeName] ?? null;
    }
}

/**
 * @param TypeNodeInterface $typeNode
 * @return NamedTypeNode
 */
function getNamedTypeNode(TypeNodeInterface $typeNode): NamedTypeNode
{
    $namedType = $typeNode;

    while ($namedType instanceof ListTypeNode || $namedType instanceof NonNullTypeNode) {
        $namedType = $namedType->getType();
    }

    return $namedType;
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

/**
 * @param NodeInterface|EnumValueDefinitionNode|FieldDefinitionNode $node
 * @return null|string
 * @throws InvariantException
 * @throws ExecutionException
 * @throws InvalidTypeException
 */
function getDeprecationReason(NodeInterface $node): ?string
{
    $deprecated = (new ValuesResolver())->getDirectiveValues(GraphQLDeprecatedDirective(), $node);
    return $deprecated['reason'] ?? null;
}
