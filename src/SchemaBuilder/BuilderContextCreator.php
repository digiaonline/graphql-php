<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Language\Node\DocumentNode;

class BuilderContextCreator implements BuilderContextCreatorInterface
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
     * @throws LanguageException
     */
    public function create(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry
    ): BuilderContextInterface {
        $context = new BuilderContext($document, $resolverRegistry, $this->definitionBuilderCreator);

        $context->boot();

        return $context;
    }
}
