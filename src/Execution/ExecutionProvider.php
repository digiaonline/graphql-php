<?php

namespace Digia\GraphQL\Execution;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ExecutionProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ExecutionInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->share(ExecutionInterface::class, Execution::class);
    }
}
