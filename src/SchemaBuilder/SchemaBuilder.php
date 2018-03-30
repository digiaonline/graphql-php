<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Util\arraySome;

class SchemaBuilder implements SchemaBuilderInterface
{
    /**
     * @var DefinitionBuilderCreatorInterface
     */
    protected $definitionBuilderCreator;

    /**
     * SchemaBuilder constructor.
     *
     * @param DefinitionBuilderCreatorInterface $definitionBuilderCreator
     */
    public function __construct(DefinitionBuilderCreatorInterface $definitionBuilderCreator)
    {
        $this->definitionBuilderCreator = $definitionBuilderCreator;
    }

    /**
     * @inheritdoc
     */
    public function build(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry,
        array $options = []
    ): SchemaInterface {
        $schemaDefinition     = null;
        $typeDefinitions      = [];
        $nodeMap              = [];
        $directiveDefinitions = [];

        foreach ($document->getDefinitions() as $definition) {
            if ($definition instanceof SchemaDefinitionNode) {
                if ($schemaDefinition) {
                    throw new LanguageException('Must provide only one schema definition.');
                }
                $schemaDefinition = $definition;
                continue;
            }

            if ($definition instanceof TypeDefinitionNodeInterface) {
                $typeName = $definition->getNameValue();
                if (isset($nodeMap[$typeName])) {
                    throw new LanguageException(sprintf('Type "%s" was defined more than once.', $typeName));
                }
                $typeDefinitions[]  = $definition;
                $nodeMap[$typeName] = $definition;
                continue;
            }

            if ($definition instanceof DirectiveDefinitionNode) {
                $directiveDefinitions[] = $definition;
                continue;
            }
        }

        $operationTypes = null !== $schemaDefinition ? getOperationTypes($schemaDefinition, $nodeMap) : [
            'query'        => $nodeMap['Query'] ?? null,
            'mutation'     => $nodeMap['Mutation'] ?? null,
            'subscription' => $nodeMap['Subscription'] ?? null,
        ];

        $definitionBuilder = $this->definitionBuilderCreator->create($nodeMap, null, $resolverRegistry);

        $types = array_map(function (TypeDefinitionNodeInterface $definition) use ($definitionBuilder) {
            return $definitionBuilder->buildType($definition);
        }, $typeDefinitions);

        $directives = array_map(function (DirectiveDefinitionNode $definition) use ($definitionBuilder) {
            return $definitionBuilder->buildDirective($definition);
        }, $directiveDefinitions);

        if (!arraySome($directives, function (DirectiveInterface $directive) {
            return $directive->getName() === 'skip';
        })) {
            $directives[] = GraphQLSkipDirective();
        }

        if (!arraySome($directives, function (DirectiveInterface $directive) {
            return $directive->getName() === 'include';
        })) {
            $directives[] = GraphQLIncludeDirective();
        }

        if (!arraySome($directives, function (DirectiveInterface $directive) {
            return $directive->getName() === 'deprecated';
        })) {
            $directives[] = GraphQLDeprecatedDirective();
        }

        return GraphQLSchema([
            'query'        => isset($operationTypes['query'])
                ? $definitionBuilder->buildType($operationTypes['query'])
                : null,
            'mutation'     => isset($operationTypes['mutation'])
                ? $definitionBuilder->buildType($operationTypes['mutation'])
                : null,
            'subscription' => isset($operationTypes['subscription'])
                ? $definitionBuilder->buildType($operationTypes['subscription'])
                : null,
            'types'        => $types,
            'directives'   => $directives,
            'astNode'      => $schemaDefinition,
            'assumeValid'  => $options['assumeValid'] ?? false,
        ]);
    }
}

/**
 * @param SchemaDefinitionNode $schemaDefinition
 * @param array                $nodeMap
 * @return array
 * @throws LanguageException
 */
function getOperationTypes(SchemaDefinitionNode $schemaDefinition, array $nodeMap): array
{
    $operationTypes = [];

    foreach ($schemaDefinition->getOperationTypes() as $operationTypeDefinition) {
        /** @var TypeNodeInterface|NamedTypeNode $operationType */
        $operationType = $operationTypeDefinition->getType();
        $typeName      = $operationType->getNameValue();
        $operation     = $operationTypeDefinition->getOperation();

        if (isset($operationTypes[$typeName])) {
            throw new LanguageException(sprintf('Must provide only one %s type in schema.', $operation));
        }

        if (!isset($nodeMap[$typeName])) {
            throw new LanguageException(sprintf('Specified %s type %s not found in document.', $operation, $typeName));
        }

        $operationTypes[$operation] = $operationType;
    }

    return $operationTypes;
}
