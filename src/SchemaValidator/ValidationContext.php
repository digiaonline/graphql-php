<?php

namespace Digia\GraphQL\SchemaValidator;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Type\SchemaInterface;

class ValidationContext implements ValidationContextInterface
{
    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var ValidationException[]
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
    public function reportError(ValidationException $error)
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
