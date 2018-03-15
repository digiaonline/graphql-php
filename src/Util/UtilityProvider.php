<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Execution\ValuesResolver;
use League\Container\ServiceProvider\AbstractServiceProvider;

class UtilityProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        TypeComparator::class,
        ValuesResolver::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(TypeComparator::class, TypeComparator::class, true/* $shared */);
        $this->container->add(ValuesResolver::class, ValuesResolver::class, true/* $shared */);
    }
}
