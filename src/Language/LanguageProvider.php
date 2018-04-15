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
        $this->container->add(NodeBuilderInterface::class, NodeBuilder::class, true/* $shared */);

        $this->container->add(ParserInterface::class, Parser::class);

        $this->container->add(NodePrinterInterface::class, NodePrinter::class, true/* $shared */);
    }
}
