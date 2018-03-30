<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Schema\DefinitionBuilderCreatorInterface;
use Digia\GraphQL\Schema\DefinitionBuilderInterface;
use Digia\GraphQL\Schema\ResolverRegistryInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arraySome;

class BuilderContext implements BuilderContextInterface
{
    /**
     * @var DocumentNode
     */
    protected $document;

    /**
     * @var ResolverRegistryInterface
     */
    protected $resolverRegistry;

    /**
     * @var DefinitionBuilderCreatorInterface
     */
    protected $definitionBuilderCreator;

    /**
     * @var DefinitionBuilderInterface
     */
    protected $definitionBuilder;

    /**
     * @var SchemaDefinitionNode|null
     */
    protected $schemaDefinition;

    /**
     * @var TypeInterface[]
     */
    protected $typeDefinitionMap;

    /**
     * @var DirectiveDefinitionNode[]
     */
    protected $directiveDefinitions;

    /**
     * @var OperationTypeDefinitionNode[]
     */
    protected $operationTypeDefinitions;

    /**
     * BuilderContext constructor.
     * @param DocumentNode                      $document
     * @param ResolverRegistryInterface         $resolverRegistry
     * @param DefinitionBuilderCreatorInterface $definitionBuilderCreator
     * @throws LanguageException
     */
    public function __construct(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry,
        DefinitionBuilderCreatorInterface $definitionBuilderCreator
    ) {
        $this->document                 = $document;
        $this->resolverRegistry         = $resolverRegistry;
        $this->definitionBuilderCreator = $definitionBuilderCreator;
    }

    /**
     * @throws LanguageException
     */
    public function boot(): void
    {
        $this->buildDefinitions();

        $this->definitionBuilder = $this->createDefinitionBuilder();

        if (null !== $this->schemaDefinition) {
            $this->buildOperationTypeDefinitions();
        }
    }

    /**
     * @return TypeInterface|null
     */
    public function buildQueryType(): ?TypeInterface
    {
        $definition = $this->operationTypeDefinitions['query'] ?? $this->typeDefinitionMap['Query'] ?? null;
        return null !== $definition ? $this->definitionBuilder->buildType($definition) : null;
    }

    /**
     * @return TypeInterface|null
     */
    public function buildMutationType(): ?TypeInterface
    {
        $definition = $this->operationTypeDefinitions['mutation'] ?? $this->typeDefinitionMap['Mutation'] ?? null;
        return null !== $definition ? $this->definitionBuilder->buildType($definition) : null;
    }

    /**
     * @return TypeInterface|null
     */
    public function buildSubscriptionType(): ?TypeInterface
    {
        $definition = $this->operationTypeDefinitions['subscription'] ?? $this->typeDefinitionMap['Subscription'] ?? null;
        return null !== $definition ? $this->definitionBuilder->buildType($definition) : null;
    }

    /**
     * @return TypeInterface[]
     */
    public function buildTypes(): array
    {
        return \array_map(function (TypeDefinitionNodeInterface $definition) {
            return $this->definitionBuilder->buildType($definition);
        }, \array_values($this->typeDefinitionMap));
    }

    /**
     * @return Directive[]
     */
    public function buildDirectives(): array
    {
        $directives = \array_map(function (DirectiveDefinitionNode $definition) {
            return $this->definitionBuilder->buildDirective($definition);
        }, $this->directiveDefinitions);

        $specifiedDirectivesMap = [
            'skip'       => GraphQLSkipDirective(),
            'include'    => GraphQLIncludeDirective(),
            'deprecated' => GraphQLDeprecatedDirective(),
        ];

        foreach ($specifiedDirectivesMap as $name => $directive) {
            if (!arraySome($directives, function (Directive $directive) use ($name) {
                return $directive->getName() === $name;
            })) {
                $directives[] = $directive;
            }
        }

        return $directives;
    }

    /**
     * @return SchemaDefinitionNode|null
     */
    public function getSchemaDefinition(): ?SchemaDefinitionNode
    {
        return $this->schemaDefinition;
    }

    /**
     * @inheritdoc
     * @throws LanguageException
     */
    protected function buildDefinitions(): void
    {
        $schemaDefinition     = null;
        $typeDefinitionMap    = [];
        $directiveDefinitions = [];

        foreach ($this->document->getDefinitions() as $definition) {
            if ($definition instanceof SchemaDefinitionNode) {
                if (null !== $schemaDefinition) {
                    throw new LanguageException('Must provide only one schema definition.');
                }

                $schemaDefinition = $definition;

                continue;
            }

            if ($definition instanceof TypeDefinitionNodeInterface) {
                $typeName = $definition->getNameValue();

                if (isset($typeDefinitionMap[$typeName])) {
                    throw new LanguageException(sprintf('Type "%s" was defined more than once.', $typeName));
                }

                $typeDefinitionMap[$typeName] = $definition;

                continue;
            }

            if ($definition instanceof DirectiveDefinitionNode) {
                $directiveDefinitions[] = $definition;

                continue;
            }
        }

        $this->schemaDefinition     = $schemaDefinition;
        $this->typeDefinitionMap    = $typeDefinitionMap;
        $this->directiveDefinitions = $directiveDefinitions;
    }

    /**
     * @return DefinitionBuilderInterface
     */
    protected function createDefinitionBuilder(): DefinitionBuilderInterface
    {
        return $this->definitionBuilderCreator->create($this->typeDefinitionMap, null, $this->resolverRegistry);
    }

    /**
     * @throws LanguageException
     */
    protected function buildOperationTypeDefinitions(): void
    {
        $operationTypeDefinitions = [];

        foreach ($this->schemaDefinition->getOperationTypes() as $operationTypeDefinition) {
            /** @var TypeNodeInterface|NamedTypeNode $operationType */
            $operationType = $operationTypeDefinition->getType();
            $typeName      = $operationType->getNameValue();
            $operation     = $operationTypeDefinition->getOperation();

            if (isset($operationTypeDefinitions[$typeName])) {
                throw new LanguageException(
                    \sprintf('Must provide only one %s type in schema.', $operation)
                );
            }

            if (!isset($this->typeDefinitionMap[$typeName])) {
                throw new LanguageException(
                    \sprintf('Specified %s type %s not found in document.', $operation, $typeName)
                );
            }

            $operationTypeDefinitions[$operation] = $operationType;
        }

        $this->operationTypeDefinitions = $operationTypeDefinitions;
    }
}
