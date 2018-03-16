<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Type\SchemaInterface;

class ContextBuilder implements ContextBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(SchemaInterface $schema): ValidationContextInterface
    {
        return new ValidationContext($schema);
    }
}
