<?php

namespace Digia\GraphQL\SchemaValidator;

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
