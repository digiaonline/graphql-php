<?php

namespace Digia\GraphQL\Execution\Strategy;

use Digia\GraphQL\Execution\UndefinedFieldException;
use Digia\GraphQL\Type\Definition\ObjectType;
use React\Promise\PromiseInterface;
use function Digia\GraphQL\Util\promiseReduce;

/**
 * Implements the "Evaluating selection sets" section of the spec
 * for "write" mode.
 *
 * Class SerialExecutionStrategy
 * @package Digia\GraphQL\Execution\Strategy
 */
class SerialExecutionStrategy extends AbstractExecutionStrategy
{
    /**
     * @inheritdoc
     */
    public function executeFields(ObjectType $parentType, $rootValue, array $path, array $fields)
    {
        return promiseReduce(
            \array_keys($fields),
            function ($results, $fieldName) use ($parentType, $rootValue, $path, $fields) {
                $fieldNodes  = $fields[$fieldName];
                $fieldPath   = $path;
                $fieldPath[] = $fieldName;

                try {
                    $result = $this->resolveField($parentType, $rootValue, $fieldNodes, $fieldPath);
                } catch (UndefinedFieldException $exception) {
                    return null;
                }

                if ($result instanceof PromiseInterface) {
                    return $result->then(function ($resolvedResult) use ($fieldName, $results) {
                        $results[$fieldName] = $resolvedResult;
                        return $results;
                    });
                }

                $results[$fieldName] = $result;

                return $results;
            }
        );
    }
}
