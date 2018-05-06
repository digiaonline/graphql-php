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
        $this->container->add(ExecutionInterface::class, Execution::class, true/* $shared */);
        $this->container->add(ValuesHelper::class, ValuesHelper::class, true/* $shared */);
    }
}
