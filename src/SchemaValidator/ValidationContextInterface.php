<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Type\SchemaInterface;

interface ValidationContextInterface
{
    /**
     * @param ValidationException $error
     */
    public function reportError(ValidationException $error);

    /**
     * @return ValidationException[]
     */
    public function getErrors(): array;

    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface;
}
