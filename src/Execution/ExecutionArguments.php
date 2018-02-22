<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Schema\Schema;

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
     * @var []
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
     * @param Schema $schema
     * @param DocumentNode $document
     * @param mixed $rootValue
     * @param mixed $contextValue
     * @param $variableValues
     * @param OperationDefinitionNode $operation
     * @param mixed $fieldResolver
     */
    public function __construct(
        Schema $schema,
        DocumentNode $document,
        mixed $rootValue,
        mixed $contextValue,
        $variableValues,
        OperationDefinitionNode $operation,
        mixed $fieldResolver)
    {
        $this->schema         = $schema;
        $this->document       = $document;
        $this->rootValue      = $rootValue;
        $this->contextValue   = $contextValue;
        $this->variableValues = $variableValues;
        $this->operation      = $operation;
        $this->fieldResolver  = $fieldResolver;
    }
}
