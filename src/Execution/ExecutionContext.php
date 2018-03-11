<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Schema;

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
     * @var []
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
     * @param                         $rootValue
     * @param                         $contextValue
     * @param                         $variableValues
     * @param                         $fieldResolver
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
    public function setRootValue($rootValue)
    {
        $this->rootValue = $rootValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContextValue()
    {
        return $this->contextValue;
    }

    /**
     * @param mixed $contextValue
     * @return ExecutionContext
     */
    public function setContextValue($contextValue)
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
    public function setVariableValues($variableValues)
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
    public function setFieldResolver($fieldResolver)
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
     * @return array|FragmentDefinitionNode[]
     */
    public function getFragments()
    {
        return $this->fragments;
    }

    /**
     * Create proper ExecutionStrategy when needed
     *
     * @return ExecutionStrategy
     */
    public function getExecutionStrategy(): ExecutionStrategy
    {
        //We can probably return different strategy in the future e.g:AsyncExecutionStrategy
        return new ExecutorExecutionStrategy($this, $this->operation, $this->rootValue, new ValuesResolver());
    }

    /**
     * @param ExecutionException $error
     * @return ExecutionContext
     */
    public function addError(ExecutionException $error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return array|ExecutionException[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
