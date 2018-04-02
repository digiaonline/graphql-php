<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\Building\BuilderContextCreatorInterface;
use Digia\GraphQL\Schema\Building\SchemaBuilderInterface;
use Digia\GraphQL\Schema\DefinitionBuilderCreatorInterface;
use Digia\GraphQL\Schema\ResolverRegistryInterface;
use Digia\GraphQL\Schema\SchemaInterface;
use function Digia\GraphQL\Type\newSchema;

class SchemaBuilder implements SchemaBuilderInterface
{
    /**
     * @var DefinitionBuilderCreatorInterface
     */
    protected $definitionBuilderCreator;

    /**
     * BuilderContextCreator constructor.
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
        $context = new BuildingContext($document, $resolverRegistry, $this->definitionBuilderCreator);

        $context->boot();

        return $context;
    }
}
