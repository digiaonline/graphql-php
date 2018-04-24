<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Util\TypeInfo;

interface ValidatorInterface
{
    /**
     * @param Schema        $schema
     * @param DocumentNode  $document
     * @param array|null    $rules
     * @param TypeInfo|null $typeInfo
     * @return ValidationException[]
     */
    public function validate(
        Schema $schema,
        DocumentNode $document,
        ?array $rules = null,
        ?TypeInfo $typeInfo = null
    ): array;

    /**
     * @param Schema       $schema
     * @param DocumentNode $document
     * @param TypeInfo     $typeInfo
     * @return ValidationContextInterface
     */
    public function createContext(
        Schema $schema,
        DocumentNode $document,
        TypeInfo $typeInfo
    ): ValidationContextInterface;
}
