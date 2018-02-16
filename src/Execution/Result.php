<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;

class Result
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
     * Result constructor.
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
     * @return Result
     */
    public function addError(GraphQLError $error): Result
    {
        $this->errors[] = $error;
        return $this;
    }
}
