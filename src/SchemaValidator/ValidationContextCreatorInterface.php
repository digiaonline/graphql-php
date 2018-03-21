<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Type\SchemaInterface;

interface ValidationContextCreatorInterface
{
    /**
     * @param SchemaInterface $schema
     * @return ValidationContextInterface
     */
    public function create(SchemaInterface $schema): ValidationContextInterface;
}
