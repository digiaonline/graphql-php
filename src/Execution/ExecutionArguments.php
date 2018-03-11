<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Schema;

class ExecutionArguments
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var DocumentNode
     */
    protected $document;

    /**
     * @var mixed
     */
    protected $rootValue;

    /**
     * @var mixed
     */
    protected $contextValue;

    /**
     * @var array
     */
    protected $variableValues;

    /**
     * @var OperationDefinitionNode
     */
    protected $operation;

    /**
     * @var mixed
     */
    protected $fieldResolver;

    /**
     * ExecutionArguments constructor.
     * @param Schema                  $schema
     * @param DocumentNode            $document
     * @param mixed                   $rootValue
     * @param mixed                   $contextValue
     * @param array                   $variableValues
     * @param OperationDefinitionNode $operation
     * @param mixed                   $fieldResolver
     */
    public function __construct(
        Schema $schema,
        DocumentNode $document,
        $rootValue,
        $contextValue,
        array $variableValues,
        OperationDefinitionNode $operation,
        $fieldResolver
    ) {
        $this->schema         = $schema;
        $this->document       = $document;
        $this->rootValue      = $rootValue;
        $this->contextValue   = $contextValue;
        $this->variableValues = $variableValues;
        $this->operation      = $operation;
        $this->fieldResolver  = $fieldResolver;
    }
}
