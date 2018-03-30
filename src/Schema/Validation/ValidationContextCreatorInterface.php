<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Schema\SchemaInterface;

interface ValidationContextCreatorInterface
{
    /**
     * @param SchemaInterface $schema
     * @return ValidationContextInterface
     */
    public function create(SchemaInterface $schema): ValidationContextInterface;
}
