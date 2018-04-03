<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Error\ExtensionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\EnumTypeExtensionNode;
use Digia\GraphQL\Language\Node\InputObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\ScalarTypeExtensionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\UnionTypeExtensionNode;
use Digia\GraphQL\Schema\DefinitionBuilder;
use Digia\GraphQL\Schema\SchemaInterface;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Util\toString;

class SchemaExtender implements SchemaExtenderInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * BuilderContextCreator constructor.
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @return SchemaInterface
     * @throws InvariantException
     * @throws ExtensionException
     * @throws InvalidArgumentException
     */
    public function extend(SchemaInterface $schema, DocumentNode $document): SchemaInterface
    {
        $context = $this->createContext($schema, $document);

        // If this document contains no new types, extensions, or directives then
        // return the same unmodified GraphQLSchema instance.
        if (!$context->isSchemaExtended()) {
            return $schema;
        }

        return newSchema([
            'query'        => $context->getExtendedQueryType(),
            'mutation'     => $context->getExtendedMutationType(),
            'subscription' => $context->getExtendedSubscriptionType(),
            'types'        => $context->getExtendedTypes(),
            'directives'   => $context->getExtendedDirectives(),
            'astNode'      => $schema->getAstNode(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createContext(SchemaInterface $schema, DocumentNode $document): ExtensionContextInterface
    {
        $info = $this->createInfo($schema, $document);

        // Context has to be created in order to create the definition builder,
        // because we are using its `resolveType` function to resolve types.
        $context = new ExtensionContext($info);

        $definitionBuilder = new DefinitionBuilder(
            $info->getTypeDefinitionMap(),
            null,
            [$context, 'resolveType'],
            $this->cache
        );

        return $context->setDefinitionBuilder($definitionBuilder);
    }

    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @return ExtendInfo
     * @throws ExtensionException
     */
    protected function createInfo(SchemaInterface $schema, DocumentNode $document): ExtendInfo
    {
        $typeDefinitionMap    = [];
        $typeExtensionsMap    = [];
        $directiveDefinitions = [];

        foreach ($document->getDefinitions() as $definition) {
            if ($definition instanceof TypeDefinitionNodeInterface) {
                // Sanity check that none of the defined types conflict with the schema's existing types.
                $typeName     = $definition->getNameValue();
                $existingType = $schema->getType($typeName);

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
                $existingType     = $schema->getType($extendedTypeName);

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
                $existingDirective = $schema->getDirective($directiveName);

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

        return new ExtendInfo(
            $schema,
            $document,
            $typeDefinitionMap,
            $typeExtensionsMap,
            $directiveDefinitions
        );
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
}
