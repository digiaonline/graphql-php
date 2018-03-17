<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Execution\ValuesResolver;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\SimpleCache\CacheInterface;

class SchemaBuilderProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        DefinitionBuilderInterface::class,
        SchemaBuilderInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(DefinitionBuilderInterface::class, DefinitionBuilder::class)
            ->withArgument(CacheInterface::class)
            ->withArgument(ValuesResolver::class);

        $this->container->add(SchemaBuilderInterface::class, SchemaBuilder::class)
            ->withArgument(DefinitionBuilderInterface::class);
    }
}
