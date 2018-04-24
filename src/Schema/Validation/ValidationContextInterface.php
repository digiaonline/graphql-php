<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Error\ValidationExceptionInterface;
use Digia\GraphQL\Schema\Schema;

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
     * @return Schema
     */
    public function getSchema(): Schema;
}
