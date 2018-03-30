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

        return GraphQLSchema([
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
