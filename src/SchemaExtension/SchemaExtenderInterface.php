<?php

namespace Digia\GraphQL\SchemaExtension;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;

interface SchemaExtenderInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @param array           $options
     * @return SchemaInterface
     */
    public function extend(SchemaInterface $schema, DocumentNode $document, array $options = []): SchemaInterface;
}
