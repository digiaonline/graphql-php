<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\SerializationInterface;

class ExecutionResult implements SerializationInterface
{
    use ArrayToJsonTrait;

    /**
     * @var array|null
     */
    protected $data;

    /**
     * @var ExecutionException[]
     */
    protected $errors;

    /**
     * ExecutionResult constructor.
     * @param array|null           $data
     * @param ExecutionException[] $errors
     */
    public function __construct(?array $data, array $errors)
    {
        $this->data   = $data;
        $this->errors = $errors;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @return ExecutionException[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param ExecutionException $error
     * @return ExecutionResult
     */
    public function addError(ExecutionException $error): ExecutionResult
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        $array = [];

        if (!empty($this->errors)) {
            $array['errors'] = \array_map(function (GraphQLException $error) {
                return $error->toArray();
            }, $this->errors);
        }

        $array['data'] = $this->data;

        return $array;
    }
}
