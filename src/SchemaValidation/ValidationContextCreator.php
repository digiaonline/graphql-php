<?php

namespace Digia\GraphQL\SchemaValidation;

use Digia\GraphQL\Type\SchemaInterface;

class ValidationContextCreator implements ValidationContextCreatorInterface
{
    /**
     * @inheritdoc
     */
    public function create(SchemaInterface $schema): ValidationContextInterface
    {
        return new ValidationContext($schema);
    }
}
