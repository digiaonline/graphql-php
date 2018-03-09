<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Util\SerializationInterface;
use function Digia\GraphQL\Util\jsonEncode;

class ExecutionResult implements SerializationInterface
{

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
        return [
            'data'   => $this->getData(),
            'errors' => $this->getErrors(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function toJSON(): string
    {
        return jsonEncode($this->toArray());
    }
}
