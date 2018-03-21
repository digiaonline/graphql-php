<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\SchemaValidator\Rule\RuleInterface;
use Digia\GraphQL\Type\SchemaInterface;

interface SchemaValidatorInterface
{
    /**
     * @param SchemaInterface      $schema
     * @param RuleInterface[]|null $rules
     * @return SchemaValidationException[]
     */
    public function validate(SchemaInterface $schema, ?array $rules = null): array;

    /**
     * @param SchemaInterface $schema
     */
    public function assertValid(SchemaInterface $schema): void;
}
