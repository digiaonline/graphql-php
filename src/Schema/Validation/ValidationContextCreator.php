<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Schema\SchemaInterface;

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
