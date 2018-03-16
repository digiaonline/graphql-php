<?php

namespace Digia\GraphQL\Util;

use League\Container\ServiceProvider\AbstractServiceProvider;

class UtilityProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        TypeComparator::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(TypeComparator::class, TypeComparator::class, true/* $shared */);
    }
}
