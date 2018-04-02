<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\SchemaInterface;

interface SchemaExtenderInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @return SchemaInterface
     */
    public function extend(SchemaInterface $schema, DocumentNode $document): SchemaInterface;

    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @return ExtensionContextInterface
     */
    public function createContext(SchemaInterface $schema, DocumentNode $document): ExtensionContextInterface;
}
