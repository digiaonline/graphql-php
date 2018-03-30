<?php

namespace Digia\GraphQL\SchemaBuilder;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\SimpleCache\CacheInterface;

class SchemaBuilderProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ResolverRegistryInterface::class,
        DefinitionBuilderCreatorInterface::class,
        BuilderContextCreatorInterface::class,
        SchemaBuilderInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ResolverRegistryInterface::class, ResolverMapRegistry::class);

        $this->container->add(DefinitionBuilderCreatorInterface::class, DefinitionBuilderCreator::class)
            ->withArgument(CacheInterface::class);

        $this->container->add(BuilderContextCreatorInterface::class, BuilderContextCreator::class)
            ->withArgument(DefinitionBuilderCreatorInterface::class);

        $this->container->add(SchemaBuilderInterface::class, SchemaBuilder::class)
            ->withArgument(BuilderContextCreatorInterface::class);
    }
}
