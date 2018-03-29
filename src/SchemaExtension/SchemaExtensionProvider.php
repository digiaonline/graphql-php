<?php

namespace Digia\GraphQL\SchemaExtension;

use Digia\GraphQL\SchemaExtension\SchemaExtender;
use Digia\GraphQL\SchemaExtension\SchemaExtenderInterface;
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
