<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Util\ArrayToJsonTrait;
use Digia\GraphQL\Util\SerializationInterface;

class ExecutionResult implements SerializationInterface
{
    use ArrayToJsonTrait;

    /**
     * @var ExecutionException[]
     */
    protected $errors;

    /**
     * @var mixed[]
     */
    protected $data;

    /**
     * ExecutionResult constructor.
     * @param mixed[]              $data
     * @param ExecutionException[] $errors
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
     * @return array|ExecutionException[]
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
        $array = ['data' => $this->data];

        if (!empty($this->errors)) {
            $array['errors'] = $this->errors;
        }

        return $array;
    }
}
