<?php

namespace Digia\GraphQL\SchemaExtension;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;

interface ExtensionContextCreatorInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @return ExtensionContextInterface
     */
    public function create(SchemaInterface $schema, DocumentNode $document): ExtensionContextInterface;
}
