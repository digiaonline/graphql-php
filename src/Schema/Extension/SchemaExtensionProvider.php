<?php

namespace Digia\GraphQL\Schema\Extension;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\SimpleCache\CacheInterface;

class SchemaExtensionProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        SchemaExtenderInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(SchemaExtenderInterface::class, SchemaExtender::class)
            ->withArgument(CacheInterface::class);
    }
}
