<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Error\ExtensionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\DefinitionBuilderCreatorInterface;
use Digia\GraphQL\Schema\SchemaInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function Digia\GraphQL\Type\newSchema;

class SchemaExtender implements SchemaExtenderInterface
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
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @return SchemaInterface
     * @throws InvariantException
     * @throws ExtensionException
     * @throws InvalidArgumentException
     */
    public function extend(SchemaInterface $schema, DocumentNode $document): SchemaInterface
    {
        $context = $this->createContext($schema, $document);

        // If this document contains no new types, extensions, or directives then
        // return the same unmodified GraphQLSchema instance.
        if (!$context->isSchemaExtended()) {
            return $schema;
        }

        return newSchema([
            'query'        => $context->getExtendedQueryType(),
            'mutation'     => $context->getExtendedMutationType(),
            'subscription' => $context->getExtendedSubscriptionType(),
            'types'        => $context->getExtendedTypes(),
            'directives'   => $context->getExtendedDirectives(),
            'astNode'      => $schema->getAstNode(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function createContext(SchemaInterface $schema, DocumentNode $document): ExtensionContextInterface
    {
        $context = new ExtensionContext($schema, $document, $this->definitionBuilderCreator);

        $context->boot();

        return $context;
    }
}
