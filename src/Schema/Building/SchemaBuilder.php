<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NameAwareInterface;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\TypeSystemDefinitionNodeInterface;
use Digia\GraphQL\Schema\DefinitionBuilder;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Schema\Schema;
use function Digia\GraphQL\Type\newSchema;

class SchemaBuilder implements SchemaBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry,
        array $options = []
    ): Schema {
        /** @noinspection PhpUnhandledExceptionInspection */
        $context = $this->createContext($document, $resolverRegistry, $options);

        /** @noinspection PhpUnhandledExceptionInspection */
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
     * @param DocumentNode              $document
     * @param ResolverRegistryInterface $resolverRegistry
     * @param array                     $options
     * @return BuildingContextInterface
     * @throws SchemaBuildingException
     */
    protected function createContext(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry,
        array $options
    ): BuildingContextInterface {
        $info = $this->createInfo($document);

        $definitionBuilder = new DefinitionBuilder(
            $info->getTypeDefinitionMap(),
            $resolverRegistry,
            $options['types'] ?? [],
            $options['directives'] ?? [],
            null // use the default resolveType-function
        );

        return new BuildingContext($resolverRegistry, $definitionBuilder, $info);
    }

    /**
     * @param DocumentNode $document
     * @return BuildInfo
     * @throws SchemaBuildingException
     */
    protected function createInfo(DocumentNode $document): BuildInfo
    {
        $schemaDefinition     = null;
        $typeDefinitionMap    = [];
        $directiveDefinitions = [];

        foreach ($document->getDefinitions() as $definition) {
            if ($definition instanceof SchemaDefinitionNode) {
                if (null !== $schemaDefinition) {
                    throw new SchemaBuildingException('Must provide only one schema definition.');
                }

                $schemaDefinition = $definition;

                continue;
            }

            if ($definition instanceof DirectiveDefinitionNode) {
                $directiveDefinitions[] = $definition;

                continue;
            }

            if ($definition instanceof TypeSystemDefinitionNodeInterface && $definition instanceof NameAwareInterface) {
                $typeName = $definition->getNameValue();

                if (isset($typeDefinitionMap[$typeName])) {
                    throw new SchemaBuildingException(\sprintf('Type "%s" was defined more than once.', $typeName));
                }

                $typeDefinitionMap[$typeName] = $definition;

                continue;
            }
        }

        return new BuildInfo(
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
     * @throws SchemaBuildingException
     */
    protected function getOperationTypeDefinitions(SchemaDefinitionNode $node, array $typeDefinitionMap): array
    {
        $definitions = [];

        foreach ($node->getOperationTypes() as $operationTypeDefinition) {
            $operationType = $operationTypeDefinition->getType();

            if (!$operationType instanceof NamedTypeNode) {
                continue; // TODO: Throw exception?
            }

            $typeName  = $operationType->getNameValue();
            $operation = $operationTypeDefinition->getOperation();

            if (isset($definitions[$typeName])) {
                throw new SchemaBuildingException(
                    \sprintf('Must provide only one %s type in schema.', $operation)
                );
            }

            if (!isset($typeDefinitionMap[$typeName])) {
                throw new SchemaBuildingException(
                    \sprintf('Specified %s type %s not found in document.', $operation, $typeName)
                );
            }

            $definitions[$operation] = $operationType;

        }

        return $definitions;
    }
}
