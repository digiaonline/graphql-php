<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLException;

class CoercedValue
{
    /**
     * @var GraphQLException[]
     */
    private $errors;

    /**
     * @var mixed
     */
    private $value;

    /**
     * CoercedValue constructor.
     * @param mixed $value
     * @param array $errors
     */
    public function __construct($value, array $errors = [])
    {
        $this->errors = $errors;
        $this->value  = $value;
    }

    /**
     * @return GraphQLException[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @param GraphQLException[] $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
