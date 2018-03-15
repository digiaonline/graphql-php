<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Type\SchemaInterface;

interface ContextBuilderInterface
{
    /**
     * @param SchemaInterface $schema
     * @return ValidationContextInterface
     */
    public function build(SchemaInterface $schema): ValidationContextInterface;
}
