<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Error\ExtensionException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
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
use Psr\SimpleCache\InvalidArgumentException;
use function Digia\GraphQL\Type\isIntrospectionType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newUnionType;
use function Digia\GraphQL\Util\invariant;
use function Digia\GraphQL\Util\keyMap;

class ExtensionContext implements ExtensionContextInterface
{
    /**
     * @var ExtendInfo
     */
    protected $info;

    /**
     * @var DefinitionBuilderInterface
     */
    protected $definitionBuilder;

    /**
     * @var NamedTypeInterface[]
     */
    protected $extendTypeCache = [];

    /**
     * ExtensionContext constructor.
     * @param ExtendInfo $info
     */
    public function __construct(ExtendInfo $info)
    {
        $this->info = $info;
    }

    /**
     * @return bool
     */
    public function isSchemaExtended(): bool
    {
        return
            $this->info->hasTypeExtensionsMap() ||
            $this->info->hasTypeDefinitionMap() ||
            $this->info->hasDirectiveDefinitions();
    }

    /**
     * @return TypeInterface|null
     * @throws InvalidArgumentException
     * @throws InvariantException
     */
    public function getExtendedQueryType(): ?TypeInterface
    {
        $existingQueryType = $this->info->getSchema()->getQueryType();

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
        $existingMutationType = $this->info->getSchema()->getMutationType();

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
        $existingSubscriptionType = $this->info->getSchema()->getSubscriptionType();

        return null !== $existingSubscriptionType
            ? $this->getExtendedType($existingSubscriptionType)
            : null;
    }

    /**
     * @return TypeInterface[]
     */
    public function getExtendedTypes(): array
    {
        $extendedTypes = \array_map(function ($type) {
            return $this->getExtendedType($type);
        }, $this->info->getSchema()->getTypeMap());

        return \array_merge(
            $extendedTypes,
            $this->definitionBuilder->buildTypes($this->info->getTypeDefinitionMap())
        );
    }

    /**
     * @return Directive[]
     * @throws InvariantException
     */
    public function getExtendedDirectives(): array
    {
        $existingDirectives = $this->info->getSchema()->getDirectives();

        invariant(!empty($existingDirectives), 'schema must have default directives');

        return \array_merge(
            $existingDirectives,
            \array_map(function (DirectiveDefinitionNode $node) {
                return $this->definitionBuilder->buildDirective($node);
            }, $this->info->getDirectiveDefinitions())
        );
    }

    /**
     * @param DefinitionBuilderInterface $definitionBuilder
     * @return ExtensionContext
     */
    public function setDefinitionBuilder(DefinitionBuilderInterface $definitionBuilder): ExtensionContext
    {
        $this->definitionBuilder = $definitionBuilder;
        return $this;
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
        $existingType = $this->info->getSchema()->getType($typeName);

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
     * @param TypeInterface $type
     * @return TypeInterface
     * @throws InvalidArgumentException
     * @throws InvariantException
     */
    protected function getExtendedType(TypeInterface $type): TypeInterface
    {
        /** @noinspection PhpUndefinedMethodInspection */
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
     * @throws InvariantException
     */
    protected function extendObjectType(ObjectType $type): ObjectType
    {
        $typeName          = $type->getName();
        $extensionASTNodes = $type->getExtensionAstNodes();

        if ($this->info->hasTypeExtensions($typeName)) {
            $extensionASTNodes = $this->extendExtensionASTNodes($typeName, $extensionASTNodes);
        }

        return newObjectType([
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
     * @throws InvariantException
     */
    protected function extendInterfaceType(InterfaceType $type): InterfaceType
    {
        $typeName          = $type->getName();
        $extensionASTNodes = $this->info->getTypeExtensions($typeName);

        if ($this->info->hasTypeExtensions($typeName)) {
            $extensionASTNodes = $this->extendExtensionASTNodes($typeName, $extensionASTNodes);
        }

        return newInterfaceType([
            'name'              => $typeName,
            'description'       => $type->getDescription(),
            'fields'            => function () use ($type) {
                return $this->extendFieldMap($type);
            },
            'astNode'           => $type->getAstNode(),
            'extensionASTNodes' => $extensionASTNodes,
            'resolveType'       => $type->getResolveTypeCallback(),
        ]);
    }

    /**
     * @param string $typeName
     * @param array  $nodes
     * @return array
     */
    protected function extendExtensionASTNodes(string $typeName, array $nodes): array
    {
        $typeExtensions = $this->info->getTypeExtensions($typeName);
        return !empty($nodes) ? \array_merge($typeExtensions, $nodes) : $typeExtensions;
    }

    /**
     * @param UnionType $type
     * @return UnionType
     * @throws InvariantException
     */
    protected function extendUnionType(UnionType $type): UnionType
    {
        return newUnionType([
            'name'        => $type->getName(),
            'description' => $type->getDescription(),
            'types'       => \array_map(function ($unionType) {
                return $this->getExtendedType($unionType);
            }, $type->getTypes()),
            'astNode'     => $type->getAstNode(),
            'resolveType' => $type->getResolveTypeCallback(),
        ]);
    }

    /**
     * @param ObjectType $type
     * @return array
     * @throws InvariantException
     */
    protected function extendImplementedInterfaces(ObjectType $type): array
    {
        $typeName = $type->getName();

        $interfaces = \array_map(function (InterfaceType $interface) {
            return $this->getExtendedType($interface);
        }, $type->getInterfaces());

        // If there are any extensions to the interfaces, apply those here.
        $extensions = $this->info->getTypeExtensions($typeName);

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
                'resolve'           => $field->getResolveCallback(),
            ];
        }

        // If there are any extensions to the fields, apply those here.
        $extensions = $this->info->getTypeExtensions($typeName);

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
            return newList($this->extendFieldType($typeDefinition->getOfType()));
        }

        if ($typeDefinition instanceof NonNullType) {
            return newNonNull($this->extendFieldType($typeDefinition->getOfType()));
        }

        return $this->getExtendedType($typeDefinition);
    }
}
