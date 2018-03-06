<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Schema\DefinitionBuilder;
use Digia\GraphQL\Language\AST\Schema\DefinitionBuilderInterface;
use Digia\GraphQL\Language\AST\Schema\SchemaBuilder;
use Digia\GraphQL\Language\AST\Schema\SchemaBuilderInterface;
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
        $this->container->add(DefinitionBuilderInterface::class, DefinitionBuilder::class, true/* $shared */)
            ->withArguments([
                function (NamedTypeNode $node) {
                    throw new \Exception(sprintf('Type "%s" not found in document.', $node->getNameValue()));
                },
                CacheInterface::class,
            ]);

        $this->container->add(SchemaBuilderInterface::class, SchemaBuilder::class, true/* $shared */)
            ->withArgument(DefinitionBuilderInterface::class);
    }
}
