<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionInterface;
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
        $this->container->add(ExecutionInterface::class, function () {
            return new Execution();
        });
    }
}