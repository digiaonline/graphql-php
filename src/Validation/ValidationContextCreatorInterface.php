<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;

interface ValidationContextCreatorInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @param TypeInfo        $typeInfo
     * @return ValidationContextInterface
     */
    public function create(
        SchemaInterface $schema,
        DocumentNode $document,
        TypeInfo $typeInfo
    ): ValidationContextInterface;
}
