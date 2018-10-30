<?php

namespace Digia\GraphQL\Execution\Strategy;

use Digia\GraphQL\Execution\UndefinedFieldException;
use Digia\GraphQL\Type\Definition\ObjectType;
use React\Promise\PromiseInterface;
use function Digia\GraphQL\Util\promiseForMap;

/**
 * Implements the "Evaluating selection sets" section of the spec
 * for "read" mode.
 *
 * Class ParallelExecutionStrategy
 * @package Digia\GraphQL\Execution\Strategy
 */
class ParallelExecutionStrategy extends AbstractExecutionStrategy
{
    /**
     * @inheritdoc
     */
    public function executeFields(ObjectType $parentType, $rootValue, array $path, array $fields)
    {
        $results            = [];
        $doesContainPromise = false;

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            try {
                $result = $this->resolveField($parentType, $rootValue, $fieldNodes, $fieldPath);
            } catch (UndefinedFieldException $exception) {
                continue;
            }

            $doesContainPromise  = $doesContainPromise || $result instanceof PromiseInterface;
            $results[$fieldName] = $result;
        }

        if (!$doesContainPromise) {
            return $results;
        }

        // Otherwise, results is a map from field name to the result of resolving that
        // field, which is possibly a promise. Return a promise that will return this
        // same map, but with any promises replaced with the values they resolved to.
        return promiseForMap($results);
    }
}
