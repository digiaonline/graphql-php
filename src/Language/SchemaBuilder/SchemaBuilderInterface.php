<?php

namespace Digia\GraphQL\Language\SchemaBuilder;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;

interface SchemaBuilderInterface
{
    /**
     * @param DocumentNode $document
     * @param array        $resolverMap
     * @param array        $options
     * @return SchemaInterface
     */
    public function build(DocumentNode $document, array $resolverMap = [], array $options = []): SchemaInterface;
}
