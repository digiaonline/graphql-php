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
     * @param GraphQLError $error
     * @return ExecutionResult
     */
    public function addError(GraphQLError $error): ExecutionResult
    {
        $this->errors[] = $error;
        return $this;
    }
}
