<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\SchemaInterface;

interface ExtensionContextCreatorInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @return ExtensionContextInterface
     */
    public function create(SchemaInterface $schema, DocumentNode $document): ExtensionContextInterface;
}
