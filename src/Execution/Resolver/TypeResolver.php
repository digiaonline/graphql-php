<?php

namespace Digia\GraphQL\Execution\Resolver;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\ExecutionEnvironment;

class TypeResolver implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(ExecutionEnvironment $environment)
    {
        $schema = $environment->getInfo()->getSchema();
        // TODO: Benchmark
        $className = (new \ReflectionClass($environment->getValue()))->getShortName();

        if (null !== ($type = $schema->getType($className . 'Type'))) {
            return $type;
        }

        throw new ExecutionException(sprintf('Could not resolve type for class "%s".', $className));
    }
}
