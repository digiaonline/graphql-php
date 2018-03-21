<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Type\SchemaInterface;

interface ValidationContextInterface
{
    /**
     * @param SchemaValidationException $error
     */
    public function reportError(SchemaValidationException $error);

    /**
     * @return SchemaValidationException[]
     */
    public function getErrors(): array;

    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface;
}
