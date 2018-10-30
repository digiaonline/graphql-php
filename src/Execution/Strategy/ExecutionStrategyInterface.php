<?php

namespace Digia\GraphQL\Execution\Strategy;

interface ExecutionStrategyInterface
{
    /**
     * @return mixed
     */
    public function execute();
}
