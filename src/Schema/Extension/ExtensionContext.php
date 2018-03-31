<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Error\ExtensionException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\EnumTypeExtensionNode;
use Digia\GraphQL\Language\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\ScalarTypeExtensionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\UnionTypeExtensionNode;
use Digia\GraphQL\Schema\DefinitionBuilderCreatorInterface;
use Digia\GraphQL\Schema\DefinitionBuilderInterface;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Schema\SchemaInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function Digia\GraphQL\Type\newGraphQLInterfaceType;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLNonNull;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLUnionType;
use function Digia\GraphQL\Type\isIntrospectionType;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\toString;

class ExtensionContext implements ExtensionContextInterface
{
    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var DocumentNode
     */
    protected $document;

    /**
     * @var DefinitionBuilderCreatorInterface
     */
    protected $definitionBuilderCreator;

    /**
     * @var DefinitionBuilderInterface
     */
    protected $definitionBuilder;

    /**
     * @var TypeDefinitionNodeInterface[]
     */
    protected $typeDefinitionMap;

    /**
     * @var ObjectTypeExtensionNode[][]|InterfaceTypeExtensionNode[][]
     */
    protected $typeExtensionsMap;

    /**
     * @var DirectiveDefinitionNode[]
     */
    protected $directiveDefinitions;

    /**
     * @var NamedTypeInterface[]
     */
    protected $extendTypeCache;

    /**
     * ExtensionContext constructor.
     * @param SchemaInterface                   $schema
     * @param DocumentNode                      $document
     * @param DefinitionBuilderCreatorInterface $definitionBuilderCreator
     * @throws ExtensionException
     */
    public function __construct(
        SchemaInterface $schema,
        DocumentNode $document,
        DefinitionBuilderCreatorInterface $definitionBuilderCreator
    ) {
        $this->schema                   = $schema;
        $this->document                 = $document;
        $this->definitionBuilderCreator = $definitionBuilderCreator;
        $this->extendTypeCache          = [];
    }

    /**
     * @throws ExtensionException
     */
    public function boot(): void
    {
        $this->extendDefinitions();

        $this->definitionBuilder = $this->createDefinitionBuilder();
    }

    /**
     * @return bool
     */
    public function isSchemaExtended(): bool
    {
        return
            !empty(\array_keys($this->typeExtensionsMap)) ||
            !empty(\array_keys($this->typeDefinitionMap)) ||
            !empty($this->directiveDefinitions);
    }

    /**
     * @return TypeInterface|null
     * @throws InvalidArgumentException
     * @throws InvariantException
     */
    public function getExtendedQueryType(): ?TypeInterface
    {
        $existingQueryType = $this->schema->getQueryType();

        return null !== $existingQueryType
            ? $this->getExtendedType($existingQueryType)
            : null;
    }

    /**
     * @return TypeInterface|null
     * @throws InvalidArgumentException
     * @throws InvariantException
     */
    public function getExtendedMutationType(): ?TypeInterface
    {
        $existingMutationType = $this->schema->getMutationType();

        return null !== $existingMutationType
            ? $this->getExtendedType($existingMutationType)
            : null;
    }

    /**
     * @return TypeInterface|null
     * @throws InvalidArgumentException
     * @throws InvariantException
     */
    public function getExtendedSubscriptionType(): ?TypeInterface
    {
        $existingSubscriptionType = $this->schema->getSubscriptionType();

        return null !== $existingSubscriptionType
            ? $this->getExtendedType($existingSubscriptionType)
            : null;
    }

    /**
     * @return TypeInterface[]
     */
    public function getExtendedTypes(): array
    {
        return \array_merge(
            \array_map(function ($type) {
                return $this->getExtendedType($type);
            }, \array_values($this->schema->getTypeMap())),
            $this->definitionBuilder->buildTypes(\array_values($this->typeDefinitionMap))
        );
    }

    /**
     * @return Directive[]
     * @throws InvariantException
     */
    public function getExtendedDirectives(): array
    {
        $existingDirectives = $this->schema->getDirectives();

        invariant(!empty($existingDirectives), 'schema must have default directives');

        return \array_merge(
            $existingDirectives,
            \array_map(function (DirectiveDefinitionNode $node) {
                return $this->definitionBuilder->buildDirective($node);
            }, $this->directiveDefinitions)
        );
    }

    /**
     * @param NamedTypeNode $node
     * @return TypeInterface|null
     * @throws ExtensionException
     * @throws InvalidArgumentException
     * @throws InvariantException
     */
    public function resolveType(NamedTypeNode $node): ?TypeInterface
    {
        $typeName     = $node->getNameValue();
        $existingType = $this->schema->getType($typeName);

        if (null !== $existingType) {
            return $this->getExtendedType($existingType);
        }

        throw new ExtensionException(
            \sprintf(
                'Unknown type: "%s". Ensure that this type exists ' .
                'either in the original schema, or is added in a type definition.',
                $typeName
            ),
            [$node]
        );
    }

    /**
     * @throws ExtensionException
     */
    protected function extendDefinitions(): void
    {
        $typeDefinitionMap    = [];
        $typeExtensionsMap    = [];
        $directiveDefinitions = [];

        foreach ($this->document->getDefinitions() as $definition) {
            if ($definition instanceof TypeDefinitionNodeInterface) {
                // Sanity check that none of the defined types conflict with the schema's existing types.
                $typeName     = $definition->getNameValue();
                $existingType = $this->schema->getType($typeName);

                if (null !== $existingType) {
                    throw new ExtensionException(
                        \sprintf(
                            'Type "%s" already exists in the schema. It cannot also ' .
                            'be defined in this type definition.',
                            $typeName
                        ),
                        [$definition]
                    );
                }

                $typeDefinitionMap[$typeName] = $definition;

                continue;
            }

            if ($definition instanceof ObjectTypeExtensionNode || $definition instanceof InterfaceTypeExtensionNode) {
                // Sanity check that this type extension exists within the schema's existing types.
                $extendedTypeName = $definition->getNameValue();
                $existingType     = $this->schema->getType($extendedTypeName);

                if (null === $existingType) {
                    throw new ExtensionException(
                        \sprintf(
                            'Cannot extend type "%s" because it does not exist in the existing schema.',
                            $extendedTypeName
                        ),
                        [$definition]
                    );
                }

                $this->checkExtensionNode($existingType, $definition);

                $typeExtensionsMap[$extendedTypeName] = \array_merge(
                    $typeExtensionsMap[$extendedTypeName] ?? [],
                    [$definition]
                );

                continue;
            }

            if ($definition instanceof DirectiveDefinitionNode) {
                $directiveName     = $definition->getNameValue();
                $existingDirective = $this->schema->getDirective($directiveName);

                if (null !== $existingDirective) {
                    throw new ExtensionException(
                        \sprintf(
                            'Directive "%s" already exists in the schema. It cannot be redefined.',
                            $directiveName
                        ),
                        [$definition]
                    );
                }

                $directiveDefinitions[] = $definition;

                continue;
            }

            if ($definition instanceof ScalarTypeExtensionNode ||
                $definition instanceof UnionTypeExtensionNode ||
                $definition instanceof EnumTypeExtensionNode ||
                $definition instanceof InputObjectTypeExtensionNode) {
                throw new ExtensionException(
                    \sprintf('The %s kind is not yet supported by extendSchema().', $definition->getKind())
                );
            }
        }

        $this->typeDefinitionMap    = $typeDefinitionMap;
        $this->typeExtensionsMap    = $typeExtensionsMap;
        $this->directiveDefinitions = $directiveDefinitions;
    }

    /**
     * @return DefinitionBuilderInterface
     */
    protected function createDefinitionBuilder(): DefinitionBuilderInterface
    {
        return $this->definitionBuilderCreator->create($this->typeDefinitionMap, [$this, 'resolveType']);
    }

    /**
     * @param TypeInterface $type
     * @param NodeInterface $node
     * @throws ExtensionException
     */
    protected function checkExtensionNode(TypeInterface $type, NodeInterface $node): void
    {
        if ($node instanceof ObjectTypeExtensionNode && !($type instanceof ObjectType)) {
            throw new ExtensionException(
                \sprintf('Cannot extend non-object type "%s".', toString($type)),
                [$node]
            );
        }

        if ($node instanceof InterfaceTypeExtensionNode && !($type instanceof InterfaceType)) {
            throw new ExtensionException(
                \sprintf('Cannot extend non-interface type "%s".', toString($type)),
                [$node]
            );
        }
    }

    /**
     * @param TypeInterface $type
     * @return TypeInterface
     * @throws InvalidArgumentException
     * @throws InvariantException
     */
    protected function getExtendedType(TypeInterface $type): TypeInterface
    {
        $typeName = $type->getName();

        if (!isset($this->extendTypeCache[$typeName])) {
            $this->extendTypeCache[$typeName] = $this->extendType($type);
        }

        return $this->extendTypeCache[$typeName];
    }

    /**
     * @param TypeInterface $type
     * @return TypeInterface
     * @throws InvariantException
     */
    protected function extendType(TypeInterface $type): TypeInterface
    {
        /** @noinspection PhpParamsInspection */
        if (isIntrospectionType($type)) {
            // Introspection types are not extended.
            return $type;
        }

        if ($type instanceof ObjectType) {
            return $this->extendObjectType($type);
        }

        if ($type instanceof InterfaceType) {
            return $this->extendInterfaceType($type);
        }

        if ($type instanceof UnionType) {
            return $this->extendUnionType($type);
        }

        // This type is not yet extendable.
        return $type;
    }

    /**
     * @param ObjectType $type
     * @return ObjectType
     */
    protected function extendObjectType(ObjectType $type): ObjectType
    {
        $typeName          = $type->getName();
        $extensionASTNodes = $type->getExtensionAstNodes();

        if (isset($this->typeExtensionsMap[$typeName])) {
            $extensionASTNodes = !empty($extensionASTNodes)
                ? \array_merge($this->typeExtensionsMap[$typeName], $extensionASTNodes)
                : $this->typeExtensionsMap[$typeName];
        }

        return newGraphQLObjectType([
            'name'              => $typeName,
            'description'       => $type->getDescription(),
            'interfaces'        => function () use ($type) {
                return $this->extendImplementedInterfaces($type);
            },
            'fields'            => function () use ($type) {
                return $this->extendFieldMap($type);
            },
            'astNode'           => $type->getAstNode(),
            'extensionASTNodes' => $extensionASTNodes,
            'isTypeOf'          => $type->getIsTypeOf(),
        ]);
    }

    /**
     * @param InterfaceType $type
     * @return InterfaceType
     */
    protected function extendInterfaceType(InterfaceType $type): InterfaceType
    {
        $typeName          = $type->getName();
        $extensionASTNodes = $this->typeExtensionsMap[$typeName] ?? [];

        if (isset($this->typeExtensionsMap[$typeName])) {
            $extensionASTNodes = !empty($extensionASTNodes)
                ? \array_merge($this->typeExtensionsMap[$typeName], $extensionASTNodes)
                : $this->typeExtensionsMap[$typeName];
        }

        return newGraphQLInterfaceType([
            'name'              => $typeName,
            'description'       => $type->getDescription(),
            'fields'            => function () use ($type) {
                return $this->extendFieldMap($type);
            },
            'astNode'           => $type->getAstNode(),
            'extensionASTNodes' => $extensionASTNodes,
            'resolveType'       => $type->getResolveType(),
        ]);
    }

    /**
     * @param UnionType $type
     * @return UnionType
     * @throws InvariantException
     */
    protected function extendUnionType(UnionType $type): UnionType
    {
        return newGraphQLUnionType([
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'types'       => \array_map(function ($unionType) {
                return $this->getExtendedType($unionType);
            }, $type->getTypes()),
            'astNode'     => $type->getAstNode(),
            'resolveType' => $type->getResolveType(),
        ]);
    }

    /**
     * @param ObjectType $type
     * @return array
     * @throws InvariantException
     */
    protected function extendImplementedInterfaces(ObjectType $type): array
    {
        $interfaces = \array_map(function (InterfaceType $interface) {
            return $this->getExtendedType($interface);
        }, $type->getInterfaces());

        // If there are any extensions to the interfaces, apply those here.
        $extensions = $this->typeExtensionsMap[$type->getName()] ?? null;

        if (null !== $extensions) {
            foreach ($extensions as $extension) {
                foreach ($extension->getInterfaces() as $namedType) {
                    // Note: While this could make early assertions to get the correctly
                    // typed values, that would throw immediately while type system
                    // validation with validateSchema() will produce more actionable results.
                    $interfaces[] = $this->definitionBuilder->buildType($namedType);
                }
            }
        }

        return $interfaces;
    }

    /**
     * @param TypeInterface|ObjectType|InterfaceType $type
     * @return array
     * @throws InvalidTypeException
     * @throws InvariantException
     * @throws ExtensionException
     * @throws InvalidArgumentException
     */
    protected function extendFieldMap(TypeInterface $type): array
    {
        $typeName    = $type->getName();
        $newFieldMap = [];
        $oldFieldMap = $type->getFields();

        foreach (\array_keys($oldFieldMap) as $fieldName) {
            $field = $oldFieldMap[$fieldName];

            $newFieldMap[$fieldName] = [
                'description'       => $field->getDescription(),
                'deprecationReason' => $field->getDeprecationReason(),
                'type'              => $this->extendFieldType($field->getType()),
                'args'              => keyMap($field->getArguments(), function (Argument $argument) {
                    return $argument->getName();
                }),
                'astNode'           => $field->getAstNode(),
                'resolve'           => $field->getResolve(),
            ];
        }

        // If there are any extensions to the fields, apply those here.
        /** @var ObjectTypeExtensionNode|InterfaceTypeExtensionNode[] $extensions */
        $extensions = $this->typeExtensionsMap[$typeName] ?? null;

        if (null !== $extensions) {
            foreach ($extensions as $extension) {
                foreach ($extension->getFields() as $field) {
                    $fieldName = $field->getNameValue();

                    if (isset($oldFieldMap[$fieldName])) {
                        throw new ExtensionException(
                            \sprintf(
                                'Field "%s.%s" already exists in the schema. ' .
                                'It cannot also be defined in this type extension.',
                                $typeName, $fieldName
                            ),
                            [$field]
                        );
                    }

                    $newFieldMap[$fieldName] = $this->definitionBuilder->buildField($field);
                }
            }
        }

        return $newFieldMap;
    }

    /**
     * @param TypeInterface $typeDefinition
     * @return TypeInterface
     * @throws InvalidArgumentException
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected function extendFieldType(TypeInterface $typeDefinition): TypeInterface
    {
        if ($typeDefinition instanceof ListType) {
            return newGraphQLList($this->extendFieldType($typeDefinition->getOfType()));
        }

        if ($typeDefinition instanceof NonNullType) {
            return newGraphQLNonNull($this->extendFieldType($typeDefinition->getOfType()));
        }

        return $this->getExtendedType($typeDefinition);
    }
}
