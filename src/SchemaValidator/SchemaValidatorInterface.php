<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\SchemaValidator\Rule\RuleInterface;
use Digia\GraphQL\Type\SchemaInterface;

interface SchemaValidatorInterface
{
    /**
     * @param SchemaInterface      $schema
     * @param RuleInterface[]|null $rules
     * @return ValidationException[]
     */
    public function validate(SchemaInterface $schema, ?array $rules = null): array;

    /**
     * @param SchemaInterface $schema
     */
    public function assertValid(SchemaInterface $schema): void;
}
