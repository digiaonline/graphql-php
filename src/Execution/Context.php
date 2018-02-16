<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Schema;

class Context
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
     * @var GraphQLError[]
     */
    protected $errors;

    /**
     * Context constructor.
     * @param Schema $schema
     * @param FragmentDefinitionNode[] $fragments
     * @param mixed $rootValue
     * @param mixed $contextValue
     * @param $variableValues
     * @param mixed $fieldResolver
     * @param OperationDefinitionNode $operation
     * @param GraphQLError[] $errors
     */
    public function __construct(
        Schema $schema,
        array $fragments,
        mixed $rootValue,
        mixed $contextValue,
        $variableValues,
        mixed $fieldResolver,
        OperationDefinitionNode $operation,
        array $errors)
    {
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
     * @return OperationDefinitionNode
     */
    public function getOperation(): OperationDefinitionNode
    {
        return $this->operation;
    }

    /**
     * @param GraphQLError $error
     * @return Context
     */
    public function addError(GraphQLError $error)
    {
        $this->errors[] = $error;
        return $this;
    }
}
