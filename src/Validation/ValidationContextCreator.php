<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;

class ValidationContextCreator implements ValidationContextCreatorInterface
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
    ): ValidationContextInterface {
        return new ValidationContext($schema, $document, $typeInfo);
    }
}
