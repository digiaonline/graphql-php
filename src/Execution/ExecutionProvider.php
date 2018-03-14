<?php

namespace Digia\GraphQL\Execution;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Class ExecutionProvider
 * @package Digia\GraphQL\Provider
 */
class ExecutionProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ExecutionInterface::class
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ExecutionInterface::class, Execution::class, true/* $shared */);
    }
}
