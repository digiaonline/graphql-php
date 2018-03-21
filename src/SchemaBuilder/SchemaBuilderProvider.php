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
        DefinitionBuilderCreatorInterface::class,
        SchemaBuilderInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(DefinitionBuilderCreatorInterface::class, DefinitionBuilderCreator::class)
            ->withArgument(CacheInterface::class)
            ->withArgument(ValuesResolver::class);

        $this->container->add(SchemaBuilderInterface::class, SchemaBuilder::class)
            ->withArgument(DefinitionBuilderCreatorInterface::class);
    }
}
