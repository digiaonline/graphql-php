<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Cache\RuntimeCache;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Schema\DefinitionBuilder;
use Digia\GraphQL\Language\AST\Schema\SchemaBuilder;
use Digia\GraphQL\Type\SchemaInterface;

/**
 * @param string $source
 * @param array  $options
 * @return SchemaInterface
 * @throws \Digia\GraphQL\Error\GraphQLError
 * @throws \Exception
 * @throws \TypeError
 */
function buildSchema(string $source, array $options = []): SchemaInterface
{
    static $instance = null;

    /** @var DocumentNode $documentNode */
    $documentNode = parse($source, $options);

    if (null === $instance) {
        $resolveType = function (NamedTypeNode $node) {
            throw new \Exception(sprintf('Type "%s" not found in document.', $node->getNameValue()));
        };

        $definitionBuilder = new DefinitionBuilder($resolveType, new RuntimeCache());
        $instance          = new SchemaBuilder($definitionBuilder);
    }

    return $instance->build($documentNode, $options);
}
