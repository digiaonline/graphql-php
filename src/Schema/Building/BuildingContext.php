<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\NamedTypeNodeInterface;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\TypeSystemDefinitionNodeInterface;
use Digia\GraphQL\Schema\DefinitionBuilderInterface;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arraySome;

class BuildingContext implements BuildingContextInterface
{
    /**
     * @var ResolverRegistryInterface
     */
    protected $resolverRegistry;

    /**
     * @var DefinitionBuilderInterface
     */
    protected $definitionBuilder;

    /**
     * @var BuildInfo
     */
    protected $info;

    /**
     * BuilderContext constructor.
     * @param ResolverRegistryInterface  $resolverRegistry
     * @param DefinitionBuilderInterface $definitionBuilder
     * @param BuildInfo                  $info
     */
    public function __construct(
        ResolverRegistryInterface $resolverRegistry,
        DefinitionBuilderInterface $definitionBuilder,
        BuildInfo $info
    ) {
        $this->resolverRegistry  = $resolverRegistry;
        $this->definitionBuilder = $definitionBuilder;
        $this->info              = $info;
    }

    /**
     * @return TypeInterface|null
     */
    public function buildQueryType(): ?TypeInterface
    {
        $definition = $this->info->getOperationTypeDefinition('query');
        return null !== $definition ? $this->definitionBuilder->buildType($definition) : null;
    }

    /**
     * @return TypeInterface|null
     */
    public function buildMutationType(): ?TypeInterface
    {
        $definition = $this->info->getOperationTypeDefinition('mutation');
        return null !== $definition ? $this->definitionBuilder->buildType($definition) : null;
    }

    /**
     * @return TypeInterface|null
     */
    public function buildSubscriptionType(): ?TypeInterface
    {
        $definition = $this->info->getOperationTypeDefinition('subscription');
        return null !== $definition ? $this->definitionBuilder->buildType($definition) : null;
    }

    /**
     * @return TypeInterface[]
     */
    public function buildTypes(): array
    {
        return \array_map(function (NamedTypeNodeInterface $definition) {
            return $this->definitionBuilder->buildType($definition);
        }, \array_values($this->info->getTypeDefinitionMap()));
    }

    /**
     * @return Directive[]
     */
    public function buildDirectives(): array
    {
        $directives = \array_map(function (DirectiveDefinitionNode $definition) {
            return $this->definitionBuilder->buildDirective($definition);
        }, $this->info->getDirectiveDefinitions());

        $specifiedDirectivesMap = [
            'skip'       => SkipDirective(),
            'include'    => IncludeDirective(),
            'deprecated' => DeprecatedDirective(),
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
        return $this->info->getSchemaDefinition();
    }
}
