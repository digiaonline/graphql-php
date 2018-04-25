<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Schema\Validation\Rule\RuleInterface;

interface SchemaValidatorInterface
{
    /**
     * @param Schema               $schema
     * @param RuleInterface[]|null $rules
     * @return SchemaValidationException[]
     */
    public function validate(Schema $schema, ?array $rules = null): array;

    /**
     * @param Schema $schema
     */
    public function assertValid(Schema $schema): void;

    /**
     * @param Schema $schema
     * @return ValidationContextInterface
     */
    public function createContext(Schema $schema): ValidationContextInterface;
}
