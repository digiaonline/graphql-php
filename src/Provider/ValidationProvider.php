<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Validation\ContextBuilder;
use Digia\GraphQL\Validation\ContextBuilderInterface;
use Digia\GraphQL\Validation\Rule\SupportedRules;
use Digia\GraphQL\Validation\Validator;
use Digia\GraphQL\Validation\ValidatorInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ValidationProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ContextBuilderInterface::class,
        ValidatorInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ContextBuilderInterface::class, ContextBuilder::class, true/* $shared */);

        $this->container->add(ValidatorInterface::class, Validator::class, true/* $shared */)
            ->withArgument(ContextBuilderInterface::class)
            ->withArgument(SupportedRules::get());
    }
}
