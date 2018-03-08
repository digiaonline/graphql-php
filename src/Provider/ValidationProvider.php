<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Validation\Validator;
use Digia\GraphQL\Validation\ValidatorInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ValidationProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ValidatorInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ValidatorInterface::class, Validator::class, true/* $shared */);
    }
}
