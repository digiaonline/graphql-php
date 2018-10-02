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
        ValuesHelper::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->share(ExecutionInterface::class, Execution::class);
        $this->container->share(ValuesHelper::class, ValuesHelper::class);
    }
}
