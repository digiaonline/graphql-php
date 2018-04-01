<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;

interface ValidatorInterface
{
    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @param array|null      $rules
     * @param TypeInfo|null   $typeInfo
     * @return ValidationException[]
     */
    public function validate(
        SchemaInterface $schema,
        DocumentNode $document,
        ?array $rules = null,
        ?TypeInfo $typeInfo = null
    ): array;

    /**
     * @param SchemaInterface $schema
     * @param DocumentNode    $document
     * @param TypeInfo        $typeInfo
     * @return ValidationContextInterface
     */
    public function createContext(
        SchemaInterface $schema,
        DocumentNode $document,
        TypeInfo $typeInfo
    ): ValidationContextInterface;
}
