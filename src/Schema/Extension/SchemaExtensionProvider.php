<?php

namespace Digia\GraphQL\Schema\Extension;

use League\Container\ServiceProvider\AbstractServiceProvider;

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
        $this->container->add(SchemaExtenderInterface::class, SchemaExtender::class);
    }
}
