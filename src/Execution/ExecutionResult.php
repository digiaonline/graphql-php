<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Util\SerializationInterface;
use function Digia\GraphQL\Util\jsonEncode;

class ExecutionResult implements SerializationInterface
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
     * @param mixed[]        $data
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
