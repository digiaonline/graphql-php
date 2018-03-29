<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Error\ValidationExceptionInterface;
use Digia\GraphQL\Type\SchemaInterface;

interface ValidationContextInterface
{
    /**
     * @param ValidationExceptionInterface $error
     */
    public function reportError(ValidationExceptionInterface $error);

    /**
     * @return ValidationExceptionInterface[]
     */
    public function getErrors(): array;

    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface;
}
