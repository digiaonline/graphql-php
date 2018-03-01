<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;

class ExecutionResult
{
    /**
     * @var GraphQLError[]
     */
    protected $errors;

    /**
     * @var mixed[]
     */
    protected $data;

    /**
     * ExecutionResult constructor.
     * @param mixed[] $data
     * @param GraphQLError[] $errors
     */
    public function __construct(array $data, array $errors)
    {
        $this->errors = $errors;
        $this->data   = $data;
    }

    /**
     * @return array|mixed[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array|GraphQLError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param GraphQLError $error
     * @return ExecutionResult
     */
    public function addError(GraphQLError $error): ExecutionResult
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'data'   => $this->getData(),
            'errors' => $this->getErrors(),
        ];
    }
}
