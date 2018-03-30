<?php

namespace Digia\GraphQL\SchemaExtension;

use Digia\GraphQL\Error\ExtensionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function Digia\GraphQL\Type\GraphQLSchema;

class SchemaExtender implements SchemaExtenderInterface
{
    /**
     * @var ExtensionContextCreatorInterface
     */
    protected $contextCreator;

    /**
     * SchemaExtender constructor.
     * @param ExtensionContextCreatorInterface $contextCreator
     */
    public function __construct(ExtensionContextCreatorInterface $contextCreator)
    {
        $this->contextCreator = $contextCreator;
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
        $context = $this->contextCreator->create($schema, $document);

        // If this document contains no new types, extensions, or directives then
        // return the same unmodified GraphQLSchema instance.
        if (!$context->isSchemaExtended()) {
            return $schema;
        }

        return GraphQLSchema([
            'query'        => $context->getExtendedQueryType(),
            'mutation'     => $context->getExtendedMutationType(),
            'subscription' => $context->getExtendedSubscriptionType(),
            'types'        => $context->getExtendedTypes(),
            'directives'   => $context->getExtendedDirectives(),
            'astNode'      => $schema->getAstNode(),
        ]);
    }
}
