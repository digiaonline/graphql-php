<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\Building\BuilderContextCreatorInterface;
use Digia\GraphQL\Schema\Building\SchemaBuilderInterface;
use Digia\GraphQL\Schema\ResolverRegistryInterface;
use Digia\GraphQL\Schema\SchemaInterface;
use function Digia\GraphQL\Type\newGraphQLSchema;

class SchemaBuilder implements SchemaBuilderInterface
{
    /**
     * @var BuilderContextCreatorInterface
     */
    protected $contextCreator;

    /**
     * SchemaBuilder constructor.
     * @param BuilderContextCreatorInterface $contextCreator
     */
    public function __construct(BuilderContextCreatorInterface $contextCreator)
    {
        $this->contextCreator = $contextCreator;
    }

    /**
     * @inheritdoc
     */
    public function build(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry,
        array $options = []
    ): SchemaInterface {
        $context = $this->contextCreator->create($document, $resolverRegistry);

        return newGraphQLSchema([
            'query'        => $context->buildQueryType(),
            'mutation'     => $context->buildMutationType(),
            'subscription' => $context->buildSubscriptionType(),
            'types'        => $context->buildTypes(),
            'directives'   => $context->buildDirectives(),
            'astNode'      => $context->getSchemaDefinition(),
            'assumeValid'  => $options['assumeValid'] ?? false,
        ]);
    }
}
