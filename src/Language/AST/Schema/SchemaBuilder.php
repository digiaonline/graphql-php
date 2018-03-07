<?php

namespace Digia\GraphQL\Language\AST\Schema;

use Digia\GraphQL\Language\AST\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\AST\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\TypeNodeInterface;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Util\arraySome;

class SchemaBuilder implements SchemaBuilderInterface
{

    /**
     * @var DefinitionBuilderInterface
     */
    protected $definitionBuilder;

    /**
     * SchemaBuilder constructor.
     *
     * @param DefinitionBuilderInterface $definitionBuilder
     */
    public function __construct(DefinitionBuilderInterface $definitionBuilder)
    {
        $this->definitionBuilder = $definitionBuilder;
    }

    /**
     * @param DocumentNode $documentNode
     * @param array        $options
     * @return SchemaInterface
     * @throws \Exception
     * @throws \TypeError
     */
    public function build(DocumentNode $documentNode, array $options = []): SchemaInterface
    {
        $schemaDefinition     = null;
        $typeDefinitions      = [];
        $nodeMap              = [];
        $directiveDefinitions = [];

        foreach ($documentNode->getDefinitions() as $definition) {
            if ($definition instanceof SchemaDefinitionNode) {
                if ($schemaDefinition) {
                    throw new \Exception('Must provide only one schema definition.');
                }
                $schemaDefinition = $definition;
                continue;
            }

            if ($definition instanceof TypeDefinitionNodeInterface) {
                $typeName = $definition->getNameValue();
                if (isset($nodeMap[$typeName])) {
                    throw new \Exception(sprintf('Type "%s" was defined more than once.', $typeName));
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

        $this->definitionBuilder->setTypeDefinitionMap($nodeMap);

        $types = array_map(function (TypeDefinitionNodeInterface $definition) {
            return $this->definitionBuilder->buildType($definition);
        }, $typeDefinitions);

        $directives = array_map(function (DirectiveDefinitionNode $definition) {
            return $this->definitionBuilder->buildDirective($definition);
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
                ? $this->definitionBuilder->buildType($operationTypes['query'])
                : null,
            'mutation'     => isset($operationTypes['mutation'])
                ? $this->definitionBuilder->buildType($operationTypes['mutation'])
                : null,
            'subscription' => isset($operationTypes['subscription'])
                ? $this->definitionBuilder->buildType($operationTypes['subscription'])
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
 * @throws \Exception
 */
function getOperationTypes(SchemaDefinitionNode $schemaDefinition, array $nodeMap): array
{
    $operationTypes = [];

    foreach ($schemaDefinition->getOperationTypes() as $operationTypeDefinition) {
        /** @var TypeNodeInterface|NamedTypeNode|NameTrait $operationType */
        $operationType = $operationTypeDefinition->getType();
        $typeName  = $operationType->getNameValue();
        $operation = $operationTypeDefinition->getOperation();

        if (isset($operationTypes[$typeName])) {
            throw new \Exception(sprintf('Must provide only one %s type in schema.', $operation));
        }

        if (!isset($nodeMap[$typeName])) {
            throw new \Exception(sprintf('Specified %s type %s not found in document.', $operation, $typeName));
        }

        $operationTypes[$operation] = $operationType;
    }

    return $operationTypes;
}
