<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Validation\ValidationExceptionInterface;

interface ValidationContextInterface
{
    /**
     * @param ValidationExceptionInterface $error
     */
    public function reportError(ValidationExceptionInterface $error): void;

    /**
     * @return ValidationExceptionInterface[]
     */
    public function getErrors(): array;

    /**
     * @return Schema
     */
    public function getSchema(): Schema;
}
