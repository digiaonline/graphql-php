<?php

namespace Digia\GraphQL\SchemaValidation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Error\ValidationExceptionInterface;
use Digia\GraphQL\Type\SchemaInterface;

class ValidationContext implements ValidationContextInterface
{
    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var SchemaValidationException[]
     */
    protected $errors = [];

    /**
     * SchemaValidationContext constructor.
     * @param SchemaInterface $schema
     */
    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @inheritdoc
     */
    public function reportError(ValidationExceptionInterface $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     */
    public function getSchema(): SchemaInterface
    {
        return $this->schema;
    }
}
