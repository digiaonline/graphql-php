<?php

namespace Digia\GraphQL\Util;

use League\Container\ServiceProvider\AbstractServiceProvider;

class UtilityProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        NameHelper::class,
        TypeHelper::class,
        TypeASTConverter::class,
        ValueASTConverter::class,
        ValueConverter::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(NameHelper::class, NameHelper::class, true/* $shared */);
        $this->container->add(TypeHelper::class, TypeHelper::class, true/* $shared */);
        $this->container->add(TypeASTConverter::class, TypeASTConverter::class, true/* $shared */);
        $this->container->add(ValueASTConverter::class, ValueASTConverter::class, true/* $shared */);
        $this->container->add(ValueConverter::class, ValueConverter::class, true/* $shared */);
    }
}
