<?php

namespace Digia\GraphQL\Schema;

use Digia\GraphQL\Schema\DefinitionBuilderCreator;
use Digia\GraphQL\Schema\DefinitionBuilderCreatorInterface;
use Digia\GraphQL\Schema\ResolverMapRegistry;
use Digia\GraphQL\Schema\ResolverRegistryInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\SimpleCache\CacheInterface;

class SchemaProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        DefinitionBuilderCreatorInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(DefinitionBuilderCreatorInterface::class, DefinitionBuilderCreator::class)
            ->withArgument(CacheInterface::class);
    }
}
