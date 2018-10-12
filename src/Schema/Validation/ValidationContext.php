<?php

namespace Digia\GraphQL\Schema\Validation;

use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Validation\ValidationExceptionInterface;

class ValidationContext implements ValidationContextInterface
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var ValidationExceptionInterface[]
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
    public function reportError(ValidationExceptionInterface $error): void
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
