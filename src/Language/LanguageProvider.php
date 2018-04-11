<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Writer\SupportedWriters;
use League\Container\ServiceProvider\AbstractServiceProvider;

class LanguageProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        NodeBuilderInterface::class,
        ParserInterface::class,
        PrinterInterface::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->container->add(NodeBuilderInterface::class, NodeBuilder::class, true/* $shared */);

        $this->container->add(ParserInterface::class, Parser::class);

        $this->container->add(PrinterInterface::class, Printer::class, true/* $shared */)
            ->withArgument(SupportedWriters::get());
    }
}
