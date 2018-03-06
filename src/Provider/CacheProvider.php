<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Cache\RuntimeCache;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\SimpleCache\CacheInterface;

class CacheProvider extends AbstractServiceProvider
{

    /**
     * @var array
     */
    protected $provides = [
        CacheInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(CacheInterface::class, RuntimeCache::class);
    }
}
