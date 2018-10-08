<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

interface ClassResolverInterface extends ResolverInterface
{
    /**
     * @param callable         $resolveCallback
     * @param mixed            $rootValue
     * @param array            $args
     * @param mixed            $contextValues
     * @param ResolveInfo|null $info
     * @return mixed
     */
    public function beforeResolve(
        callable $resolveCallback,
        $rootValue,
        array $args,
        $contextValues = null,
        ?ResolveInfo $info = null
    );

    /**
     * @param mixed            $result
     * @param mixed            $rootValue
     * @param array            $args
     * @param mixed            $contextValues
     * @param ResolveInfo|null $info
     * @return mixed
     */
    public function afterResolve(
        $result,
        $rootValue,
        array $args,
        $contextValues = null,
        ?ResolveInfo $info = null
    );
}
