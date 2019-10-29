<?php

namespace Digia\GraphQL\Schema;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Execution\ExecutionException;
use Digia\GraphQL\Execution\ValuesResolver;
use Digia\GraphQL\Language\LanguageException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\EnumTypeDefinitionNode;
use Digia\GraphQL\Language\Node\EnumValueDefinitionNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\InputObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ListTypeNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NamedTypeNodeInterface;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\NonNullTypeNode;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ScalarTypeDefinitionNode;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Language\Node\UnionTypeDefinitionNode;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Util\ValueASTConverter;
use function Digia\GraphQL\Type\introspectionTypes;
use function Digia\GraphQL\Type\newDirective;
use function Digia\GraphQL\Type\newEnumType;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newScalarType;
use function Digia\GraphQL\Type\newUnionType;
use function Digia\GraphQL\Type\specifiedScalarTypes;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\keyValueMap;

class DefinitionBuilder implements DefinitionBuilderInterface
{
    /**
     * @var NamedTypeNodeInterface[]
     */
    protected $typeDefinitionsMap;

    /**
     * @var ResolverRegistryInterface
     */
    protected $resolverRegistry;

    /**
     * @var callable
     */
    protected $resolveTypeFunction;

    /**
     * @var NamedTypeInterface[]
     */
    protected $types;

    /**
     * @var Directive[]
     */
    protected $directives;

    /**
     * DefinitionBuilder constructor.
     * @param array                          $typeDefinitionsMap
     * @param ResolverRegistryInterface|null $resolverRegistry
     * @param array                          $types
     * @param array                          $directives
     * @param callable|null                  $resolveTypeCallback
     */
    public function __construct(
        array $typeDefinitionsMap,
        ?ResolverRegistryInterface $resolverRegistry = null,
        array $types = [],
        array $directives = [],
        ?callable $resolveTypeCallback = null
    ) {
        $this->typeDefinitionsMap  = $typeDefinitionsMap;
        $this->resolverRegistry    = $resolverRegistry;
        $this->resolveTypeFunction = $resolveTypeCallback ?? [$this, 'defaultTypeResolver'];

        $this->registerTypes($types);
        $this->registerDirectives($directives);
    }

    /**
     * @inheritdoc
     */
    public function buildTypes(array $nodes): array
    {
        return \array_map(function (NamedTypeNodeInterface $node) {
            return $this->buildType($node);
        }, $nodes);
    }

    /**
     * @inheritdoc
     */
    public function buildType(NamedTypeNodeInterface $node): NamedTypeInterface
    {
        $typeName = $node->getNameValue();

        if (isset($this->types[$typeName])) {
            return $this->types[$typeName];
        }

        if ($node instanceof NamedTypeNode) {
            $definition = $this->getTypeDefinition($typeName);

            /** @noinspection PhpUnhandledExceptionInspection */
            $type = null !== $definition
                ? $this->buildNamedType($definition)
                : $this->resolveType($node);
        } else {
            /** @noinspection PhpUnhandledExceptionInspection */
            $type = $this->buildNamedType($node);
        }

        return $this->types[$typeName] = $type;
    }

    /**
     * @inheritdoc
     */
    public function buildDirective(DirectiveDefinitionNode $node): Directive
    {
        $directiveName = $node->getNameValue();

        if (isset($this->directives[$directiveName])) {
            return $this->directives[$directiveName];
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $directive = newDirective([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'locations'   => \array_map(function (NameNode $node) {
                return $node->getValue();
            }, $node->getLocations()),
            'args'        => $node->hasArguments() ? $this->buildArguments($node->getArguments()) : [],
            'astNode'     => $node,
        ]);

        return $this->directives[$directiveName] = $directive;
    }

    /**
     * @inheritdoc
     */
    public function buildField($node, ?callable $resolve = null): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return [
            'type'              => $this->buildWrappedType($node->getType()),
            'description'       => $node->getDescriptionValue(),
            'args'              => $node->hasArguments() ? $this->buildArguments($node->getArguments()) : [],
            'deprecationReason' => $this->getDeprecationReason($node),
            'resolve'           => $resolve,
            'astNode'           => $node,
        ];
    }

    /**
     * @param TypeNodeInterface $typeNode
     * @return TypeInterface
     * @throws InvariantException
     * @throws InvalidTypeException
     */
    protected function buildWrappedType(TypeNodeInterface $typeNode): TypeInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $typeDefinition = $this->buildType($this->getNamedTypeNode($typeNode));
        return $this->buildWrappedTypeRecursive($typeDefinition, $typeNode);
    }

    /**
     * @param NamedTypeInterface $innerType
     * @param TypeNodeInterface  $inputTypeNode
     * @return TypeInterface
     * @throws InvariantException
     * @throws InvalidTypeException
     */
    protected function buildWrappedTypeRecursive(
        NamedTypeInterface $innerType,
        TypeNodeInterface $inputTypeNode
    ): TypeInterface {
        if ($inputTypeNode instanceof ListTypeNode) {
            return newList($this->buildWrappedTypeRecursive($innerType, $inputTypeNode->getType()));
        }

        if ($inputTypeNode instanceof NonNullTypeNode) {
            return newNonNull($this->buildWrappedTypeRecursive($innerType, $inputTypeNode->getType()));
        }

        return $innerType;
    }

    /**
     * @param array $customTypes
     */
    protected function registerTypes(array $customTypes)
    {
        $typesMap = keyMap(
            \array_merge($customTypes, specifiedScalarTypes(), introspectionTypes()),
            function (NamedTypeInterface $type) {
                return $type->getName();
            }
        );

        foreach ($typesMap as $typeName => $type) {
            $this->types[$typeName] = $type;
        }
    }

    /**
     * @param array $customDirectives
     */
    protected function registerDirectives(array $customDirectives)
    {
        $directivesMap = keyMap(
            \array_merge($customDirectives, specifiedDirectives()),
            function (Directive $directive) {
                return $directive->getName();
            }
        );

        foreach ($directivesMap as $directiveName => $directive) {
            $this->directives[$directiveName] = $directive;
        }
    }

    /**
     * @param array $nodes
     * @return array
     */
    protected function buildArguments(array $nodes): array
    {
        return keyValueMap(
            $nodes,
            function (InputValueDefinitionNode $value) {
                return $value->getNameValue();
            },
            function (InputValueDefinitionNode $value): array {
                $type         = $this->buildWrappedType($value->getType());
                $defaultValue = $value->getDefaultValue();
                return [
                    'type'         => $type,
                    'description'  => $value->getDescriptionValue(),
                    'defaultValue' => null !== $defaultValue
                        ? ValueASTConverter::convert($defaultValue, $type)
                        : null,
                    'astNode'      => $value,
                ];
            });
    }

    /**
     * @param TypeNodeInterface $node
     * @return NamedTypeInterface
     * @throws LanguageException
     */
    protected function buildNamedType(TypeNodeInterface $node): NamedTypeInterface
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
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->buildInputObjectType($node);
        }

        throw new LanguageException(\sprintf('Type kind "%s" not supported.', $node->getKind()));
    }

    /**
     * @param ObjectTypeDefinitionNode $node
     * @return ObjectType
     */
    protected function buildObjectType(ObjectTypeDefinitionNode $node): ObjectType
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return newObjectType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'fields'      => $node->hasFields() ? function () use ($node) {
                return $this->buildFields($node);
            } : [],
            // Note: While this could make early assertions to get the correctly
            // typed values, that would throw immediately while type system
            // validation with validateSchema() will produce more actionable results.
            'interfaces'  => function () use ($node) {
                return $node->hasInterfaces() ? \array_map(function (NamedTypeNodeInterface $interface) {
                    return $this->buildType($interface);
                }, $node->getInterfaces()) : [];
            },
            'astNode'     => $node,
        ]);
    }

    /**
     * @param ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|InputObjectTypeDefinitionNode $node
     * @return array
     */
    protected function buildFields($node): array
    {
        return keyValueMap(
            $node->getFields(),
            function ($value) {
                /** @var FieldDefinitionNode|InputValueDefinitionNode $value */
                return $value->getNameValue();
            },
            function ($value) use ($node) {
                /** @var FieldDefinitionNode|InputValueDefinitionNode $value */
                return $this->buildField($value,
                    $this->getFieldResolver($node->getNameValue(), $value->getNameValue()));
            }
        );
    }

    /**
     * @param string $typeName
     * @param string $fieldName
     * @return callable|null
     */
    protected function getFieldResolver(string $typeName, string $fieldName): ?callable
    {
        return null !== $this->resolverRegistry
            ? $this->resolverRegistry->getFieldResolver($typeName, $fieldName)
            : null;
    }

    /**
     * @param InterfaceTypeDefinitionNode $node
     * @return InterfaceType
     */
    protected function buildInterfaceType(InterfaceTypeDefinitionNode $node): InterfaceType
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return newInterfaceType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'fields'      => $node->hasFields() ? function () use ($node): array {
                return $this->buildFields($node);
            } : [],
            'resolveType' => $this->getTypeResolver($node->getNameValue()),
            'astNode'     => $node,
        ]);
    }

    /**
     * @param EnumTypeDefinitionNode $node
     * @return EnumType
     */
    protected function buildEnumType(EnumTypeDefinitionNode $node): EnumType
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return newEnumType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'values'      => $node->hasValues() ? keyValueMap(
                $node->getValues(),
                function (EnumValueDefinitionNode $value): ?string {
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
        /** @noinspection PhpUnhandledExceptionInspection */
        return newUnionType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'types'       => $node->hasTypes() ? \array_map(function (NamedTypeNodeInterface $type) {
                return $this->buildType($type);
            }, $node->getTypes()) : [],
            'resolveType' => $this->getTypeResolver($node->getNameValue()),
            'astNode'     => $node,
        ]);
    }

    /**
     * @param string $typeName
     * @return callable|null
     */
    protected function getTypeResolver(string $typeName): ?callable
    {
        return null !== $this->resolverRegistry
            ? $this->resolverRegistry->getTypeResolver($typeName)
            : null;
    }

    /**
     * @param ScalarTypeDefinitionNode $node
     * @return ScalarType
     */
    protected function buildScalarType(ScalarTypeDefinitionNode $node): ScalarType
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return newScalarType([
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
     * @throws InvariantException
     */
    protected function buildInputObjectType(InputObjectTypeDefinitionNode $node): InputObjectType
    {
        return newInputObjectType([
            'name'        => $node->getNameValue(),
            'description' => $node->getDescriptionValue(),
            'fields'      => $node->hasFields() ? function () use ($node) {
                return keyValueMap(
                    $node->getFields(),
                    function (InputValueDefinitionNode $value): ?string {
                        return $value->getNameValue();
                    },
                    function (InputValueDefinitionNode $value): array {
                        $type         = $this->buildWrappedType($value->getType());
                        $defaultValue = $value->getDefaultValue();
                        return [
                            'type'         => $type,
                            'description'  => $value->getDescriptionValue(),
                            'defaultValue' => null !== $defaultValue
                                ? ValueASTConverter::convert($defaultValue, $type)
                                : null,
                            'astNode'      => $value,
                        ];
                    }
                );
            } : [],
            'astNode'     => $node,
        ]);
    }

    /**
     * @param NamedTypeNode $node
     * @return NamedTypeInterface
     */
    protected function resolveType(NamedTypeNode $node): NamedTypeInterface
    {
        return \call_user_func($this->resolveTypeFunction, $node);
    }

    /**
     * @param NamedTypeNode $node
     * @return NamedTypeInterface|null
     */
    public function defaultTypeResolver(NamedTypeNode $node): ?NamedTypeInterface
    {
        return $this->types[$node->getNameValue()] ?? null;
    }

    /**
     * @param string $typeName
     * @return NamedTypeNodeInterface|null
     */
    protected function getTypeDefinition(string $typeName): ?NamedTypeNodeInterface
    {
        return $this->typeDefinitionsMap[$typeName] ?? null;
    }

    /**
     * @param TypeNodeInterface $typeNode
     * @return NamedTypeNodeInterface
     */
    protected function getNamedTypeNode(TypeNodeInterface $typeNode): NamedTypeNodeInterface
    {
        $namedType = $typeNode;

        while ($namedType instanceof ListTypeNode || $namedType instanceof NonNullTypeNode) {
            $namedType = $namedType->getType();
        }

        /** @var NamedTypeNodeInterface $namedType */
        return $namedType;
    }

    /**
     * @param NodeInterface|EnumValueDefinitionNode|FieldDefinitionNode $node
     * @return null|string
     * @throws InvariantException
     * @throws ExecutionException
     */
    protected function getDeprecationReason(NodeInterface $node): ?string
    {
        if (isset($this->directives['deprecated'])) {
            $deprecated = ValuesResolver::coerceDirectiveValues($this->directives['deprecated'], $node);

            return $deprecated['reason'] ?? null;
        }

        return null;
    }
}
