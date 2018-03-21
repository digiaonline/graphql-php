<?php

namespace Digia\GraphQL\SchemaExtender;

use Digia\GraphQL\Cache\CacheAwareTrait;
use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\InterfaceTypeDefinitionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectTypeDefinitionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\SchemaBuilder\DefinitionBuilderCreatorInterface;
use Digia\GraphQL\SchemaBuilder\DefinitionBuilderInterface;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\SchemaInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\isIntrospectionType;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Util\toString;

class SchemaExtender implements SchemaExtenderInterface
{
    use CacheAwareTrait;

    private const CACHE_PREFIX = 'GraphQL_SchemaExtender_';

    /**
     * @var DefinitionBuilderCreatorInterface
     */
    protected $definitionBuilderCreator;

    /**
     * @var DefinitionBuilderInterface
     */
    protected $definitionBuilder;

    /**
     * @var array
     */
    protected $typeDefinitionMap;

    /**
     * @var array
     */
    protected $typeExtensionMap;

    /**
     * @var array
     */
    protected $directiveDefinitions;

    /**
     * SchemaExtender constructor.
     * @param DefinitionBuilderCreatorInterface $definitionBuilderCreator
     */
    public function __construct(DefinitionBuilderCreatorInterface $definitionBuilderCreator, CacheInterface $cache)
    {
        $this->definitionBuilderCreator = $definitionBuilderCreator;
        $this->cache                    = $cache;
    }

    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @param array           $options
     * @return SchemaInterface
     * @throws ExecutionException
     */
    public function extend(SchemaInterface $schema, DocumentNode $document, array $options = []): SchemaInterface
    {
        $this->typeDefinitionMap    = [];
        $this->typeExtensionsMap    = [];
        $this->directiveDefinitions = [];

        foreach ($document->getDefinitions() as $definition) {
            if ($definition instanceof TypeDefinitionNodeInterface) {
                // Sanity check that none of the defined types conflict with the schema's existing types.
                $typeName     = $definition->getNameValue();
                $existingType = $schema->getType($typeName);

                if (null !== $existingType) {
                    throw new ExecutionException(
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

            if ($definition instanceof ObjectTypeDefinitionNode || $definition instanceof InterfaceTypeDefinitionNode) {
                // Sanity check that this type extension exists within the schema's existing types.
                $extendedTypeName = $definition->getNameValue();
                $existingType     = $schema->getType($extendedTypeName);

                if (null === $existingType) {
                    throw new ExecutionException(
                        \sprintf(
                            'Cannot extend type "%s" because it does not exist in the existing schema.',
                            $extendedTypeName
                        ),
                        [$definition]
                    );
                }

                $this->checkExtensionNode($existingType, $definition);

                $existingTypeExtensions               = $typeExtensionsMap[$extendedTypeName] ?? [];
                $typeExtensionsMap[$extendedTypeName] = \array_merge($existingTypeExtensions, [$definition]);

                continue;
            }

            if ($definition instanceof DirectiveDefinitionNode) {
                $directiveName     = $definition->getNameValue();
                $existingDirective = $schema->getDirective($directiveName);

                if (null !== $existingDirective) {
                    throw new ExecutionException(
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

            throw new ExecutionException(
                \sprintf('The %s kind is not yet supported by extendSchema().', $definition->getKind())
            );
        }

        // If this document contains no new types, extensions, or directives then
        // return the same unmodified GraphQLSchema instance.
        if (empty($typeDefinitionMap) && empty($typeExtensionsMap) && empty($directiveDefinitions)) {
            return $schema;
        }

        $resolveTypeFunction = function (NamedTypeNode $node) use ($schema): ?TypeInterface {
            $typeName     = $node->getNameValue();
            $existingType = $schema->getType($typeName);

            if (null !== $existingType) {
                return $this->getExtendedType($existingType);
            }

            throw new ExecutionException(
                \sprintf(
                    'Unknown type: "%s". Ensure that this type exists ' .
                    'either in the original schema, or is added in a type definition.',
                    $typeName
                ),
                [$node]
            );
        };

        $this->definitionBuilder = $this->definitionBuilderCreator->create(
            $typeDefinitionMap,
            [],
            $resolveTypeFunction
        );
    }

    /**
     * @param TypeInterface $type
     * @param NodeInterface $node
     * @throws ExecutionException
     */
    protected function checkExtensionNode(TypeInterface $type, NodeInterface $node): void
    {
        if ($node instanceof ObjectTypeExtensionNode && !($type instanceof ObjectType)) {
            throw new ExecutionException(
                \sprintf('Cannot extend non-object type "%s".', toString($type)),
                [$node]
            );
        }

        if ($node instanceof InterfaceTypeExtensionNode && !($type instanceof InterfaceType)) {
            throw new ExecutionException(
                \sprintf('Cannot extend non-interface type "%s".', toString($type)),
                [$node]
            );
        }
    }

    /**
     * @param NamedTypeInterface $type
     * @return NamedTypeInterface
     * @throws InvalidArgumentException
     */
    protected function getExtendedType(NamedTypeInterface $type): NamedTypeInterface
    {
        $typeName = $type->getName();

        if ($this->isInCache($typeName)) {
            $this->setInCache($typeName, $this->extendType($type));
        }

        return $this->getFromCache($typeName);
    }

    /**
     * @param NamedTypeInterface $type
     * @return NamedTypeInterface
     */
    protected function extendType(NamedTypeInterface $type): NamedTypeInterface
    {
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

    protected function extendObjectType(ObjectType $type): ObjectType
    {
        $typeName          = $type->getName();
        $extensionASTNodes = $type->getExtensionAstNodes();

        if (isset($this->typeExtensionMap[$typeName])) {
            $extensionASTNodes = !empty($extensionASTNodes)
                ? \array_merge($this->typeExtensionMap[$typeName], $extensionASTNodes)
                : $this->typeExtensionMap[$typeName];
        }

        return GraphQLObjectType([
            'name'              => $typeName,
            'description'       => $type->getDescription(),
            'interfaces'        => function () use ($type) {
                return $this->extendImplementedInterfaces($type);
            },
            'fields'            => function () use ($type) {
                return $this->extendFieldMap($type);
            },
            'extensionASTNodes' => $extensionASTNodes,
            'isTypeOf'          => $type->getIsTypeOf(),
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
        /** @var ObjectType[] $extensions */
        $extensions = $this->typeExtensionMap[$type->getName()] ?? null;

        if (null !== $extensions) {
            foreach ($extensions as $extension) {
                foreach ($extension->getInterfaces() as $namedType) {
                    // Note: While this could make early assertions to get the correctly
                    // typed values, that would throw immediately while type system
                    // validation with validateSchema() will produce more actionable results.
                    /** @var NodeInterface $namedType */
                    $interfaces[] = $this->definitionBuilder->buildType($namedType);
                }
            }
        }

        return $interfaces;
    }

    /**
     * @param TypeInterface|ObjectType|InterfaceType $type
     * @return array
     * @throws InvariantException
     */
    protected function extendFieldMap(TypeInterface $type): array
    {
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
        /** @var ObjectType[] $extensions */
        $extensions = $this->typeExtensionMap[$type->getName()] ?? null;

        if (null !== $extensions) {
            foreach ($extensions as $extension) {
                foreach ($extension->getFields() as $field) {
                    $fieldName = $field->getName();
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getCachePrefix(): string
    {
        return self::CACHE_PREFIX;
    }
}
