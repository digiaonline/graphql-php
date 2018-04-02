<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Schema\DefinitionBuilderCreatorInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\SimpleCache\CacheInterface;

class SchemaBuildingProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        SchemaBuilderInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(SchemaBuilderInterface::class, SchemaBuilder::class)
            ->withArgument(CacheInterface::class);
    }
}
