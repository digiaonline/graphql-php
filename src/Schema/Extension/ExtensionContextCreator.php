<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\DefinitionBuilderCreatorInterface;
use Digia\GraphQL\Schema\SchemaInterface;

class ExtensionContextCreator implements ExtensionContextCreatorInterface
{
    /**
     * @var DefinitionBuilderCreatorInterface
     */
    protected $definitionBuilderCreator;

    /**
     * ExtensionContextCreator constructor.
     * @param DefinitionBuilderCreatorInterface $definitionBuilderCreator
     */
    public function __construct(DefinitionBuilderCreatorInterface $definitionBuilderCreator)
    {
        $this->definitionBuilderCreator = $definitionBuilderCreator;
    }

    /**
     * @inheritdoc
     */
    public function create(SchemaInterface $schema, DocumentNode $document): ExtensionContextInterface
    {
        $context = new ExtensionContext($schema, $document, $this->definitionBuilderCreator);

        $context->boot();

        return $context;
    }
}
