<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Execution\ExecutionException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Schema\Schema;

class ExecutionContext
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var FragmentDefinitionNode[]
     */
    protected $fragments;

    /**
     * @var mixed
     */
    protected $rootValue;

    /**
     * @var mixed
     */
    protected $contextValue;

    /**
     * @var mixed
     */
    protected $variableValues;

    /**
     * @var mixed
     */
    protected $fieldResolver;

    /**
     * @var OperationDefinitionNode
     */
    protected $operation;

    /**
     * @var ExecutionException[]
     */
    protected $errors;

    /**
     * ExecutionContext constructor.
     * @param Schema                  $schema
     * @param array                   $fragments
     * @param mixed                   $rootValue
     * @param mixed                   $contextValue
     * @param mixed                   $variableValues
     * @param mixed                   $fieldResolver
     * @param OperationDefinitionNode $operation
     * @param array                   $errors
     */
    public function __construct(
        Schema $schema,
        array $fragments,
        $rootValue,
        $contextValue,
        $variableValues,
        $fieldResolver,
        OperationDefinitionNode $operation,
        array $errors
    ) {
        $this->schema         = $schema;
        $this->fragments      = $fragments;
        $this->rootValue      = $rootValue;
        $this->contextValue   = $contextValue;
        $this->variableValues = $variableValues;
        $this->fieldResolver  = $fieldResolver;
        $this->operation      = $operation;
        $this->errors         = $errors;
    }

    /**
     * @return mixed
     */
    public function getRootValue()
    {
        return $this->rootValue;
    }

    /**
     * @param mixed $rootValue
     * @return ExecutionContext
     */
    public function setRootValue($rootValue): ExecutionContext
    {
        $this->rootValue = $rootValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContextValue()
    {
        return $this->contextValue ?? [];
    }

    /**
     * @param mixed $contextValue
     * @return ExecutionContext
     */
    public function setContextValue($contextValue): ExecutionContext
    {
        $this->contextValue = $contextValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVariableValues()
    {
        return $this->variableValues;
    }

    /**
     * @param mixed $variableValues
     * @return ExecutionContext
     */
    public function setVariableValues($variableValues): ExecutionContext
    {
        $this->variableValues = $variableValues;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFieldResolver()
    {
        return $this->fieldResolver;
    }

    /**
     * @param mixed $fieldResolver
     * @return ExecutionContext
     */
    public function setFieldResolver($fieldResolver): ExecutionContext
    {
        $this->fieldResolver = $fieldResolver;
        return $this;
    }


    /**
     * @return OperationDefinitionNode
     */
    public function getOperation(): OperationDefinitionNode
    {
        return $this->operation;
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return FragmentDefinitionNode[]
     */
    public function getFragments(): array
    {
        return $this->fragments;
    }

    /**
     * @param ExecutionException $error
     * @return ExecutionContext
     */
    public function addError(ExecutionException $error): ExecutionContext
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return ExecutionException[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
