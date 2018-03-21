<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\SchemaExtender\SchemaExtender;
use Digia\GraphQL\SchemaExtender\SchemaExtenderInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class SchemaExtenderProvider extends AbstractServiceProvider
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
