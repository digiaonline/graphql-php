<?php

namespace Digia\GraphQL\Error;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ErrorProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ErrorHandlerInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ErrorHandlerInterface::class, ErrorHandler::class, true/* $shared */);
    }
}
