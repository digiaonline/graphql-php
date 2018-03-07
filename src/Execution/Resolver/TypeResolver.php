<?php

namespace Digia\GraphQL\Execution\Resolver;

use Digia\GraphQL\Execution\ExecutionEnvironment;

class TypeResolver implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(ExecutionEnvironment $environment)
    {
        $schema    = $environment->getInfo()->getSchema();
        // TODO: Benchmark
        $className = (new \ReflectionClass($environment->getValue()))->getShortName();

        if (null !== ($type = $schema->getType($className . 'Type'))) {
            return $type;
        }

        throw new \Exception(sprintf('Could not resolve type for class "%s".', $className));
    }
}
