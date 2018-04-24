<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Error\ValidationExceptionInterface;
use Digia\GraphQL\Schema\Schema;

class ValidationContext implements ValidationContextInterface
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var SchemaValidationException[]
     */
    protected $errors = [];

    /**
     * SchemaValidationContext constructor.
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
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
    public function getSchema(): Schema
    {
        return $this->schema;
    }
}
