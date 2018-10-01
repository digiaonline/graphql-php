<?php

namespace Digia\GraphQL\Language;

use League\Container\ServiceProvider\AbstractServiceProvider;

class LanguageProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        NodeBuilderInterface::class,
        ParserInterface::class,
        NodePrinterInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->share(NodeBuilderInterface::class, NodeBuilder::class);
        $this->container->share(NodePrinterInterface::class, NodePrinter::class);
        $this->container->add(ParserInterface::class, Parser::class);
    }
}
