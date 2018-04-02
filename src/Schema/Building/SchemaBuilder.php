<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Error\BuildingException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Schema\DefinitionBuilder;
use Digia\GraphQL\Schema\ResolverRegistryInterface;
use Digia\GraphQL\Schema\SchemaInterface;
use Psr\SimpleCache\CacheInterface;
use function Digia\GraphQL\Type\newSchema;

class SchemaBuilder implements SchemaBuilderInterface
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
     * @inheritdoc
     */
    public function build(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry,
        array $options = []
    ): SchemaInterface {
        $context = $this->createContext($document, $resolverRegistry);

        return newSchema([
            'query'        => $context->buildQueryType(),
            'mutation'     => $context->buildMutationType(),
            'subscription' => $context->buildSubscriptionType(),
            'types'        => $context->buildTypes(),
            'directives'   => $context->buildDirectives(),
            'astNode'      => $context->getSchemaDefinition(),
            'assumeValid'  => $options['assumeValid'] ?? false,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createContext(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry
    ): BuildingContextInterface {
        $info = $this->createBuildingInfo($document);

        $definitionBuilder = new DefinitionBuilder(
            $info->getTypeDefinitionMap(),
            $resolverRegistry,
            null, // use the default resolveType-function
            $this->cache
        );

        return new BuildingContext($document, $resolverRegistry, $definitionBuilder, $info);
    }

    /**
     * @inheritdoc
     * @throws BuildingException
     */
    protected function createBuildingInfo(DocumentNode $document): BuildingInfo
    {
        $schemaDefinition     = null;
        $typeDefinitionMap    = [];
        $directiveDefinitions = [];

        foreach ($document->getDefinitions() as $definition) {
            if ($definition instanceof SchemaDefinitionNode) {
                if (null !== $schemaDefinition) {
                    throw new BuildingException('Must provide only one schema definition.');
                }

                $schemaDefinition = $definition;

                continue;
            }

            if ($definition instanceof TypeDefinitionNodeInterface) {
                $typeName = $definition->getNameValue();

                if (isset($typeDefinitionMap[$typeName])) {
                    throw new BuildingException(sprintf('Type "%s" was defined more than once.', $typeName));
                }

                $typeDefinitionMap[$typeName] = $definition;

                continue;
            }

            if ($definition instanceof DirectiveDefinitionNode) {
                $directiveDefinitions[] = $definition;

                continue;
            }
        }

        return new BuildingInfo(
            $document,
            $typeDefinitionMap,
            $directiveDefinitions,
            null !== $schemaDefinition ? $this->getOperationTypeDefinitions($schemaDefinition, $typeDefinitionMap) : [],
            $schemaDefinition
        );
    }

    /**
     * @param SchemaDefinitionNode $node
     * @return array
     * @throws BuildingException
     */
    protected function getOperationTypeDefinitions(SchemaDefinitionNode $node, array $typeDefinitionMap): array
    {
        $definitions = [];

        foreach ($node->getOperationTypes() as $operationTypeDefinition) {
            /** @var TypeNodeInterface|NamedTypeNode $operationType */
            $operationType = $operationTypeDefinition->getType();
            $typeName      = $operationType->getNameValue();
            $operation     = $operationTypeDefinition->getOperation();

            if (isset($definitions[$typeName])) {
                throw new BuildingException(
                    \sprintf('Must provide only one %s type in schema.', $operation)
                );
            }

            if (!isset($typeDefinitionMap[$typeName])) {
                throw new BuildingException(
                    \sprintf('Specified %s type %s not found in document.', $operation, $typeName)
                );
            }

            $definitions[$operation] = $operationType;
        }

        return $definitions;
    }
}
