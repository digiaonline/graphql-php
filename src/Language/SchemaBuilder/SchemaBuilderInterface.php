<?php

namespace Digia\GraphQL\Language\SchemaBuilder;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;

interface SchemaBuilderInterface
{
    /**
     * @param DocumentNode $documentNode
     * @param array        $options
     * @return SchemaInterface
     */
    public function build(DocumentNode $documentNode, array $options = []): SchemaInterface;
}
