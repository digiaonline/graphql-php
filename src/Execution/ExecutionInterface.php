<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\Schema;

interface ExecutionInterface
{
    /**
     * @param Schema        $schema
     * @param DocumentNode  $documentNode
     * @param null          $rootValue
     * @param null          $contextValue
     * @param array         $variableValues
     * @param null          $operationName
     * @param callable|null $fieldResolver
     * @return mixed
     */
    public function execute(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue = null,
        $contextValue = null,
        $variableValues = [],
        $operationName = null,
        callable $fieldResolver = null
    ): ExecutionResult;
}
