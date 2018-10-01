<?php

namespace Digia\GraphQL\Schema\Building;

use League\Container\ServiceProvider\AbstractServiceProvider;

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
        $this->container->add(SchemaBuilderInterface::class, SchemaBuilder::class);
    }
}
