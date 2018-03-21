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
        NameValidator::class,
        TypeComparator::class,
        ValueNodeResolver::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(NameValidator::class, NameValidator::class, true/* $shared */);
        $this->container->add(TypeComparator::class, TypeComparator::class, true/* $shared */);
        $this->container->add(ValueNodeResolver::class, ValueNodeResolver::class, true/* $shared */);
    }
}
