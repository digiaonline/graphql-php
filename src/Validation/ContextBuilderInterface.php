<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;

interface ContextBuilderInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @param TypeInfo        $typeInfo
     * @return ValidationContextInterface
     */
    public function build(SchemaInterface $schema, DocumentNode $document, TypeInfo $typeInfo): ValidationContextInterface;
}
