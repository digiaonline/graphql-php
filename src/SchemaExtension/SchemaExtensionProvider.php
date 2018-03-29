<?php

namespace Digia\GraphQL\SchemaExtension;

use Digia\GraphQL\SchemaBuilder\DefinitionBuilderCreatorInterface;
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
            ->withArgument(DefinitionBuilderCreatorInterface::class)
            ->withArgument(CacheInterface::class);
    }
}
