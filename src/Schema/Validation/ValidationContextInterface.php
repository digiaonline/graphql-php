<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\ValidationExceptionInterface;
use Digia\GraphQL\Schema\SchemaInterface;

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
