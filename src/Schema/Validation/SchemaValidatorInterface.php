<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Schema\SchemaInterface;
use Digia\GraphQL\Schema\Validation\Rule\RuleInterface;

interface SchemaValidatorInterface
{

    /**
     * @param SchemaInterface $schema
     * @param RuleInterface[]|null $rules
     *
     * @return SchemaValidationException[]
     */
    public function validate(
        SchemaInterface $schema,
        ?array $rules = null
    ): array;

    /**
     * @param SchemaInterface $schema
     */
    public function assertValid(SchemaInterface $schema): void;

    /**
     * @param SchemaInterface $schema
     *
     * @return ValidationContextInterface
     */
    public function createContext(SchemaInterface $schema
    ): ValidationContextInterface;
}
