<?php

namespace Digia\GraphQL\SchemaExtension;

use Digia\GraphQL\SchemaBuilder\DefinitionBuilderCreatorInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class SchemaExtensionProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        ExtensionContextCreatorInterface::class,
        SchemaExtenderInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(ExtensionContextCreatorInterface::class, ExtensionContextCreator::class, true)
            ->withArgument(DefinitionBuilderCreatorInterface::class);

        $this->container->add(SchemaExtenderInterface::class, SchemaExtender::class)
            ->withArgument(ExtensionContextCreatorInterface::class);
    }
}
