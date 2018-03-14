<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Validation\Rule\RulesBuilder;
use Digia\GraphQL\Validation\Rule\RulesBuilderInterface;
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
        $this->container->add(RulesBuilderInterface::class, RulesBuilder::class, true/* $shared */);

        $this->container->add(ValidatorInterface::class, Validator::class, true/* $shared */)
            ->withArgument(ContextBuilderInterface::class)
            ->withArgument(RulesBuilderInterface::class);
    }
}
